<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:100'],
            'email'               => ['nullable', 'email', 'unique:users,email', 'required_without:phone'],
            'phone'               => ['nullable', 'string', 'max:20', 'unique:users,phone', 'required_without:email'],
            'password'            => ['required', 'string', 'min:8', 'confirmed'],
            'language_preference' => ['nullable', 'in:fr,en'],
        ];
    }

    public function messages(): array
    {
        $lang = $this->input('language_preference', 'fr');

        return $lang === 'fr' ? [
            'name.required'    => 'Le nom est obligatoire.',
            'email.unique'     => 'Cette adresse e-mail est déjà utilisée.',
            'phone.unique'     => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min'     => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ] : [
            'name.required'    => 'Name is required.',
            'email.unique'     => 'This email address is already taken.',
            'phone.unique'     => 'This phone number is already taken.',
            'password.min'     => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}
