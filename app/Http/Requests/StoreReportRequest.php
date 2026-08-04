<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Report::class);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'in:spam,abus,hors_sujet,autre'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
