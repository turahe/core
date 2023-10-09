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
  <Menu ref="menuRef" as="div" :class="wrapperClass">
    <Float
      :arrow="arrow"
      :offset="15"
      flip
      :z-index="1250"
      placement="bottom"
      portal
      :show="visible"
      v-bind="{ ...$attrs, ...{ class: undefined } }"
      enter="transition ease-out duration-100"
      enter-from="transform opacity-0 scale-95"
      enter-to="transform opacity-100 scale-100"
      leave="transition ease-in duration-75"
      leave-from="transform opacity-100 scale-100"
      leave-to="transform opacity-0 scale-95"
    >
      <slot
        name="toggle"
        v-bind="{
          disabled: disabled,
          loading: loading,
          icon: icon,
          iconClass: iconClass,
          noCaret: noCaret,
          toggle: toggle,
        }"
      >
        <!-- Must be <button> element -->
        <MenuButton
          :as="IButton"
          @click="toggle"
          :variant="variant"
          :disabled="disabled"
          :loading="loading"
          :rounded="rounded"
          :size="size"
          :icon="icon"
          :icon-class="iconClass"
          :class="['w-full', $attrs.class, { 'justify-between': !noCaret }]"
        >
          <slot name="toggle-content">
            {{ text }}
          </slot>
          <Icon
            v-if="!noCaret"
            icon="ChevronDown"
            :class="size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4'"
            class="-mr-1 ml-2 shrink-0"
          />
        </MenuButton>
      </slot>
      <MenuItems
        ref="menuItemsRef"
        static
        :class="[
          itemsClass,
          'rounded-md outline-none',
          colorMaps.white.backgroundColorClasses,
          bordered ? 'border ' + colorMaps.white.borderColorClasses : '',
          {
            'w-full': full,
            'shadow-lg': shadow,
          },
        ]"
      >
        <FloatArrow
          v-if="arrow"
          :class="[
            'absolute h-5 w-5 rotate-45',
            colorMaps.white.backgroundColorClasses,
            bordered ? 'border ' + colorMaps.white.borderColorClasses : '',
          ]"
        />

        <div
          :class="[
            'relative rounded-md',
            colorMaps.white.backgroundColorClasses,
          ]"
        >
          <slot></slot>
        </div>
      </MenuItems>
    </Float>
  </Menu>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, provide, onMounted } from 'vue'
import { Menu, MenuButton, MenuItems } from '@headlessui/vue'
import { Float, FloatArrow } from '@headlessui-float/vue'
import { onClickOutside } from '@vueuse/core'
import IButton from '~/Core/resources/js/components/UI/Buttons/IButton.vue'

const colorMaps = {
  white: {
    backgroundColorClasses: 'bg-white dark:bg-neutral-800',
    borderColorClasses: 'border-neutral-200 dark:border-neutral-700',
  },
}

const emit = defineEmits(['show', 'hide'])

const props = defineProps({
  text: String,
  full: { type: Boolean, default: true },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  rounded: { type: Boolean, default: true },
  icon: { type: String },
  iconClass: [String, Array, Object],
  wrapperClass: [String, Array, Object],
  itemsClass: [String, Array, Object],
  size: { type: String, default: 'md' },
  noCaret: { default: false, type: Boolean },
  variant: { type: String, default: 'white' },
  bordered: { type: Boolean, default: true },
  arrow: { type: Boolean, default: true },
  shadow: { type: Boolean, default: true },
})

provide('hide', hide)

const visible = ref(false)

const menuItemsRef = ref(null)
const menuRef = ref(null)

onMounted(() => {
  onClickOutside(menuItemsRef, hide, {
    ignore: ['.c-popper', menuRef.value.$el],
  })
})

function toggle() {
  visible.value ? hide() : show()
}

function show() {
  visible.value = true
  emit('show')
}

function hide() {
  visible.value = false
  emit('hide')
}

defineExpose({
  show,
  hide,
})
</script>
