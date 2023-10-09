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
  <div
    class="inline-flex h-full w-80 flex-col overflow-y-hidden rounded-lg bg-neutral-200/40 border border-neutral-300/40 dark:border-neutral-700 align-top shadow dark:bg-neutral-900"
  >
    <div class="px-3 py-2.5">
      <div class="flex items-center">
        <slot name="columnHeader">
          <h5
            class="mr-auto truncate font-medium text-neutral-800 dark:text-neutral-100 text-sm"
            v-text="name"
          />
          <div>
            <slot name="topRight"></slot>
          </div>
        </slot>
      </div>
      <slot name="afterColumnHeader"></slot>
    </div>
    <div
      class="h-auto overflow-y-auto overflow-x-hidden"
      :id="'boardColumn' + columnId"
    >
      <draggable
        :data-column="columnId"
        :modelValue="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
        :move="onMoveCallback"
        :item-key="item => item.id"
        :emptyInsertThreshold="100"
        @start="onDragStart"
        @end="onDragEnd"
        @change="onChangeEventHandler"
        v-bind="columnCardsDraggableOptions"
        :group="{ name: boardId }"
      >
        <template #item="{ element }">
          <div
            class="m-2 overflow-hidden whitespace-normal rounded-md bg-white shadow dark:bg-neutral-800"
          >
            <slot name="card" :card="element">
              <div class="px-4 py-5 sm:p-6">
                {{ element.display_name }}
              </div>
            </slot>
          </div>
        </template>
      </draggable>
    </div>

    <div class="flex items-center p-3"></div>

    <InfinityLoader
      @handle="infiniteHandler($event)"
      :scroll-element="'#boardColumn' + columnId"
    />
  </div>
</template>
<script setup>
import { computed } from 'vue'
import InfinityLoader from '~/Core/resources/js/components/InfinityLoader.vue'
import draggable from 'vuedraggable'
import { useDraggable } from '~/Core/resources/js/composables/useDraggable'

const emit = defineEmits([
  'drag-start',
  'drag-end',
  'updated',
  'added',
  'removed',
  'update:modelValue',
])

const props = defineProps({
  name: { required: true, type: String },
  columnId: { required: true, type: Number },
  modelValue: { required: true, type: Array },
  boardId: { required: true, type: String },
  loader: { required: true, type: Function },
  move: Function,
})

const { scrollableDraggableOptions } = useDraggable()

const columnCardsDraggableOptions = computed(() => ({
  ...scrollableDraggableOptions,
  ...{ filter: 'a, button', delay: 25, preventOnFilter: false },
}))

function infiniteHandler(state) {
  props.loader(props.columnId, state)
}

function onDragStart(e) {
  emit('drag-start', e)
}

function onDragEnd(e) {
  emit('drag-end', e)
}

function onMoveCallback(evt, originalEvent) {
  if (props.move && props.move(evt, originalEvent) === false) {
    return false
  }
}

function onChangeEventHandler(e) {
  if (e.removed) {
    emit('removed', {
      columnId: props.columnId,
      event: e,
    })
  }

  if (e.moved) {
    emit('updated', {
      columnId: props.columnId,
      event: e,
    })
  }

  if (e.added) {
    emit('added', {
      columnId: props.columnId,
      event: e,
    })

    emit('updated', {
      columnId: props.columnId,
      event: e,
    })
  }
}
</script>
