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
  <ICustomSelect
    @option:selected="runWhenSelected ? run() : $emit('selected', $event)"
    v-model="action"
    :searchable="false"
    :clearable="false"
    :loading="actionIsRunning"
    :options="actions"
    :placeholder="$t('core::actions.select')"
    label="name"
  />
</template>
<script setup>
import { computed, toRef } from 'vue'
import defaultProps from './props'
import { useAction } from './useAction'
import castArray from 'lodash/castArray'

const emit = defineEmits(['run', 'selected'])

const props = defineProps({
  ...defaultProps,
  runWhenSelected: { type: Boolean, default: true },
})

const actionIds = computed(() => castArray(props.ids))

const { run, action, actionIsRunning } = useAction(
  actionIds,
  {
    resourceName: toRef(props, 'resourceName'),
    requestParams: toRef(props, 'actionRequestParams'),
  },
  response => emit('run', response)
)

defineExpose({ run })
</script>
