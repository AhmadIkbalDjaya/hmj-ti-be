<?php

namespace App\Http\Requests\User\Position;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'slug' => ['required', 'string', Rule::unique('positions')->ignore($this->route('position'))],
            'parent_id' => 'nullable|integer|exists:positions,id',
            'level' => 'required|integer',
            'order_index' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ];
    }
}
