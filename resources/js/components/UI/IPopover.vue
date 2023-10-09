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
  <Popover ref="popperRef">
    <Float
      v-bind="{ ...$attrs, ...{ class: {} } }"
      :arrow="arrow"
      :offset="10"
      :z-index="1250"
      :placement="placement"
      portal
      :show="visible"
      enter="transition ease-out duration-100"
      enter-from="transform opacity-0 scale-95"
      enter-to="transform opacity-100 scale-100"
      leave="transition ease-in duration-75"
      leave-from="transform opacity-100 scale-100"
      leave-to="transform opacity-0 scale-95"
    >
      <PopoverButton as="template" @click="toggle">
        <slot></slot>
      </PopoverButton>

      <PopoverPanel
        ref="panelRef"
        static
        :class="[
          $attrs.class,
          'overflow-hidden rounded-md focus:outline-none',
          colorMaps[variant].backgroundColorClasses,
          bordered ? 'border ' + colorMaps[variant].borderColorClasses : '',
          {
            'shadow-lg': shadow,
          },
        ]"
      >
        <FloatArrow
          v-if="arrow"
          :class="[
            'absolute h-5 w-5 rotate-45',
            colorMaps[variant].backgroundColorClasses,
            bordered ? 'border ' + colorMaps[variant].borderColorClasses : '',
          ]"
        />

        <div
          :class="[
            'relative rounded-md',
            colorMaps[variant].backgroundColorClasses,
            { 'pointer-events-none opacity-60': busy },
          ]"
        >
          <div
            v-if="title || $slots.title || closeable"
            :class="[
              colorMaps[variant].backgroundColorClasses,
              'px-4 py-3',
              bordered
                ? 'border-b ' + colorMaps[variant].borderColorClasses
                : '',
            ]"
          >
            <div class="flex justify-between">
              <h4
                v-text="title"
                class="text-[0.9rem] font-medium text-neutral-800 dark:text-neutral-100"
              />
              <a
                v-if="closeable"
                href="#"
                @click.prevent="hide"
                class="mt-0.5 text-neutral-500 focus:outline-none hover:text-neutral-700 dark:text-neutral-300 dark:hover:text-neutral-100"
              >
                <Icon icon="X" class="h-5 w-5" />
              </a>
            </div>
          </div>
          <slot name="popper"></slot>
        </div>
      </PopoverPanel>
    </Float>
  </Popover>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, onMounted } from 'vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { Float, FloatArrow } from '@headlessui-float/vue'
import { onClickOutside } from '@vueuse/core'

const colorMaps = {
  white: {
    backgroundColorClasses: 'bg-white dark:bg-neutral-800',
    borderColorClasses: 'border-neutral-200 dark:border-neutral-700',
  },
}

const emit = defineEmits(['show', 'hide'])

const props = defineProps({
  busy: Boolean,
  variant: { type: String, default: 'white' },
  placement: { type: String, default: 'bottom' },
  bordered: { type: Boolean, default: true },
  arrow: { type: Boolean, default: true },
  shadow: { type: Boolean, default: true },
  title: String,
  closeable: Boolean,
  disabled: Boolean,
})

const visible = ref(false)

const panelRef = ref(null)
const popperRef = ref(null)

onMounted(() => {
  onClickOutside(panelRef, hide, {
    ignore: ['.c-popper', popperRef.value.$el],
  })
})

function toggle() {
  visible.value ? hide() : show()
}

function show() {
  if (props.disabled === true) {
    return
  }
  visible.value = true
  emit('show')
}

function hide() {
  if (props.disabled === true) {
    return
  }
  visible.value = false
  emit('hide')
}

defineExpose({
  show,
  hide,
})
</script>
