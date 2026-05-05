<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class XRaysRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dateOfXrays'                      => 'required|date|before_or_equal:today',
            'tiles.lateral-ceph.filled'        => 'nullable|boolean',
            'tiles.panoramic.filled'           => 'required_without:tiles.full-mouth-series.filled|boolean',
            'tiles.full-mouth-series.filled'   => 'required_without:tiles.panoramic.filled|boolean',
            // TODO: add S3 key validation once upload is wired
        ];
    }
}
