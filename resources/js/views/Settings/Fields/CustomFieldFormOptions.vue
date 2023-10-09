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
  <IFormGroup>
    <div class="flex">
      <IFormLabel
        class="grow text-neutral-900 dark:text-neutral-100"
        :label="$t('core::fields.options')"
        required
      />
      <IButtonIcon
        v-i-tooltip="$t('core::app.add_another')"
        icon="Plus"
        @click="newOption"
      />
    </div>
    <IAlert :show="form.options.length === 0" variant="info" class="mt-2">
      <i18n-t
        scope="global"
        :keypath="'core::fields.custom.create_option_icon'"
        tag="div"
        class="flex"
      >
        <template #icon>
          <Icon icon="Plus" class="h-5 w-5 cursor-pointer" @click="newOption" />
        </template>
      </i18n-t>
    </IAlert>
    <draggable
      @sort="setDisplayOrder"
      v-bind="draggableOptions"
      :list="form.options"
      :item-key="(item, index) => index"
      handle=".option-draggable-handle"
    >
      <template #item="{ element, index }">
        <div class="relative mt-3">
          <div class="group -mx-6 px-6">
            <div
              class="option-draggable-handle absolute -left-5 top-3 z-20 hidden focus-within:block group-hover:block hover:block"
            >
              <IButtonIcon
                icon="Selector"
                class="cursor-move text-neutral-400"
                icon-class="w-4 h-4"
              />
            </div>
            <div class="relative">
              <div
                v-if="element.id"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-neutral-500 dark:text-neutral-200"
                v-text="$t('core::app.id') + ': ' + element.id"
              />
              <IFormInput
                v-model="form.options[index].name"
                @keydown.enter.prevent.stop="newOption"
                ref="optionNameRef"
                :class="['pr-20', { 'pl-14': element.id }]"
                @keydown="form.errors.clear('options')"
              />
              <IPopover auto-placement class="w-72">
                <IButtonIcon
                  icon="ColorSwatch"
                  v-i-tooltip="$t('core::app.colors.color')"
                  :style="{ color: element.swatch_color }"
                  class="absolute right-11 top-2.5 -mt-px"
                />

                <template #popper>
                  <div class="px-4 py-3">
                    <IColorSwatches
                      v-model="form.options[index].swatch_color"
                      :swatches="swatches"
                    />
                  </div>
                </template>
              </IPopover>
              <IButtonIcon
                icon="X"
                @click="removeOption(index)"
                class="absolute right-3 top-2.5"
              />
            </div>
          </div>
        </div>
      </template>
    </draggable>

    <IFormError v-text="form.getError('options')" />
  </IFormGroup>
</template>
<script setup>
import { ref, nextTick } from 'vue'
import draggable from 'vuedraggable'
import { useDraggable } from '~/Core/resources/js/composables/useDraggable'

const props = defineProps({
  form: { required: true, type: Object },
})

const { draggableOptions } = useDraggable()
const optionNameRef = ref(null)
const swatches = Innoclapps.config('favourite_colors')

/** Set the display order of the options based at the current sorting */
function setDisplayOrder() {
  props.form.options.forEach((option, index) => {
    option.display_order = index + 1
  })
}

function newOption() {
  props.form.options.push({
    name: null,
    display_order: props.form.options.length + 1,
    swatch_color: null,
  })

  // Focus the last option
  nextTick(() => {
    optionNameRef.value.focus()
  })
}

function removeOption(index) {
  let option = props.form.options[index]

  if (option.id) {
    Innoclapps.dialog()
      .confirm()
      .then(() => props.form.options.splice(index, 1))
  } else {
    props.form.options.splice(index, 1)
  }
}
</script>
