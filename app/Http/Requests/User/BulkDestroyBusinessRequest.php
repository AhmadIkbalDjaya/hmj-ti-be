<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyBusinessRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'select_all' => 'sometimes|boolean',
            'ids' => 'required_without:select_all|array',
            'ids.*' => 'integer|exists:businesses,id',
            'exclude_ids' => 'sometimes|array',
            'exclude_ids.*' => 'integer|exists:businesses,id',
            'filters' => 'sometimes|array',
            'filters.search' => 'sometimes|nullable|string',
            'filters.is_active' => 'sometimes|nullable|boolean',
        ];
    }
}
