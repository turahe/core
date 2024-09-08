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

namespace Turahe\Core\Changelog;

class PivotAttached extends PivotLogger
{
    /**
     * Log pivot attached event
     *
     * Used e.q. when attaching company
     *
     * @param  \Turahe\Core\Models\Model  $attachedTo  The model where the pivot is attached
     * @param  array  $pivotIds  Attached pivot IDs
     * @param  string  $relation  The relation name the event occured
     * @return null
     */
    public static function log($attachedTo, $pivotIds, $relation)
    {
        $pivotModels = static::getRelatedPivotIds($attachedTo, $pivotIds, $relation);

        foreach ($pivotModels as $pivotModel) {
            static::perform($attachedTo, $pivotModel, $relation, 'attached');
        }
    }
}
