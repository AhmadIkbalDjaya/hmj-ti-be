<?php

namespace App\Http\Requests\User;

use App\Enums\CadreStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BulkDestroyCadreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'select_all' => 'sometimes|boolean',
            'ids' => 'required_without:select_all|array',
            'ids.*' => 'integer|exists:cadres,id',
            'exclude_ids' => 'sometimes|array',
            'exclude_ids.*' => 'integer|exists:cadres,id',
            'filters' => 'sometimes|array',
            'filters.search' => 'sometimes|nullable|string',
            'filters.batch' => 'sometimes|nullable|string',
            'filters.status' => ['sometimes', 'nullable', new Enum(CadreStatus::class)],
        ];
    }
}
