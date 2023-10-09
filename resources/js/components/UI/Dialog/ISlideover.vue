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
  <TransitionRoot appear as="template" :show="localVisible">
    <Dialog
      as="div"
      static
      :initial-focus="initialFocus"
      class="dialog fixed inset-0 overflow-hidden"
      @close="handleDialogClosedEvent"
      :open="localVisible"
    >
      <div class="absolute inset-0 overflow-hidden">
        <DialogOverlay
          v-show="overlay"
          class="absolute inset-0 bg-neutral-500/75 transition-opacity dark:bg-neutral-700/90"
        />

        <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-400 sm:duration-600"
            enter-from="translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-400 sm:duration-600"
            leave-from="translate-x-0"
            leave-to="translate-x-full"
          >
            <component
              :is="form ? 'form' : 'div'"
              @submit.prevent="$emit('submit')"
              :novalidate="form ? true : undefined"
              @keydown.passive="$emit('keydown', $event)"
              :class="[
                'w-screen',
                { 'sm:max-w-lg': size === 'sm' },
                { 'sm:max-w-2xl': size === 'md' },
                { 'sm:max-w-3xl': size === 'lg' },
                { 'sm:max-w-4xl': size === 'xl' },
                { 'sm:max-w-5xl': size === 'xxl' },
              ]"
            >
              <div
                class="flex h-full flex-col divide-y divide-neutral-200 bg-white shadow-xl dark:divide-neutral-700 dark:bg-neutral-900"
              >
                <div
                  class="flex min-h-0 flex-1 flex-col overflow-y-scroll py-6"
                >
                  <div class="px-4 sm:px-6" v-if="!hideHeader">
                    <slot name="modal-header" :cancel="hide">
                      <div class="flex items-start justify-between">
                        <div class="space-y-1">
                          <DialogTitle
                            class="text-lg font-medium text-neutral-700 dark:text-white"
                          >
                            {{ title }}
                          </DialogTitle>
                          <p
                            v-if="description"
                            class="text-sm text-neutral-500 dark:text-neutral-300"
                            v-text="description"
                          />
                        </div>
                        <div class="ml-3 flex h-7 items-center">
                          <button
                            v-if="!hideHeaderClose"
                            type="button"
                            :id="'modalClose-' + id"
                            class="rounded-md bg-white text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 hover:text-neutral-500 dark:bg-neutral-800"
                            @click="hide"
                          >
                            <Icon icon="X" class="h-6 w-6" />
                          </button>
                        </div>
                      </div>
                    </slot>
                  </div>
                  <div class="relative mt-8 flex-1 px-4 sm:px-6">
                    <slot></slot>
                  </div>
                </div>
                <div
                  v-if="!hideFooter"
                  class="shrink-0 px-4 py-4 dark:bg-neutral-800"
                >
                  <slot name="modal-footer" :cancel="hide">
                    <div
                      class="flex flex-wrap justify-end space-x-3 sm:flex-nowrap"
                    >
                      <slot
                        name="modal-cancel"
                        :cancel="hide"
                        :title="cancelTitle"
                      >
                        <IButton
                          @click="hide"
                          :variant="cancelVariant"
                          :disabled="computedCancelDisabled"
                          :size="cancelSize"
                          :text="cancelTitle"
                        />
                      </slot>
                      <slot name="modal-ok" :title="okTitle">
                        <IButton
                          @click="handleOkClick"
                          :variant="okVariant"
                          :size="okSize"
                          :type="form ? 'submit' : 'button'"
                          :loading="okLoading"
                          :disabled="computedOkDisabled"
                          :text="okTitle"
                        />
                      </slot>
                    </div>
                  </slot>
                </div>
              </div>
            </component>
          </TransitionChild>
        </div>
      </div>
      <IConfirmationDialog
        v-if="$root.confirmationDialog"
        :dialog="$root.confirmationDialog"
      />
    </Dialog>
  </TransitionRoot>
</template>
<script setup>
import { ref, toRef, computed, watch, nextTick, onMounted } from 'vue'
import {
  Dialog,
  DialogOverlay,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { useDialog } from './useDialog'
import propsDefinition from './props'
import emitsDefinition from './emits'

const emit = defineEmits(emitsDefinition)
const props = defineProps(propsDefinition)

useDialog(show, hide, toRef(props, 'id'))

const localVisible = ref(false)
const hiding = ref(false)

const computedOkDisabled = computed(() => {
  return props.busy || props.okDisabled
})

const computedCancelDisabled = computed(() => {
  return props.busy || props.cancelDisabled
})

watch(
  () => props.visible,
  newVal => (newVal ? show() : hide())
)

function handleDialogClosedEvent() {
  if (!props.staticBackdrop) {
    hide()
  }
}

function show() {
  emit('show')
  localVisible.value = true
  emit('update:visible', true)
  nextTick(() => emit('shown'))
}

function hide() {
  // Sometimes when the modal is hidden via the close button,
  // the v-model:visible is updated later and causing the hide event
  // to be fired/called twice
  if (hiding.value) {
    return
  }

  hiding.value = true

  localVisible.value = false
  emit('update:visible', false)
  nextTick(() => {
    emit('hidden')
    hiding.value = false
  })
}

function handleOkClick(e) {
  emit('ok', e)
}

onMounted(() => {
  if (props.visible) {
    show()
  }
})

defineExpose({ hide, show })
</script>
