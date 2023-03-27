<?php


namespace App\Http\Requests;

use App\Trait\HelperTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BaseFormRequest extends FormRequest implements BaseFormRequestInterface
{
    use HelperTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return JsonResponse|bool
     */
    public function authorize(): JsonResponse|bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
        ];
    }


    /**
     * Get the error messages for the defined validation rules.*
     * @param Validator $validator
     * @return array
     */
    public function failedValidation(Validator $validator): array
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => true
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
