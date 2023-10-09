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

namespace Modules\Core;

class TreeBuilder
{
    /**
     * The parent id key name in the array
     *
     * @var string
     */
    protected $parentIdKeyName = 'parent_id';

    /**
     * The children key name when createing the child array
     *
     * @var string
     */
    protected $childrenKeyName = 'children';

    /**
     * The main id key name
     *
     * @var string
     */
    protected $mainIdKeyName = 'id';

    /**
     * Build the tree
     *
     * @return array
     */
    public function build(array $elements, int $parentId = 0)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element[$this->parentIdKeyName] == $parentId) {
                $children = $this->build($elements, $element[$this->mainIdKeyName]);

                if ($children) {
                    $element[$this->childrenKeyName] = $children;
                }

                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * Set parent id key name
     */
    public function setParentIdKeyName(string $name)
    {
        $this->parentIdKeyName = $name;

        return $this;
    }

    /**
     * Set children key name
     */
    public function setChildrenKeyName(string $name)
    {
        $this->childrenKeyName = $name;

        return $this;
    }

    /**
     * Set main id key name
     */
    public function setMainIdKeyName(string $name)
    {
        $this->mainIdKeyName = $name;

        return $this;
    }
}
