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
        $password = Password::min(config('security.password.min_length', 12))
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();

        if (config('security.password.check_compromised', false)) {
            $password->uncompromised();
        }

        return [
            $required ? 'required' : 'nullable',
            'confirmed',
            'string',
            'max:128',
            $password,
        ];
    }

    public static function profileImage(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
    }

    public static function safeDocument(): array
    {
        return [
            'file',
            'max:10240',
            function (string $attribute, mixed $value, \Closure $fail): void {
                $extension = strtolower((string) ($value?->getClientOriginalExtension() ?: $value?->extension()));
                $mime = strtolower((string) ($value?->getMimeType() ?: $value?->getClientMimeType()));

                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                $allowedMimes = [
                    'application/pdf',
                    'application/x-pdf',
                    'application/octet-stream',
                    'image/jpeg',
                    'image/png',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];

                if (! in_array($extension, $allowedExtensions, true)) {
                    $fail('فقط فایل‌های PDF، عکس و Word قابل قبول است.');

                    return;
                }

                if ($mime !== '' && ! in_array($mime, $allowedMimes, true)) {
                    $fail('نوع فایل قابل قبول نیست. لطفاً PDF، عکس یا Word ارسال کنید.');
                }
            },
        ];
    }

    public static function financeAttachment(): array
    {
        return ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'];
    }
}
