<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class SecurityRules
{
    public static function phone(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:30',
            'regex:/^[0-9+\-\s()]{7,30}$/',
        ];
    }

    public static function strongPassword(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'confirmed',
            Password::min(8)->mixedCase()->numbers(),
        ];
    }

    public static function profileImage(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
    }

    public static function safeDocument(): array
    {
        return ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'];
    }

    public static function financeAttachment(): array
    {
        return ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'];
    }
}
