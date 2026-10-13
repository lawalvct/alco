<?php

namespace App\Http\Requests\Leads;

class ContactLeadRequest extends LeadRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'message.required' => 'Please tell us briefly how we can help.',
        ];
    }
}
