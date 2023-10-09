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
  <IDropdown
    :placement="placement"
    wrapper-class="w-full flex-1"
    class="min-w-0 max-w-full sm:max-w-xs"
    ref="dropdownRef"
    icon="Filter"
    no-caret
    v-bind="$attrs"
  >
    <template #toggle-content>
      <span class="truncate">
        {{ !activeFilter ? $t('core::filters.filters') : activeFilter.name }}
      </span>
    </template>

    <div class="w-[19rem]">
      <div
        class="border-b border-neutral-200 px-4 py-3 dark:border-neutral-700"
      >
        <a
          href="#"
          @click.prevent="initiateNewFilter"
          :class="[
            'link mr-2 text-sm',
            {
              'border-r border-neutral-300 pr-2 dark:border-neutral-700':
                activeFilter,
            },
          ]"
          v-t="'core::filters.new'"
        />

        <a
          v-show="activeFilter"
          href="#"
          class="link border-r border-neutral-300 pr-2 text-sm dark:border-neutral-700"
          @click.prevent="initiateEdit"
          v-t="'core::filters.edit'"
        />

        <a
          href="#"
          v-show="activeFilter"
          class="link pl-2 text-sm"
          @click.prevent="clearActive"
          v-t="'core::filters.clear_applied'"
        />

        <SearchInput
          v-model="search"
          size="sm"
          class="mt-3"
          :placeholder="$t('core::filters.search')"
        />
      </div>

      <p
        v-show="hasSavedFilters && !searchResultIsEmpty"
        class="mt-1 inline-flex items-center truncate px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-200"
      >
        <Icon icon="Bars3" class="mr-2 h-5 w-5 text-current" />
        {{ $t('core::filters.available') }}
      </p>

      <p
        v-show="!hasSavedFilters || searchResultIsEmpty"
        class="block px-4 py-2 text-sm text-neutral-500 dark:text-neutral-300"
        v-t="'core::filters.not_available'"
      />

      <div
        v-show="hasSavedFilters && !searchResultIsEmpty"
        class="inline-block max-h-80 w-full overflow-auto"
      >
        <FilterDropdownItem
          v-for="filter in filteredList"
          :key="filter.id"
          :identifier="identifier"
          :view="view"
          :filter-id="filter.id"
          :name="filter.name"
          @click="handleFilterSelected"
        />
      </div>
    </div>
  </IDropdown>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, computed } from 'vue'
import FilterDropdownItem from './FilterDropdownItem.vue'
import { useQueryBuilder } from '~/Core/resources/js/components/QueryBuilder/useQueryBuilder'
import { useFilterable } from './useFilterable'

const emit = defineEmits(['apply'])

const props = defineProps({
  placement: { default: 'bottom-end', type: String },
  identifier: { required: true, type: String },
  view: { required: true, type: String },
})

const { queryBuilderRules, rulesAreVisible, toggleFiltersRules } =
  useQueryBuilder(props.identifier, props.view)

const { filters, activeFilter } = useFilterable(props.identifier, props.view)

const dropdownRef = ref(null)
const search = ref(null)

const searchResultIsEmpty = computed(
  () => search.value && filteredList.value.length === 0
)

const hasSavedFilters = computed(() => filters.value.length > 0)

const filteredList = computed(() => {
  if (!search.value) {
    return filters.value
  }

  return filters.value.filter(filter => {
    return filter.name.toLowerCase().includes(search.value.toLowerCase())
  })
})

function hide() {
  dropdownRef.value.hide()
}

function clearActive() {
  activeFilter.value = null

  emit('apply', queryBuilderRules.value)
}

function initiateEdit() {
  toggleFiltersRules()
  hide()
}

function initiateNewFilter() {
  if (activeFilter.value) {
    clearActive()
  }

  rulesAreVisible.value = true

  hide()
}

function handleFilterSelected(id) {
  Innoclapps.$emit(`${props.identifier}-${props.view}-filter-selected`, id)
}
</script>
