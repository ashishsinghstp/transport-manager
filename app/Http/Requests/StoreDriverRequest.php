<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'pay_rate' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'hst_number' => ['nullable', 'string', 'max:20'],
            'document' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png,doc,docx,xls,xlsx', 'max:10240'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
