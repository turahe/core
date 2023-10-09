<!--
  - This source code is the proprietary and confidential information of
  - Nur Wachid. You may not disclose, copy, distribute,
  -  or use this code without the express written permission of
  - Nur Wachid.
  -
  - Copyright (c) 2023.
  -
  -
  -->

<template>
  <div>
    <QueryBuilderGroup
      :index="0"
      :query="value"
      :rules="mergedRules"
      :max-depth="maxDepth"
      :read-only="readOnly"
      :depth="depth"
      :labels="mergedLabels"
    />
    <slot></slot>
  </div>
</template>
<script setup>
import { ref, computed } from 'vue'
import QueryBuilderGroup from './QueryBuilderGroup.vue'
import { useQueryBuilderLabels } from './useLabels'
import { useStore } from 'vuex'

const { labels: defaultLabels } = useQueryBuilderLabels()

const props = defineProps({
  rules: Array,
  identifier: { type: String, required: true },
  view: { type: String, required: true },
  readOnly: { type: Boolean, default: false },
  labels: Object,
  maxDepth: {
    type: Number,
    default: 3, // only 3 is supported ATM
    validator: function (value) {
      return value >= 1
    },
  },
})

const DefaultNumberAndDateFieldOperators = [
  'equal',
  'not_equal',
  'less',
  'less_or_equal',
  'greater',
  'greater_or_equal',
  'between',
  'not_between',
  'is_null',
  'is_not_null',
]

const ruleTypes = {
  text: {
    operators: [
      'equal',
      'not_equal',
      'begins_with',
      'not_begins_with',
      'contains',
      'not_contains',
      'ends_with',
      'not_ends_with',
      'is_empty',
      'is_not_empty',
      'is_null',
      'is_not_null',
    ],
    inputType: 'text',
    id: 'text-field',
  },
  number: {
    operators: DefaultNumberAndDateFieldOperators,
    inputType: 'number',
    id: 'number-field',
  },
  date: {
    operators: DefaultNumberAndDateFieldOperators.concat(['is']),
    inputType: 'date',
    id: 'date-field',
  },
  numeric: {
    operators: DefaultNumberAndDateFieldOperators,
    inputType: 'numeric',
    id: 'numeric-field',
  },
  radio: {
    operators: ['equal'],
    options: [],
    inputType: 'radio',
    id: 'radio-field',
  },
  checkbox: {
    operators: ['in'],
    options: [],
    inputType: 'checkbox',
    id: 'checkbox-field',
  },
  select: {
    operators: ['equal', 'not_equal'],
    options: [],
    inputType: 'select',
    id: 'select-field',
  },
  'multi-select': {
    operators: ['in', 'not_in'],
    options: [],
    inputType: 'select',
    id: 'multi-select-field',
  },
}

const store = useStore()

const depth = ref(1)

/**
 * Currently filter rules in the builder
 */
const value = computed(() =>
  store.getters['filters/getBuilderRules'](props.identifier, props.view)
)

/**
 * Merged labels in case additional labels are passed as prop
 */
const mergedLabels = computed(() =>
  Object.assign({}, defaultLabels, props.labels)
)

const mergedRules = computed(() => {
  let labels = []

  if (!props.rules) {
    return labels
  }

  props.rules.forEach(rule => {
    if (typeof ruleTypes[rule.type] !== 'undefined') {
      labels.push(Object.assign({}, ruleTypes[rule.type], rule))
    } else {
      labels.push(rule)
    }
  })

  return labels
})

if (Object.keys(value.value).length === 0) {
  store.commit('filters/RESET_BUILDER_RULES', {
    identifier: props.identifier,
    view: props.view,
  })
}
</script>
