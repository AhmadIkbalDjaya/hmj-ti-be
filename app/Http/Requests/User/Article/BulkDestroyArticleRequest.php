<?php

namespace App\Http\Requests\User\Article;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyArticleRequest extends FormRequest
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
            'ids.*' => 'integer|exists:articles,id',
            'exclude_ids' => 'sometimes|array',
            'exclude_ids.*' => 'integer|exists:articles,id',
            'filters' => 'sometimes|array',
            'filters.search' => 'sometimes|nullable|string',
            'filters.is_active' => 'sometimes|nullable|boolean',
            'filters.is_featured' => 'sometimes|nullable|boolean',
        ];
    }
}
