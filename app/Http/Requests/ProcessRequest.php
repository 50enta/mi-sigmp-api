<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ProcessRequest extends FormRequest
{
    /**
     * The registrar is always the person linked to the authenticated user.
     * It must not be selected or supplied by the client.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'abertoPor' => $this->user()?->pessoa_id,
        ]);
    }
}
