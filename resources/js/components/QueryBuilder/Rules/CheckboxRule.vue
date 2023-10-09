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
  <div class="mt-1 flex items-center space-x-2">
    <IFormCheckbox
      v-for="option in options"
      :key="option[rule.valueKey]"
      v-model:checked="value"
      :value="option[rule.valueKey]"
      :disabled="readOnly"
      :id="rule.id + '_' + option[rule.valueKey] + '_' + index"
      :name="rule.id + '_' + option[rule.valueKey]"
      :label="option[rule.labelKey]"
    />
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { toRef, computed } from 'vue'
import { useType } from './useType'
import propsDefinition from './props'
import { useElementOptions } from '~/Core/resources/js/composables/useElementOptions'

const props = defineProps(propsDefinition)

const { options, setOptions, getOptions } = useElementOptions()

const value = computed({
  get() {
    return props.query.value
  },
  set(value) {
    updateValue(value)
  },
})

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

if (props.query.value === null) {
  updateValue([])
}

getOptions(props.rule).then(setOptions)
</script>
