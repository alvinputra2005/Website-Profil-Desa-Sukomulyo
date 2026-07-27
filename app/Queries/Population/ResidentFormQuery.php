<?php

namespace App\Queries\Population;

use App\Models\FamilyCard;
use App\Models\Household;
use App\Models\Resident;

class ResidentFormQuery
{
    public function data(Resident $resident): array
    {
        return [
            'resident' => $resident,
            'families' => FamilyCard::with('head')->where('is_active', true)->orderBy('family_card_number')->get(),
            'households' => Household::with('head')->where('is_active', true)->orderBy('household_number')->get(),
        ];
    }
}
