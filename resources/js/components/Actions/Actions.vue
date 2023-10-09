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
  <component
    v-if="hasActionsAvailable"
    :is="type === 'select' ? ActionSelect : ActionDropdown"
    :action-request-params="actionRequestParams"
    :actions="filteredActions"
    :ids="ids"
    :view="view"
    :resource-name="resourceName"
    @run="actionExecutedHandler"
  />
</template>
<script setup>
import { computed } from 'vue'
import ActionSelect from './ActionsSelect.vue'
import ActionDropdown from './ActionsDropdown.vue'
import reject from 'lodash/reject'
import defaultProps from './props'

const emit = defineEmits(['run'])

const props = defineProps({
  ...defaultProps,
  type: { required: true, type: String },
})

const filteredActions = computed(() => {
  return reject(props.actions, action => {
    if (props.view === 'update' && action.hideOnUpdate === true) {
      return true
    } else if (props.view === 'index' && action.hideOnIndex === true) {
      return true
    }
    return false
  })
})

const hasActionsAvailable = computed(() => filteredActions.value.length > 0)

function actionExecutedHandler(response) {
  emit('run', response)
}
</script>
