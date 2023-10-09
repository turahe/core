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
  <div
    class="relative justify-between last:!mb-0 odd:my-4 odd:border-t odd:border-neutral-200 odd:pt-4 dark:odd:border-neutral-600 sm:flex sm:items-center"
  >
    <div
      class="mb-1 flex w-full flex-col justify-start sm:mr-3 sm:mb-0 sm:w-auto sm:flex-row sm:items-center sm:space-x-3"
    >
      <div class="mb-1 flex items-center sm:mb-0">
        <div
          v-if="rule.helpText"
          class="mr-1 -ml-1"
          v-i-tooltip.bottom.light="rule.helpText"
        >
          <Icon
            icon="QuestionMarkCircle"
            class="h-4 w-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
          />
        </div>
        <label
          class="whitespace-nowrap text-sm font-medium text-neutral-800 dark:text-neutral-200"
          v-text="rule.label"
        />
      </div>

      <ICustomSelect
        v-if="showOperands"
        class="mb-1 w-52 sm:mb-0"
        size="sm"
        :disabled="readOnly"
        :clearable="false"
        v-model="selectFieldOperand"
        :option-label-provider="operand => operand[operand.labelKey]"
        :options="rule.operands"
        @option:selected="handleOperandSelected"
      />

      <IFormSelect
        v-if="!rule.isStatic"
        size="sm"
        v-show="!hasOnlyOneOperator"
        :value="query.operator"
        @input="
          $store.commit('filters/UPDATE_QUERY_OPERATOR', {
            query: query,
            value: $event,
          })
        "
        :disabled="readOnly"
        class="w-auto bg-none"
      >
        <option
          v-for="operator in operators"
          :key="operator"
          :value="operator"
          :selected="operator == query.operator"
        >
          {{
            labels.operatorLabels[operator]
              ? labels.operatorLabels[operator]
              : operator
          }}
        </option>
      </IFormSelect>
    </div>

    <div class="grow" v-show="!isNullable">
      <component
        :is="rulesComponents[operand ? operand.rule.component : rule.component]"
        :query="query"
        :rule="operand ? operand.rule : rule"
        :index="index"
        :labels="labels"
        :operand="operand"
        :read-only="readOnly"
        :operator="query.operator"
        :is-nullable="isNullable"
        :is-between="isBetween"
      />
    </div>
    <IButtonIcon
      v-if="!readOnly"
      @click="requestRemove"
      icon="X"
      class="absolute right-0 top-1 sm:relative sm:right-auto sm:top-auto sm:ml-3"
      icon-class="h-4 w-4 sm:h-5 sm:w-5"
    />
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
  name: 'rule',
}
</script>
<script setup>
import { ref, computed, onMounted } from 'vue'
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'

import NumericRule from './Rules/NumericRule.vue'
import CheckboxRule from './Rules/CheckboxRule.vue'
import DateRule from './Rules/DateRule.vue'
import NumberRule from './Rules/NumberRule.vue'
import RadioRule from './Rules/RadioRule.vue'
import SelectRule from './Rules/SelectRule.vue'
import MultiSelectRule from './Rules/MultiSelectRule.vue'
import TextRule from './Rules/TextRule.vue'
import StaticRule from './Rules/StaticRule.vue'
import NullableRule from './Rules/NullableRule.vue'
import { isNullableOperator, isBetweenOperator } from './utils'
import { useStore } from 'vuex'

const emit = defineEmits(['child-deletion-requested'])

const props = defineProps(['query', 'index', 'rule', 'labels', 'readOnly'])

const rulesComponents = {
  'numeric-rule': NumericRule,
  'checkbox-rule': CheckboxRule,
  'date-rule': DateRule,
  'number-rule': NumberRule,
  'radio-rule': RadioRule,
  'select-rule': SelectRule,
  'multi-select-rule': MultiSelectRule,
  'text-rule': TextRule,
  'static-rule': StaticRule,
  'nullable-rule': NullableRule,
}

const store = useStore()

const selectFieldOperand = ref(null)

/**
 * Get the selected opereand
 */
const operand = computed(() =>
  find(props.rule.operands, ['value', props.query.operand])
)

/**
 * Inicates whether the rule as operand with rule
 */
const hasOperandWithRule = computed(() => operand.value && operand.value.rule)

/**
 * Get the rule operators
 */
const operators = computed(() => {
  if (!hasOperandWithRule.value) {
    return props.rule.operators
  }

  return operand.value.rule.operators
})

/**
 * Indicates whether the rules has only one operator
 */
const hasOnlyOneOperator = computed(() => operators.value.length === 1)

/**
 * Indicates whether the operands should be shown
 */
const showOperands = computed(() => {
  if (
    props.rule.isStatic ||
    (props.rule.hasOwnProperty('hideOperands') && props.rule.hideOperands)
  ) {
    return false
  }

  return props.rule.operands && props.rule.operands.length > 0
})

/**
 * Indicates whether the rule operator is between
 */
const isBetween = computed(() => isBetweenOperator(props.query.operator))

/**
 * Indicates whether the rule operator is nullable
 */
const isNullable = computed(() => isNullableOperator(props.query.operator))

/**
 * Handle the operand selected event
 */
function handleOperandSelected(operand) {
  store.commit('filters/UPDATE_QUERY_OPERAND', {
    query: props.query,
    value: operand[operand.valueKey],
  })

  // When operand is changed, set the first operator as active
  if (operand.rule.operators && operand.rule.operators.length > 0) {
    store.commit('filters/UPDATE_QUERY_OPERATOR', {
      query: props.query,
      value: operand.rule.operators[0],
    })
  }
}

/**
 * Request rule remove
 *
 * @return {Void}
 */
function requestRemove() {
  emit('child-deletion-requested', props.index)
}

onMounted(() => {
  selectFieldOperand.value = operand.value && cloneDeep(operand.value)
})
</script>
