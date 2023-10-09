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
  <IDropdown items-class="max-w-xs sm:max-w-sm" v-bind="$attrs" :full="false">
    <template #toggle="{ disabled, noCaret, toggle }">
      <slot :label="toggleLabel" :toggle="toggle">
        <button
          type="button"
          @click="toggle"
          :disabled="disabled"
          :class="toggleClass"
          class="flex w-full items-center rounded-md px-1 text-sm text-neutral-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-300 disabled:pointer-events-none disabled:opacity-60 hover:text-neutral-500 dark:text-neutral-200 dark:hover:text-neutral-300"
        >
          <slot name="label" :label="toggleLabel">
            {{ toggleLabel }}
          </slot>

          <Icon icon="ChevronDown" v-if="!noCaret" class="ml-1 mt-px h-5 w-5" />
        </button>
      </slot>
    </template>
    <div class="overflow-y-auto py-1" :style="{ maxHeight: maxHeight }">
      <slot name="header"></slot>
      <IDropdownItem
        v-for="item in items"
        :key="typeof item == 'object' ? item[valueKey] : item"
        @click="handleClickEvent(item)"
        :class="
          typeof item == 'object' && item.hasOwnProperty('class') && item.class
            ? item.class
            : null
        "
        :icon="typeof item == 'object' && item.icon ? item.icon : null"
        :prepend-icon="
          typeof item == 'object' && item.hasOwnProperty('prependIcon')
            ? item.prependIcon
            : false
        "
        :disabled="
          typeof item == 'object' && item.disabled === true ? true : false
        "
      >
        {{ typeof item == 'object' ? item[labelKey] : item }}
      </IDropdownItem>
      <slot name="footer"></slot>
    </div>
  </IDropdown>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { computed } from 'vue'
import find from 'lodash/find'
import isObject from 'lodash/isObject'
import isEqual from 'lodash/isEqual'

const emit = defineEmits(['change', 'update:modelValue'])

const props = defineProps({
  modelValue: { required: true },
  label: String,
  toggleClass: [Array, Object, String],
  items: { type: Array, default: () => [] },
  maxHeight: { type: String, default: '500px' },
  // If values are object
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'value' },
})

const toggleLabel = computed(() => {
  if (props.label) {
    return props.label
  }

  if (isObject(props.modelValue)) {
    return props.modelValue[props.labelKey]
  } else if (
    typeof props.modelValue === 'string' ||
    typeof props.modelValue === 'number' ||
    props.modelValue === null
  ) {
    if (isObject(props.items[0])) {
      let item = find(props.items, [props.valueKey, props.modelValue])
      return item ? item[props.labelKey] : ''
    }

    return props.items.find(item => item == props.modelValue) || ''
  }

  return props.modelValue
})

function handleClickEvent(active) {
  // Updates the v-model value
  emit('update:modelValue', active)

  // Custom change event
  if (!isEqual(active, props.modelValue)) {
    emit('change', active)
  }
}
</script>
