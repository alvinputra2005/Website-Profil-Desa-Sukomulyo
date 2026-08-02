<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VillageComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VillageCommentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', VillageComment::class);

        $query = VillageComment::query();

        if ($search = $request->query('q')) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $items = $query->latest()->paginate(20)->withQueryString();
        $pendingCount = VillageComment::where('status', 'pending')->count();

        return view('admin.comments.index', compact('items', 'pendingCount'));
    }

    public function show(VillageComment $comment)
    {
        $this->authorize('view', $comment);

        return view('admin.comments.show', compact('comment'));
    }

    public function review(Request $request, VillageComment $comment)
    {
        $this->authorize('update', $comment);

        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'pending'])],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $comment->forceFill([
            'status' => $data['status'],
            'is_visible' => $data['status'] === 'approved',
            'review_note' => $data['review_note'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ])->save();

        return redirect()
            ->route('admin.comments.show', $comment)
            ->with('success', 'Komentar berhasil ditinjau.');
    }

    public function destroy(VillageComment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()->route('admin.comments.index')->with('success', 'Komentar dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorize('viewAny', VillageComment::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:village_comments,id'],
        ]);

        $items = VillageComment::whereKey($data['ids'])->get();

        DB::transaction(function () use ($items): void {
            $items->each(function (VillageComment $comment): void {
                $this->authorize('delete', $comment);
                $comment->delete();
            });
        });

        return redirect()->route('admin.comments.index')->with('success', $items->count().' komentar dihapus.');
    }
}
