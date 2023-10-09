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
  <TransitionRoot as="template" :show="open">
    <Dialog
      as="div"
      static
      class="dialog fixed inset-0 overflow-y-auto"
      :open="open"
    >
      <div
        class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0"
      >
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <DialogOverlay
            class="fixed inset-0 bg-neutral-500/75 transition-opacity dark:bg-neutral-700/90"
          />
        </TransitionChild>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span
          class="hidden sm:inline-block sm:h-screen sm:align-middle"
          aria-hidden="true"
          >&#8203;</span
        >
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          enter-to="opacity-100 translate-y-0 sm:scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 translate-y-0 sm:scale-100"
          leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
          <div
            class="inline-block w-full transform rounded-lg bg-white px-4 pb-4 pt-5 text-left align-bottom shadow-xl transition-all dark:bg-neutral-800 sm:my-8 sm:max-w-lg sm:p-6 sm:align-middle"
          >
            <template v-if="!dialog.component">
              <div class="sm:flex sm:items-start">
                <div
                  :class="[
                    'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10',
                    dialog.iconWrapperColorClass
                      ? dialog.iconWrapperColorClass
                      : 'bg-danger-100',
                  ]"
                >
                  <Icon
                    :icon="dialogIcon"
                    :class="[
                      'h-6 w-6',
                      dialog.iconColorClass
                        ? dialog.iconColorClass
                        : 'text-danger-600',
                    ]"
                  />
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <DialogTitle
                    v-if="title"
                    as="h3"
                    :class="{ 'mt-2': !dialog.message }"
                    class="text-lg/6 font-medium text-neutral-600 dark:text-white"
                  >
                    <span v-if="dialog.html" v-html="title"></span>
                    <span v-else v-text="title"></span>
                  </DialogTitle>

                  <div
                    v-if="dialog.message"
                    :class="{ 'mt-2': Boolean(title) }"
                  >
                    <p class="text-sm text-neutral-500 dark:text-neutral-300">
                      <span v-if="dialog.html" v-html="dialog.message"></span>
                      <span v-else v-text="dialog.message"></span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-5 sm:ml-10 sm:mt-4 sm:flex sm:pl-4">
                <IButton
                  :variant="confirmVariant"
                  class="w-full sm:w-auto"
                  @click="confirm"
                  :text="confirmText"
                />
                <IButton
                  :variant="cancelVariant"
                  class="mt-3 w-full sm:ml-3 sm:mt-0 sm:w-auto"
                  @click="cancel"
                  :text="$t('core::app.cancel')"
                />
              </div>
            </template>
            <component
              v-else
              :is="dialog.component"
              :close="close"
              :cancel="cancel"
              :dialog="dialog"
            />
          </div>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  Dialog,
  DialogOverlay,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'

const { t } = useI18n()

const props = defineProps({
  dialog: { required: true, type: Object },
})

const open = ref(true)

const title = computed(() => {
  if (props.dialog.title === false) {
    return null
  }

  return props.dialog.title || t('core::actions.confirmation_message')
})

const dialogIcon = computed(() => props.dialog.icon || 'ExclamationTriangle')
const confirmVariant = computed(() => props.dialog.confirmVariant || 'danger')
const confirmText = computed(
  () => props.dialog.confirmText || t('core::app.confirm')
)
const cancelVariant = computed(() => props.dialog.cancelVariant || 'white')

function close() {
  open.value = false
}

function confirm() {
  props.dialog.resolve()
  close()
}

function cancel() {
  props.dialog.reject()
  close()
}
</script>
