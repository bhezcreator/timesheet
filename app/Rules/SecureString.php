<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;

class SecureString implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $attributeName = Lang::has("validation.attributes.{$attribute}")
            ? __('validation.attributes.' . $attribute)
            : $attribute;

        // 1. Protection XSS
        if (preg_match('/<script|javascript:|onclick|onerror/i', $value)) {
            $fail("Le champ {$attributeName} contient du code ou des scripts non autorisés.");
            return;
        }

        $clean = strip_tags(trim($value));

        // 2. Validation - \R à l'extérieur de la classe
        if (! preg_match('/^[a-z0-9\-\._ a-z0-9àâäéèêëîïôöùûü;:!,?çÂÆÇÈÉÊËÎÏÔŒÙÛÜ@\'"]+$/i', $clean)) {
            // Si le test échoue, vérifier si c'est à cause des sauts de ligne
            if (preg_match('/\R/', $clean)) {
                // Autoriser les sauts de ligne en supprimant les autres caractères interdits
                $cleanWithoutNewlines = preg_replace('/\R/', '', $clean);
                if (! preg_match('/^[a-z0-9\-\._ a-z0-9àâäéèêëîïôöùûü;:!,?çÂÆÇÈÉÊËÎÏÔŒÙÛÜ@\'"]+$/i', $cleanWithoutNewlines)) {
                    $fail("Le champ {$attributeName} contient des caractères spéciaux non autorisés.");
                }
            } else {
                $fail("Le champ {$attributeName} contient des caractères spéciaux non autorisés.");
            }
        }
    }
}
