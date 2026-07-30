<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class ReviewLetterDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['review_status' => ['required', 'in:approved,rejected,pending'], 'review_note' => ['required_if:review_status,rejected', 'nullable', 'string', 'max:1000']]; }
    public function messages(): array { return ['review_note.required_if' => 'Isi catatan perbaikan sebelum meminta dokumen diganti.']; }
}
