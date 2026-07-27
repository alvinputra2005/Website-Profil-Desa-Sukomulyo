<?php

namespace App\Actions\Officials;

use App\Models\Official;
use Illuminate\Support\Facades\DB;

class MoveOfficialAction
{
    public function execute(Official $official, string $direction): void
    {
        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';
        $neighbor = Official::where('display_order', $operator, $official->display_order)
            ->orderBy('display_order', $order)
            ->first();

        if (! $neighbor) {
            return;
        }

        DB::transaction(function () use ($official, $neighbor): void {
            $currentOrder = $official->display_order;
            $official->update(['display_order' => $neighbor->display_order]);
            $neighbor->update(['display_order' => $currentOrder]);
        });
    }
}
