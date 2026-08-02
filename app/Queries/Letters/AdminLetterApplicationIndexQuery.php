<?php

namespace App\Queries\Letters;

use App\Models\LetterApplication;
use Illuminate\Http\Request;

class AdminLetterApplicationIndexQuery
{
    public function paginate(Request $request)
    {
        return LetterApplication::query()->with(['service', 'assignee'])
            ->when($request->string('q')->isNotEmpty(), fn ($q) => $q->where(fn ($inner) => $inner->where('application_number', 'like', '%'.$request->string('q').'%')->orWhere('applicant_name', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('service'), fn ($q) => $q->where('letter_service_id', $request->service))
            ->when($request->filled('assigned_to'), fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->boolean('unassigned'), fn ($q) => $q->whereNull('assigned_to'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('submitted_at', '>=', $request->from))
            ->when($request->filled('until'), fn ($q) => $q->whereDate('submitted_at', '<=', $request->until))
            ->orderByRaw("CASE WHEN status = 'submitted' THEN 0 WHEN status = 'draft' THEN 1 ELSE 2 END")
            ->orderByRaw('COALESCE(submitted_at, created_at) DESC')
            ->paginate(20)
            ->withQueryString();
    }
}
