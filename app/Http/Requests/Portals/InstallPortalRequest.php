<?php

namespace App\Http\Requests\Portals;

use Illuminate\Foundation\Http\FormRequest;

class InstallPortalRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'refresh_token' => 'required|string',
            'expiry_date' => 'required|integer',
        ];
    }
}
