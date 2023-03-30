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
        $clientId = $this->request->get('client_id');
        $clientSecret = $this->request->get('client_secret');

        if (config('app.client_id') != $clientId
            ||
            config('app.client_secret') != $clientSecret
        ) {
            return $this->onError(__('portal.invalid_credentials'));
        }

        return [
            'token' => 'required|string',
            'refresh_token' => 'required|string',
            'expiry_date' => 'required|integer',
            'domain' => 'required|string|unique:portals,domain',
        ];
    }
}
