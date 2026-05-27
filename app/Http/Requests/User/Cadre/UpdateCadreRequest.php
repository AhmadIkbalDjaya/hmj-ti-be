<?php

namespace App\Http\Requests\User\Cadre;

use App\Enums\CadreStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCadreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'batch' => 'required|string|max:255',
            'status' => ['required', new Enum(CadreStatus::class)],
        ];
    }
}
