<?php

namespace App\Livewire\Helpers;

trait SanitizesInputs
{
    /**
     * Clean and sanitize specified Livewire component fields.
     * Removes specified special symbols and converts the string to lowercase.
     *
     * @param array $fields List of field names (e.g., ['name', 'accession_number'])
     * @param string $symbols Regex character class of symbols to remove
     * @return void
     */
    public function cleanFields(array $fields, string $symbols = '!@#$%^&*()_+`~{}[]|\\\\:;"\'<>,.?/ ')
    {
        foreach ($fields as $field) {
            if (isset($this->{$field}) && is_string($this->{$field})) {
                // Escape characters for regex safety
                $pattern = '/[' . preg_quote($symbols, '/') . ']/u';

                // Remove specified symbols from anywhere in the string and convert to lowercase
                $cleaned = preg_replace($pattern, '', $this->{$field});
                $this->{$field} = mb_strtolower(trim($cleaned));
            }
        }
    }
}
