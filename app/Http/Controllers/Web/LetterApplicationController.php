<?php

namespace App\Http\Controllers\Web;

use App\Actions\Letters\ChangeLetterApplicationStatusAction;
use App\Actions\Letters\CreateLetterApplicationAction;
use App\Actions\Letters\UpdateLetterApplicationAction;
use App\Enums\LetterApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreLetterApplicationRequest;
use App\Http\Requests\Web\UpdateLetterApplicationRequest;
use App\Http\Requests\Web\UploadLetterDocumentsRequest;
use App\Models\LetterApplication;
use App\Models\LetterApplicationDocument;
use App\Models\LetterService;
use App\Models\PopulationArea;
use App\Services\Letters\LetterFormSchemaService;
use App\Services\Letters\LetterDocumentRequirementService;
use App\Services\Letters\LetterSettings;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LetterApplicationController extends Controller
{
    public function create(
        LetterService $letterService,
        PublicSiteService $site,
        LetterFormSchemaService $schemas,
        LetterSettings $settings,
    ): View
    {
        abort_unless($letterService->is_active, 404);
        session(['letter_form_rendered_at' => now()->timestamp]);
        $submissionKey = Str::random(40);
        session(["letter_submission.{$submissionKey}" => ['service_id' => $letterService->id]]);
        $site->shareLayout();

        $defaultHamlets = ['Gumul', 'Talasan', 'Bakir', 'Kedungrejo', 'Biyan'];
        $hamlets = PopulationArea::query()
            ->whereNotNull('hamlet')
            ->distinct()
            ->orderBy('hamlet')
            ->pluck('hamlet')
            ->filter()
            ->values()
            ->all();
        $hamlets = collect($defaultHamlets)
            ->concat($hamlets)
            ->map(fn ($hamlet): string => mb_convert_case(trim((string) $hamlet), MB_CASE_TITLE, 'UTF-8'))
            ->filter()
            ->reject(fn (string $hamlet): bool => $hamlet === 'Sukomulyo')
            ->unique(fn (string $hamlet): string => mb_strtolower($hamlet))
            ->values()
            ->all();

        return view('pages.letters.create', [
            'letterService' => $letterService,
            'fields' => $schemas->fields($letterService),
            'submissionKey' => $submissionKey,
            'hamlets' => $hamlets,
            'submissionSteps' => collect(config('administrative_services.submission_steps', [])),
            'officeHours' => $settings->officeHours(),
        ]);
    }

    public function store(StoreLetterApplicationRequest $request, LetterService $letterService, CreateLetterApplicationAction $action): RedirectResponse
    {
        $submissionKey = $request->validated('submission_key');
        $submission = $submissionKey ? session("letter_submission.{$submissionKey}") : null;
        if ($submissionKey) {
            abort_unless(is_array($submission) && (int) ($submission['service_id'] ?? 0) === $letterService->id, 419);
            if (isset($submission['token'])) {
                return redirect()->route('letter-services.track.token', $submission['token']);
            }
        }
        $created = $action->execute($letterService, $request->validated());
        if ($submissionKey) {
            session(["letter_submission.{$submissionKey}" => ['service_id' => $letterService->id, 'token' => $created->trackingToken]]);
        }

        return redirect()->route('letter-services.application.documents', $created->trackingToken)
            ->with('tracking_pin', $created->trackingPin)
            ->with('new_application', true);
    }

    public function documents(
        string $token,
        PublicSiteService $site,
        LetterDocumentRequirementService $documentRequirements,
    ): View
    {
        $application = $this->fromToken($token);
        $site->shareLayout();
        $requirements = $documentRequirements->forApplication($application);

        return view('pages.letters.documents', compact('application', 'token', 'requirements'));
    }

    public function uploadDocuments(
        UploadLetterDocumentsRequest $request,
        string $token,
        LetterDocumentRequirementService $documentRequirements,
    ): RedirectResponse
    {
        $application = $this->fromToken($token);
        $requirements = $documentRequirements->forApplication($application);
        $uploadedRequirementKeys = $application->documents()
            ->where('upload_status', 'uploaded')
            ->pluck('requirement_key');
        foreach ($requirements as $index => $requirement) {
            $key = $requirement['key'] ?? 'requirement_'.($index + 1);
            if (($requirement['required'] ?? true) && !$request->hasFile("documents.{$index}") && !$uploadedRequirementKeys->contains($key)) {
                throw ValidationException::withMessages(["documents.{$index}" => 'Dokumen '.($requirement['label'] ?? 'persyaratan').' wajib diunggah.']);
            }
        }
        $disk = config('filesystems.letter_documents_disk', 'local');
        foreach ($requirements as $index => $requirement) {
            $file = $request->file("documents.{$index}");
            if (!$file) continue;
            $key = $requirement['key'] ?? 'requirement_'.($index + 1);
            $path = $file->store('layanan-surat/'.Str::slug($application->service->slug).'/'.$application->application_number.'/'.$key, $disk);
            LetterApplicationDocument::updateOrCreate(
                ['letter_application_id' => $application->id, 'requirement_key' => $key],
                ['public_id' => (string) Str::ulid(), 'label' => $requirement['label'] ?? 'Dokumen persyaratan', 'disk' => $disk, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'stored_extension' => $file->extension(), 'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize(), 'size_bytes' => $file->getSize(), 'upload_status' => 'uploaded', 'review_status' => 'pending_review', 'review_note' => null, 'reviewed_by' => null, 'reviewed_at' => null, 'uploaded_at' => now(), 'checksum_sha256' => hash_file('sha256', $file->getRealPath())]
            );
        }
        $application->forceFill(['status' => LetterApplicationStatus::Submitted, 'submitted_at' => now()])->save();
        $application->statusHistories()->create(['from_status' => LetterApplicationStatus::Draft, 'to_status' => LetterApplicationStatus::Submitted, 'created_at' => now()]);
        return redirect()->route('letter-services.track.token', $token)->with('success', 'Dokumen berhasil diunggah dan menunggu verifikasi petugas.');
    }

    public function presign(Request $request, string $token): \Illuminate\Http\JsonResponse
    {
        $application = $this->fromToken($token);
        abort_unless($application->status === LetterApplicationStatus::Draft, 409);
        $data = $request->validate(['requirement_key' => ['required', 'string', 'max:100'], 'original_name' => ['required', 'string', 'max:255'], 'mime_type' => ['required', 'in:image/jpeg,image/png,application/pdf'], 'size_bytes' => ['required', 'integer', 'min:1', 'max:5242880']]);
        $requirement = collect($application->service->requirements_json ?? [])->firstWhere('key', $data['requirement_key']);
        abort_unless($requirement, 422, 'Persyaratan dokumen tidak valid.');
        $extension = $data['mime_type'] === 'application/pdf' ? 'pdf' : ($data['mime_type'] === 'image/png' ? 'png' : 'jpg');
        $path = 'layanan-surat/'.Str::slug($application->service->slug).'/'.$application->application_number.'/'.$data['requirement_key'].'/'.Str::ulid().'.'.$extension;
        $disk = config('filesystems.letter_documents_disk', 'local');
        if (!method_exists(Storage::disk($disk), 'temporaryUploadUrl')) return response()->json(['message' => 'Presigned upload belum tersedia pada disk ini.'], 422);
        ['url' => $url, 'headers' => $headers] = Storage::disk($disk)->temporaryUploadUrl(
            $path,
            now()->addMinutes(10),
            ['ContentType' => $data['mime_type']]
        );
        $document = LetterApplicationDocument::updateOrCreate(
            ['letter_application_id' => $application->id, 'requirement_key' => $data['requirement_key']],
            ['public_id' => (string) Str::ulid(), 'label' => $requirement['label'],
             'disk' => $disk, 'path' => $path, 'original_name' => basename($data['original_name']),
             'stored_extension' => $extension, 'mime_type' => $data['mime_type'], 'size_bytes' => $data['size_bytes'],
             'file_size' => $data['size_bytes'], 'upload_status' => 'pending_upload', 'review_status' => 'pending_review',
             'uploaded_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'review_note' => null]
        );
        return response()->json(['document_id' => $document->public_id, 'upload_url' => $url, 'upload_headers' => $headers, 'expires_at' => now()->addMinutes(10)->toIso8601String()]);
    }

    public function completeDocument(
        Request $request,
        string $token,
    ): \Illuminate\Http\JsonResponse
    {
        $application = $this->fromToken($token);
        $document = $application->documents()->where('public_id', $request->input('document_id'))->firstOrFail();
        $disk = Storage::disk($document->disk);
        abort_unless($disk->exists($document->path), 422, 'Best belum ditemukan di penyimpanan.');
        $actualSize = $disk->size($document->path);
        abort_unless($actualSize <= 5242880 && $actualSize > 0, 422, 'Ukuran file tidak valid.');
        $document->update(['upload_status' => 'uploaded', 'uploaded_at' => now(), 'file_size' => $actualSize, 'size_bytes' => $actualSize]);
        return response()->json(['document' => ['id' => $document->public_id, 'name' => $document->original_name, 'size' => $actualSize, 'preview_url' => route('letter-services.application.documents.preview', [$token, $document->public_id])]]);
    }

    public function previewDocument(string $token, string $document): \Symfony\Component\HttpFoundation\Response
    {
        $application = $this->fromToken($token);
        $file = $application->documents()->where('public_id', $document)->firstOrFail();
        $url = Storage::disk($file->disk)->temporaryUrl($file->path, now()->addMinutes(5));
        return redirect()->away($url)->header('Cache-Control', 'private, no-store');
    }

    public function edit(string $token, PublicSiteService $site, LetterFormSchemaService $schemas): View
    {
        $application = $this->fromToken($token);
        abort_unless($application->canBeEditedByApplicant(), 403);
        $site->shareLayout();

        return view('pages.letters.edit', ['application' => $application, 'token' => $token, 'fields' => $schemas->fields($application->service)]);
    }

    public function update(UpdateLetterApplicationRequest $request, string $token, UpdateLetterApplicationAction $action): RedirectResponse
    {
        $application = $this->fromToken($token);
        $action->execute($application, $request->validated());

        return redirect()->route('letter-services.track.token', $token)->with('success', 'Perbaikan permohonan berhasil dikirim.');
    }

    public function cancel(string $token, ChangeLetterApplicationStatusAction $action): RedirectResponse
    {
        $application = $this->fromToken($token);
        $action->execute($application, LetterApplicationStatus::Cancelled, ['public_note' => 'Dibatalkan oleh pemohon.']);

        return redirect()->route('letter-services.track.token', $token)->with('success', 'Permohonan berhasil dibatalkan.');
    }

    private function fromToken(string $token): LetterApplication
    {
        return LetterApplication::query()->with('service')->where('tracking_token_hash', hash('sha256', $token))->firstOrFail();
    }
}
