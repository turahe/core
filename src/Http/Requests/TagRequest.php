<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Turahe\Core\Http\Requests;

use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Turahe\Core\Rules\SwatchColorRule;
use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => array_filter([
                'required', 'string', 'max:191',  function (string $attribute, mixed $value, Closure $fail) {
                    if (str_contains($value, ',')) {
                        $fail(__('core::tags.validation.coma'))->translate();
                    }
                },
                $this->getUniqueTagRule(),
            ]),
            'swatch_color' => ['required', new SwatchColorRule],
        ];
    }

    /**
     * Get the unique tag validation rule.
     */
    protected function getUniqueTagRule(): ?Unique
    {
        if ($this->isMethod('POST')) {
            return null;
        }

        $tag = $this->route('tag');
        $uniqueRule = Rule::unique('tags', 'name')->ignore($tag);

        if ($tag->type) {
            return $uniqueRule->where('type', $tag->type);
        }

        return $uniqueRule->using(fn ($query) => $query->whereNull('type'));
    }
}
