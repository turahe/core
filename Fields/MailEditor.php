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

namespace Modules\Core\Fields;

class MailEditor extends Field
{
    /**
     * Field component
     */
    public ?string $component = 'mail-editor-field';

    /**
     * Resolve the field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    public function resolve($model)
    {
        return clean(parent::resolve($model));
    }
}
