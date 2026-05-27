<?php

namespace App\Http\Requests\User\Complaint;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'select_all' => 'sometimes|boolean',
            'ids' => 'required_without:select_all|array',
            'ids.*' => 'integer|exists:complaints,id',
            'exclude_ids' => 'sometimes|array',
            'exclude_ids.*' => 'integer|exists:complaints,id',
            'filters' => 'sometimes|array',
            'filters.search' => 'sometimes|nullable|string',
        ];
    }
}
