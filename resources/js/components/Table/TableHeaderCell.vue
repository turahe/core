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
  <th
    scope="col"
    :class="[
      'whitespace-nowrap',
      column.thClass,
      {
        'table-sticky-column': column.primary,
        'text-left': isLeftAligned,
        'text-center': column.centered,
      },
    ]"
    :style="{
      width: width,
      'min-width': column.minWidth,
    }"
  >
    <div class="flex items-center">
      <IFormCheckbox
        v-if="isSelectable"
        :class="['mr-4', { 'ml-2': condensed }]"
        @change="$emit('toggle-select-all')"
        :checked="allRowsSelected"
      />

      <div class="grow">
        <a
          v-if="column.sortable"
          @click.prevent="$emit('sort-requested', column.attribute)"
          class="group inline-flex focus:outline-none hover:text-neutral-700 dark:hover:text-neutral-400"
          href="#"
        >
          <slot>
            {{ column.label }}
          </slot>
          <span
            class="ml-2 flex-none rounded bg-neutral-200 text-sm text-neutral-900 group-hover:bg-neutral-300"
            v-show="isOrdered"
          >
            <Icon
              :icon="isSortedAscending ? 'ChevronUp' : 'ChevronDown'"
              class="h-4 w-4"
            />
          </span>
          <span
            v-show="!isOrdered"
            class="invisible ml-2 flex-none rounded text-neutral-400 group-hover:visible group-focus:visible"
          >
            <Icon
              v-once
              icon="ChevronDown"
              class="invisible h-4 w-4 flex-none rounded text-neutral-400 group-hover:visible group-focus:visible"
            />
          </span>
        </a>
        <span v-else>
          <slot>
            {{ column.label }}
          </slot>
        </span>
      </div>
    </div>
  </th>
</template>
<script setup>
import { computed } from 'vue'

const emit = defineEmits(['toggle-select-all', 'sort-requested'])

const props = defineProps({
  column: { type: Object, required: true },
  // Whether the current column is ordered
  isOrdered: Boolean,
  condensed: Boolean,
  isSortedAscending: Boolean,
  resourceName: String,
  isSelectable: Boolean,
  allRowsSelected: Boolean,
})

const width = computed(() => props.column.width || 'auto')

const isLeftAligned = computed(
  () =>
    !props.column.thClass ||
    !['text-center', 'text-left', 'text-right'].some(alignmentClass =>
      props.column.thClass.includes(alignmentClass)
    )
)
</script>
