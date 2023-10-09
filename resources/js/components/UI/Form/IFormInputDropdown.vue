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
  <IDropdown
    ref="dropdownRef"
    adaptive-width
    :full="full"
    popper-class="bg-white rounded-md dark:bg-neutral-800"
  >
    <template #toggle="{ toggle }">
      <div
        :style="{ width: width }"
        :class="['relative', { 'pointer-events-none': disabled }]"
      >
        <!-- On mobile pointer events are disabled to not open the keyboard on touch,
        in this case, the user will be able to select only from the dropdown provided values -->
        <IFormInput
          @click="toggle(), inputClicked()"
          @blur="inputBlur"
          :id="inputId"
          v-bind="$attrs"
          autocomplete="off"
          :class="[
            'pointer-events-none pr-8',
            { 'sm:pointer-events-auto': !disabled },
          ]"
          ref="inputRef"
          :disabled="disabled"
          v-model="selectedItem"
          :placeholder="placeholder"
        />
        <Icon
          v-show="Boolean(selectedItem)"
          icon="X"
          class="absolute right-3 top-2.5 h-5 w-5 cursor-pointer text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
          @click="clearSelected"
        />
      </div>
    </template>
    <div
      :style="[
        {
          height: height,
          'overflow-y': maxHeight ? 'scroll' : null,
          'max-height': maxHeight || 'auto',
        },
      ]"
    >
      <IDropdownItem
        v-for="(item, index) in items"
        :key="index"
        :active="isSelected(item)"
        @click="itemPicked(item)"
        :text="item"
      />
    </div>
  </IDropdown>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { watch, ref, shallowRef } from 'vue'

const emit = defineEmits(['update:modelValue', 'blur', 'cleared', 'shown'])

const props = defineProps({
  width: String,
  height: String,
  maxHeight: String,
  inputId: { type: String, default: 'input-dropdown' },
  modelValue: String,
  placeholder: String,
  items: Array,
  disabled: Boolean,
  full: { type: Boolean, default: true },
})

const isOpen = ref(false)
const selectedItem = shallowRef(props.modelValue)

const inputRef = ref(null)
const dropdownRef = ref(null)

watch(selectedItem, newVal => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})

watch(
  () => props.modelValue,
  newVal => {
    if (newVal !== selectedItem.value) {
      selectedItem.value = newVal
    }
  }
)

function openIfNeeded() {
  if (!isOpen.value) {
    dropdownRef.value.show()
    isOpen.value = true
    emit('shown')
  }
}

function inputClicked(e) {
  openIfNeeded()
}

function inputBlur(e) {
  // Allow timeout as if user  clicks on the dropdown item to have
  // a selected value in case @blur event is checking the value
  setTimeout(() => emit('blur'), 500)
}

function closePicker() {
  dropdownRef.value.hide()
  isOpen.value = false
}

function itemPicked(item) {
  closePicker()
  selectedItem.value = item
}

function isSelected(item) {
  return item === selectedItem.value
}

function clearSelected() {
  selectedItem.value = ''
  emit('cleared')
  inputRef.value.focus()
  openIfNeeded()
}
</script>
