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
  <DropdownSelectInput
    v-model="action"
    :items="actions"
    placement="bottom-end"
    :label="$t('core::actions.actions')"
    label-key="name"
    value-key="uriKey"
    @change="run"
  />
</template>
<script setup>
import { computed, toRef } from 'vue'
import defaultProps from './props'
import { useAction } from './useAction'
import castArray from 'lodash/castArray'

const emit = defineEmits(['run'])
const props = defineProps(defaultProps)

const actionIds = computed(() => castArray(props.ids))

const { run, action } = useAction(
  actionIds,
  {
    resourceName: toRef(props, 'resourceName'),
    requestParams: toRef(props, 'actionRequestParams'),
  },
  response => emit('run', response)
)
</script>
