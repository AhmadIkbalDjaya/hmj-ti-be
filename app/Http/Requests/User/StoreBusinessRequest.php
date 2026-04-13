<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
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
            'title' => 'required|string',
            'slug' => 'required|string|unique:businesses',
            'description' => 'required|string',
            'price' => 'required|integer',
            'image' => 'required|image|mimes:jpg,jpeg,png',
            'whatsapp' => 'required|string',
            'is_active' => 'required|boolean',
        ];
    }
}
