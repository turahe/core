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

return [
    'filters'                    => 'Filters',
    'apply'                      => 'Apply Filters',
    'save_and_apply'             => 'Save and Apply',
    'create'                     => 'Create new filter',
    'filter_by'                  => 'Filter by',
    'edit'                       => 'Edit',
    'save_as_new'                => 'Save filter?',
    'available'                  => 'Available Filters',
    'not_available'              => 'No filters available, get started by creating a filter.',
    'cannot_be_shared'           => 'This filter consist of rules (:rules) that may not be always available for all organization members, for this reason, the filter cannot be shared with other organization members.',
    'search'                     => 'Search filters...',
    'name'                       => 'Filter Name',
    'new'                        => 'New Filter',
    'clear_rules'                => 'Clear Rules',
    'clear_applied'              => 'Clear Filter',
    'static_rule_indicator'      => 'Static Rule',
    'is_readonly'                => 'This filter is read-only filter with ability to be marked as default, other modifications of the filter are disabled, if you need to extend this filter, create new a one instead.',
    'system_default_delete_info' => 'You cannot delete application default filter',

    'country' => 'Country',

    'share' => [
        'with'          => 'Shared with',
        'private'       => 'Private',
        'everyone'      => 'Everyone',
        'private_info'  => 'Only the creator can see this filter',
        'everyone_info' => 'All users can see and use this filter',
    ],

    'placeholders' => [
        'enter'                => 'Enter :label',
        'choose'               => 'Choose :label',
        'choose_with_multiple' => 'Choose one or more :label',
        'select_date'          => 'Select date',
    ],

    'conditions' => [
        'or'  => 'or',
        'and' => 'and',
    ],

    'show_matching_records_conditions' => 'Show records that match :match_type of these conditions:',
    'or_match_any_conditions'          => ':condition match :match_type of these conditions:',

    'is_default'        => 'This is a default filter?',
    'mark_as_default'   => 'Mark as default',
    'unmark_as_default' => 'Unmark as default',
    'filters'           => 'Filters',
    'edit'              => 'Edit',
    'edit_filter'       => 'Edit filter',
    'match_type'        => 'Match Type',
    'match_type_all'    => 'All',
    'match_type_any'    => 'Any',
    'add_filter'        => 'Add Filter',
    'add_condition'     => 'Add Condition',
    'add_group'         => 'Add Group',
    'operators'         => [
        'is'               => 'is',
        'was'              => 'was',
        'equal'            => 'is equal to',
        'not_equal'        => 'is not equal to',
        'in'               => 'is any of',
        'not_in'           => 'is none of',
        'less'             => 'is less than',
        'less_or_equal'    => 'is less or equal than',
        'greater'          => 'is greater than',
        'greater_or_equal' => 'is greater or equal than',
        'between'          => 'is between',
        'not_between'      => 'is not between',
        'begins_with'      => 'begins with',
        'not_begins_with'  => 'not begins with',
        'contains'         => 'contains',
        'not_contains'     => 'not contains',
        'ends_with'        => 'ends with',
        'not_ends_with'    => 'not ends with',
        'is_empty'         => 'is empty',
        'is_not_empty'     => 'is not empty',
        'is_null'          => 'is unknown',
        'is_not_null'      => 'is known',
    ],
    // For current user custom identifier "Me"
    'me' => 'Me',
];
