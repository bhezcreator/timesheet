<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;

class SecureString implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Récupération du nom traduit du champ (ex: recherche dans validation.attributes.titre)
        $attributeName = Lang::has("validation.attributes.{$attribute}")
            ? __('validation.attributes.' . $attribute)
            : $attribute;

        // 1. Protection XSS
        if (preg_match('/<script|javascript:|onclick|onerror/i', $value)) {
            $fail("Le champ {$attributeName} contient du code ou des scripts non autorisés.");
            return;
        }

        $clean = strip_tags(trim($value));

        // 2. Validation Regex avec gestion des retours à la ligne (\R)
        if (!preg_match('/^[a-z0-9\-\._ a-z0-9àâäéèêëîïôöùûü;:!,?çÂÆÇÈÉÊËÎÏÔŒÙÛÜ@\'"\R]+$/i', $clean)) {
            $fail("Le champ {$attributeName} contient des caractères spéciaux non autorisés.");
        }
    }
}
