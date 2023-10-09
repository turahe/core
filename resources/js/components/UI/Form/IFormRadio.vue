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
  <div class="flex items-start">
    <input
      :id="id"
      :name="name"
      type="radio"
      v-model="localModelValue"
      :value="value"
      :disabled="disabled"
      @change="handleChangeEvent"
      class="form-radio dark:bg-neutral-700"
    />
    <IFormLabel :for="id" class="-mt-0.5 ml-2">
      <component
        :is="swatchColor ? TextBackground : 'span'"
        :color="swatchColor || undefined"
        :class="
          swatchColor
            ? 'inline-flex items-center justify-center rounded-full px-2.5 font-normal leading-5 dark:!text-white'
            : null
        "
      >
        <slot>{{ label }}</slot>
      </component>
    </IFormLabel>
    <IFormText :id="id + 'description'" class="mt-1" v-if="description">
      {{ description }}
    </IFormText>
  </div>
</template>
<script setup>
import { randomString } from '@/utils'
import TextBackground from '~/Core/resources/js/components/TextBackground.vue'
import { shallowRef, watch, onMounted } from 'vue'

const emit = defineEmits(['update:modelValue', 'change'])

const props = defineProps({
  name: String,
  label: String,
  description: String,
  swatchColor: String,
  modelValue: {},
  value: {},
  disabled: Boolean,
  id: {
    type: [String, Number],
    default() {
      return randomString()
    },
  },
})

const localModelValue = shallowRef(null)

watch(
  () => props.modelValue,
  newVal => {
    localModelValue.value = newVal
  }
)

function handleChangeEvent(e) {
  let value = e.target.value

  // Allow providing null as a value
  if (value === 'on' && props.value === null) {
    value = null
  }

  emit('update:modelValue', value)
  emit('change', value)
}

onMounted(() => (localModelValue.value = props.modelValue))
</script>
