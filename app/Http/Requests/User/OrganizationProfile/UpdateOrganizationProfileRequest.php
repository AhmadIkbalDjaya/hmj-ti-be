<?php

namespace App\Http\Requests\User\OrganizationProfile;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationProfileRequest extends FormRequest
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
            'goal' => 'required|string',
            'vision' => 'required|string',
            'missions' => 'required|array|min:1',
            'missions.*' => 'required|string',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'secondary_image' => 'nullable|image|mimes:jpg,jpeg,png',
        ];
    }
}
