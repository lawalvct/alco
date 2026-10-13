<?php

namespace App\Http\Requests\Leads;

use App\Models\EngagementPackage;
use App\Models\Lead;
use Illuminate\Validation\Rule;

class ConsultationLeadRequest extends LeadRequest
{
    public const TAILORED = 'tailored';

    public function rules(): array
    {
        $selectable = collect(EngagementPackage::publicCatalogue())
            ->where('selectable', true)
            ->pluck('slug')
            ->all();

        return [
            ...parent::rules(),
            'package' => ['nullable', 'string', Rule::in([...$selectable, self::TAILORED])],
            'preferred_contact_method' => ['nullable', Rule::in(array_keys(Lead::CONTACT_METHODS))],
            'availability' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'package.in' => 'That package is no longer available to choose. Pick another option, or ask for a tailored engagement.',
        ];
    }
}
