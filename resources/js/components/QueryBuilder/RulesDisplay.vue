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
    class="flex snap-x space-x-1 overflow-x-auto scrollbar-thin scrollbar-track-neutral-200 scrollbar-thumb-neutral-300 scrollbar-thumb-rounded-md"
    :class="{ 'pb-2': group.children }"
    style="font-size: 0.96rem"
  >
    <RuleDisplay
      v-for="(rule, index) in group.children"
      :key="rule.query.rule"
      :index="index"
      :condition="group.condition"
      :identifier="identifier"
      :view="view"
      :rule="rule"
    />
  </div>
</template>

<script setup>
import RuleDisplay from './RuleDisplay.vue'
import { useQueryBuilder } from './useQueryBuilder'

const props = defineProps({
  view: { required: true, type: String },
  identifier: { required: true, type: String },
})

const { queryBuilderRules: group } = useQueryBuilder(
  props.identifier,
  props.view
)
</script>
