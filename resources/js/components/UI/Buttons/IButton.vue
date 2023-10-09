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
    :is="tag"
    :type="type"
    :class="computedClasses"
    :disabled="disabled"
    @click="handleClickEvent"
    :tabindex="disabled ? '-1' : undefined"
  >
    <Icon
      v-if="icon"
      v-show="!loading"
      :icon="icon"
      :class="[
        'pointer-events-none shrink-0', // avoid click event.target propagating to the icon, see FloatingFilters vco middleware
        !iconClass
          ? { 'h-4 w-4': size === 'sm', 'h-5 w-5': size !== 'sm' }
          : iconClass,
        { '-ml-1 mr-2': $slots.default || text },
      ]"
    />
    <ISpinner
      v-if="loading"
      :class="[
        { 'h-4 w-4': size === 'sm', 'h-5 w-5': size !== 'sm' },
        { 'mr-2': $slots.default || text },
        'text-current',
      ]"
    />
    <slot>
      {{ text }}
    </slot>
  </component>
</template>
<script setup>
import { computed, useSlots } from 'vue'
import { useRouter } from 'vue-router'

const emit = defineEmits(['click'])
const props = defineProps({
  text: String,
  icon: String,
  iconClass: [String, Array, Object],
  to: [Object, String],
  tag: { default: 'button', type: [String, Object] },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  rounded: { default: true, type: Boolean },
  block: { default: false, type: Boolean },
  variant: {
    type: String,
    default: 'primary',
    validator(value) {
      return ['primary', 'secondary', 'danger', 'white', 'success'].includes(
        value
      )
    },
  },
  size: {
    type: [String, Boolean],
    default: 'md',
    validator(value) {
      if (value === false) {
        return true
      }
      // buttons have md by default because can be used to other
      // elements link </a>
      return ['sm', 'md', 'lg'].includes(value)
    },
  },
})

const router = useRouter()
const slots = useSlots()

const computedClasses = computed(() => [
  'btn',
  {
    'btn-primary': props.variant === 'primary',
    'btn-secondary': props.variant === 'secondary',
    'btn-danger': props.variant === 'danger',
    'btn-white': props.variant === 'white',
    'btn-success': props.variant === 'success',
    'btn-sm': props.size === 'sm',
    'btn-md': props.size === 'md',
    'btn-lg': props.size === 'lg',
    rounded: props.rounded && props.size === 'sm',
    'rounded-md': props.rounded && (props.size === 'md' || props.size === 'lg'),
    'w-full justify-center': props.block,
    'only-icon': props.icon && !slots.default && !props.text,
  },
])

function handleClickEvent(e) {
  if (props.to) {
    router.push(props.to)
  } else {
    emit('click', e)
  }
}
</script>
