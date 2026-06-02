<?php

namespace App\Http\Requests\User\Member;

use App\Enums\Gender;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreMemberRequest extends FormRequest
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
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'gender' => ['required', new Enum(Gender::class)],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'position_id' => 'required|integer|exists:positions,id',
        ];
    }
}
