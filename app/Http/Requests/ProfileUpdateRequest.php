<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'profile_photo' => ['nullable', 'image', 'max:10000'],
            'remove_profile_photo' => ['sometimes', 'boolean'],
        ];

        if ($this->user()?->role === 'Student') {
            $rules['student_number'] = [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique(User::class, 'student_number')->ignore($this->user()->id),
            ];
            $rules['phone'] = ['nullable', 'string', 'max:40'];
            $rules['program'] = ['nullable', 'string', 'max:255'];
            $rules['year_level'] = ['nullable', 'integer', 'min:1', 'max:8'];
        }

        return $rules;
    }
}
