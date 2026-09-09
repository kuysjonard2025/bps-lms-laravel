<?php

namespace App\Livewire\Helpers;

trait SanitizesInputs
{
    public function cleanFields(array $fields, string $symbols = '!@#$%^&*()_+`~{}[]|\\:;"\'<>,.?/')
    {
        foreach ($fields as $field) {
            if (is_string($this->{$field})) {
                // Trim symbols and convert the actual value to lowercase
                $this->{$field} = strtolower(trim($this->{$field}, $symbols));
            }
        }
    }
}
