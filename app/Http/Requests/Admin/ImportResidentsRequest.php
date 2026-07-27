<?php

namespace App\Http\Requests\Admin;

class ImportResidentsRequest extends PopulationRequest
{
    public function rules(): array
    {
        return ['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240']];
    }
}
