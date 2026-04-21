<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class PhotographsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dateOfPhotos'        => 'required|date|before_or_equal:today',
            'tiles'               => 'required|array',
            'tiles.*.filled'      => 'required|boolean',
            // TODO: add S3 key validation once upload is wired
        ];
    }
}
