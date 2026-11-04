<?php

namespace App\Http\Requests\V1\Portals;

use Illuminate\Foundation\Http\FormRequest;

class InstallPortalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'refresh_token' => 'required|string',
            'expiry_date' => 'required|integer',
        ];
    }
}
