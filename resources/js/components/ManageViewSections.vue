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
  <div v-if="show">
    <div class="mb-4">
      <p
        class="font-medium text-neutral-700 dark:text-white"
        v-t="'core::app.record_view.sections.edit_heading'"
      />
      <p
        class="text-sm text-neutral-500 dark:text-neutral-300"
        v-t="'core::app.record_view.sections.edit_subheading'"
      />
    </div>
    <draggable
      :modelValue="sections"
      @update:modelValue="sections = $event"
      item-key="id"
      class="space-y-3"
      handle=".section-reorder-handle"
      v-bind="draggableOptions"
    >
      <template #item="{ element }">
        <div
          class="flex rounded-md border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900"
        >
          <div class="grow">
            <IFormCheckbox
              v-model:checked="checked[element.id]"
              :name="'section-' + element.id"
              :id="'section-' + element.id"
            >
              {{ element.heading || element.id }}
            </IFormCheckbox>
          </div>
          <div>
            <IButtonIcon
              icon="Selector"
              class="section-reorder-handle cursor-move"
            />
          </div>
        </div>
      </template>
    </draggable>
    <div class="mt-3 flex items-center justify-end space-x-2">
      <IButton
        variant="white"
        size="sm"
        :text="$t('core::app.cancel')"
        @click="$emit('update:show', false)"
      />
      <IButton
        variant="primary"
        size="sm"
        @click="save"
        :disabled="sectionsAreBeingSaved"
        :loading="sectionsAreBeingSaved"
        :text="$t('core::app.save')"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import map from 'lodash/map'
import draggable from 'vuedraggable'
import { useDraggable } from '~/Core/resources/js/composables/useDraggable'

const emit = defineEmits(['saved', 'update:show', 'update:sections'])

const props = defineProps({
  sections: { type: Array, required: true },
  show: { type: Boolean, default: false },
  identifier: { type: String, required: true },
})

const { draggableOptions } = useDraggable()

const checked = ref({})

props.sections.forEach(section => {
  checked.value[section.id] = section.enabled
})

const sectionsAreBeingSaved = ref(false)

function save() {
  sectionsAreBeingSaved.value = true

  Innoclapps.request()
    .post('/settings', {
      [props.identifier + '_view_sections']: map(
        props.sections,
        (section, index) => ({
          id: section.id,
          order: index + 1,
          enabled: checked.value[section.id],
        })
      ),
    })
    .then(() => {
      emit('saved')

      const newValue = map(props.sections, (section, index) =>
        Object.assign({}, section, {
          order: index + 1,
          enabled: checked.value[section.id],
        })
      )

      emit('update:sections', newValue)
    })
    .finally(() => (sectionsAreBeingSaved.value = false))
}
</script>
