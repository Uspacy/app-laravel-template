<?php

namespace App\Http\Requests\Portal;

use App\Http\Requests\BaseFormRequest;

class InstallPortalRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
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
