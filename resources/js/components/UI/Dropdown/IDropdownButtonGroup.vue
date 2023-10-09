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
  <span
    :class="[
      'relative z-0 inline-flex shadow-sm',
      size != 'sm' ? 'rounded-md' : 'rounded',
    ]"
  >
    <IButton
      :variant="variant"
      :size="size"
      :type="type"
      :icon="icon"
      :icon-class="iconClass"
      :to="to"
      :rounded="false"
      @click="$emit('click', $event)"
      :disabled="disabled"
      :loading="loading"
      :class="[
        'focus:z-10',
        size != 'sm' ? 'rounded-l-md' : 'rounded-l',
        { '-mr-px': !disabled },
      ]"
    >
      <slot name="button-content">
        {{ text }}
      </slot>
    </IButton>

    <IDropdown :placement="placement">
      <template #toggle="{ toggle }">
        <IButton
          :variant="variant"
          :disabled="disabled"
          :size="size"
          :rounded="false"
          :class="['focus:z-10', size != 'sm' ? 'rounded-r-md' : 'rounded-r']"
          @click="toggle"
        >
          <slot name="dropdown-toggle-content">
            <Icon
              icon="ChevronDown"
              :class="size === 'sm' ? 'h-4 w-4' : 'h-5 w-5'"
            />
          </slot>
        </IButton>
      </template>
      <slot></slot>
    </IDropdown>
  </span>
</template>
<script setup>
defineEmits(['click'])

defineProps({
  text: String,
  to: [Object, String],
  placement: { type: String, default: 'bottom-end' },
  size: { type: String, default: 'md' },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  variant: { type: String, default: 'primary' },
  icon: { type: String },
  type: { type: String, default: 'button' },
  iconClass: [String, Array, Object],
})
</script>
