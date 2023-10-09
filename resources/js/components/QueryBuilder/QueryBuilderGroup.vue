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
    :class="[
      'border-l-4 px-4 py-3',
      borderClasses,
      bgClasses,
      {
        'mb-4 mt-5': depth > 1,
      },
    ]"
  >
    <div
      class="flex items-center justify-between sm:flex-nowrap"
      :class="{ 'mb-4': readOnly }"
    >
      <i18n-t
        scope="global"
        class="flex w-full flex-wrap items-start text-sm font-medium text-neutral-800 dark:text-neutral-100 sm:items-center"
        :keypath="
          depth <= 1
            ? 'core::filters.show_matching_records_conditions'
            : 'core::filters.or_match_any_conditions'
        "
        tag="div"
      >
        <template #condition v-if="depth > 1">
          {{ $t('core::filters.conditions.' + previousMatchType) }}
        </template>
        <template #match_type>
          <select
            class="border-1 mx-1 rounded-md border-neutral-300 bg-none px-2 py-0 text-sm focus:shadow-none focus:ring-primary-500 dark:border-neutral-500 dark:bg-neutral-500 dark:text-white"
            :class="{ 'pointer-events-none': readOnly }"
            :value="query.condition"
            @input="
              $store.commit('filters/UPDATE_QUERY_CONDITION', {
                query: query,
                value: $event.target.value,
              })
            "
          >
            <option value="and">{{ labels.matchTypeAll }}</option>
            <option value="or">{{ labels.matchTypeAny }}</option>
          </select>
        </template>
      </i18n-t>
      <IButtonIcon
        v-if="depth > 1"
        @click.prevent.stop="requestRemove"
        icon="X"
        class="mt-px"
      />
    </div>
    <div>
      <div
        :class="[
          'mt-3 flex w-full items-center',
          { 'mb-7': totalChildren > 0, hidden: readOnly },
        ]"
      >
        <div class="w-56">
          <ICustomSelect
            size="sm"
            :placeholder="labels.addRule"
            :clearable="false"
            :options="rules"
            v-model="selectedRule"
            @option:selected="addRule"
          />
        </div>
        <a
          v-if="depth < maxDepth"
          class="link ml-3 shrink-0 text-sm"
          href="#"
          v-show="totalChildren > 0"
          @click="addGroup"
          v-text="labels.addGroup"
        />
      </div>

      <component
        v-for="(child, index) in children"
        :key="child.query.rule + '-' + index"
        :is="components[child.type]"
        :query="child.query"
        :max-depth="maxDepth"
        :read-only="readOnly"
        :previous-match-type="query.condition"
        :rule="getRuleById(child.query.rule)"
        :rules="rules"
        :index="index"
        :depth="nextDepth"
        :labels="labels"
        @child-deletion-requested="removeChild"
      />
    </div>
  </div>
</template>
<script>
export default {
  name: 'QueryBuilderGroup',
}
</script>
<script setup>
import { ref, computed } from 'vue'
import QueryBuilderRule from './QueryBuilderRule.vue'
import find from 'lodash/find'
import { useStore } from 'vuex'

const components = { rule: QueryBuilderRule, group: 'QueryBuilderGroup' }

const emit = defineEmits(['child-deletion-requested'])

const props = defineProps([
  'index',
  'query',
  'rules',
  'maxDepth',
  'depth',
  'labels',
  'readOnly',
  'previousMatchType',
])

const store = useStore()

const selectedRule = ref('')

/**
 * Group border class based in it's depth
 */
const borderClasses = computed(() => ({
  'border-neutral-200 dark:border-neutral-500': props.depth === 1,
  'border-info-400 dark:border-info-500': props.depth === 2,
  'border-primary-400 dark:primary-info-500': props.depth > 2,
}))

/**
 * Group background class based in it's depth
 */
const bgClasses = computed(() => ({
  'bg-neutral-50 dark:bg-neutral-800': props.depth === 1,
  'bg-neutral-100 dark:bg-neutral-900': props.depth === 2,
  'bg-neutral-200 dark:bg-neutral-800': props.depth > 2,
}))

const children = computed({
  get() {
    return props.query.children
  },
  set(value) {
    store.commit('filters/SET_QUERY_CHILDREN', {
      query: props.query,
      children: value,
    })
  },
})

/**
 * The number of total child rules in the group
 */
const totalChildren = computed(() => children.value.length)

const nextDepth = computed(() => props.depth + 1)

/**
 * Find rule by id
 *
 * @param  {Number|String} ruleId
 *
 * @return {null|Object}
 */
function getRuleById(ruleId) {
  return find(props.rules, ['id', ruleId])
}

/**
 * Add new rule
 */
function addRule() {
  let selectedOperand = null
  let selectedOperator = selectedRule.value.operators[0]

  if (selectedRule.value.operands && selectedRule.value.operands.length > 0) {
    selectedOperand =
      selectedRule.value.operands[0][selectedRule.value.operands[0].valueKey]

    if (selectedRule.value.operands[0].rule) {
      selectedOperator = selectedRule.value.operands[0].rule.operators[0]
    }
  }

  store.commit('filters/ADD_QUERY_CHILD', {
    query: props.query,
    child: {
      type: 'rule',
      query: {
        type: selectedRule.value.type,
        rule: selectedRule.value.id,
        operator: selectedOperator,
        operand: selectedOperand,
        value: null,
      },
    },
  })

  selectedRule.value = ''
}

/**
 * Add new group
 */
function addGroup() {
  if (props.depth < props.maxDepth) {
    store.commit('filters/ADD_QUERY_GROUP', props.query)
  }
}

/**
 * Remove group
 *
 * @return {Void}
 */
function requestRemove() {
  emit('child-deletion-requested', props.index)
}

/**
 * Remove child
 *
 * @param  {Number} index
 *
 * @return {Void}
 */
function removeChild(index) {
  store.commit('filters/REMOVE_QUERY_CHILD', {
    query: props.query,
    index: index,
  })
}
</script>
