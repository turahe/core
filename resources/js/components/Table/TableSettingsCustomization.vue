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
  <IModal
    :id="tableId + 'listSettings'"
    size="sm"
    :description="$t('core::table.customize_list_view')"
    :title="$t('core::table.list_settings')"
    @hidden="
      customizeTable(tableId, false), (search = null), setColumnsForDraggable()
    "
    @shown="customizeTable(tableId, true)"
  >
    <div v-if="config.allowDefaultSortChange" class="mb-4 mt-10">
      <p
        class="mb-1.5 font-medium text-neutral-700 dark:text-neutral-100"
        v-t="'core::table.default_sort'"
      />

      <draggable
        v-model="sorted"
        :item-key="item => item.attribute + '-' + item.direction"
        handle=".sort-draggable-handle"
        v-bind="draggableOptions"
      >
        <template #item="{ element, index }">
          <div
            class="mb-1 flex items-center rounded border border-neutral-200 px-2 py-1 dark:border-neutral-600"
          >
            <div class="grow p-1">
              <IFormSelect
                v-model="sorted[index].attribute"
                :id="'column_' + index"
              >
                <!-- ios by default selects the first field but no events are triggered in this case
                we will make sure to add blank one -->
                <option value="" v-if="!sorted[index].attribute"></option>
                <option
                  v-for="sortableColumn in sortable"
                  :key="sortableColumn.attribute"
                  :value="sortableColumn.attribute"
                  v-show="!isSortedColumnDisabled(sortableColumn.attribute)"
                >
                  {{ sortableColumn.label }}
                </option>
              </IFormSelect>
            </div>
            <div class="p-1">
              <IFormSelect
                v-model="sorted[index].direction"
                :id="'column_type_' + index"
              >
                <option value="asc">
                  Asc (<span v-t="'core::app.ascending'"></span>)
                </option>
                <option value="desc">
                  Desc (<span v-t="'core::app.descending'"></span>)
                </option>
              </IFormSelect>
            </div>
            <div class="p-1">
              <IButton
                :variant="index === 0 ? 'secondary' : 'danger'"
                :disabled="index === 0 && isAddSortColumnDisabled"
                size="sm"
                @click="index === 0 ? addSortedColumn() : removeSorted(index)"
              >
                <Icon icon="Plus" class="h-4 w-4" v-if="index === 0" />
                <Icon icon="Minus" class="h-4 w-4" v-else-if="index > 0" />
              </IButton>
            </div>
            <div class="p-1">
              <IButtonIcon
                icon="Selector"
                class="sort-draggable-handle cursor-move"
              />
            </div>
          </div>
        </template>
      </draggable>
    </div>

    <p
      class="mb-1.5 font-medium text-neutral-700 dark:text-neutral-100"
      v-t="'core::table.columns'"
    />

    <SearchInput v-model="search" @input="setColumnsForDraggable" />

    <div class="mt-4 max-h-[400px] overflow-auto">
      <draggable
        v-model="columnsForDraggable"
        handle=".column-draggable-handle"
        :move="onColumnMove"
        item-key="attribute"
        v-bind="scrollableDraggableOptions"
      >
        <template #item="{ element }">
          <div
            class="mb-2 mr-2 flex rounded-md border border-neutral-200 px-3 py-2 dark:border-neutral-600"
          >
            <div class="grow">
              <IFormCheckbox
                v-i-tooltip="
                  element.primary === true
                    ? $t('core::table.primary_column')
                    : ''
                "
                v-model:checked="visibleColumns"
                :name="'col-' + element.attribute"
                :disabled="element.primary === true"
                :value="element.attribute"
                :id="'col-' + element.attribute"
              >
                <Icon
                  v-if="element.helpText"
                  icon="QuestionCircle"
                  class="h-4 w-4 text-neutral-600"
                  v-i-tooltip="element.helpText"
                />
                {{ element.label }}
              </IFormCheckbox>
            </div>
            <div>
              <IButtonIcon
                v-if="!element.primary"
                icon="Selector"
                class="column-draggable-handle cursor-move"
              />
            </div>
          </div>
        </template>
      </draggable>
    </div>
    <hr class="my-3 border-t border-neutral-200 dark:border-neutral-600" />
    <IFormGroup
      :label="$t('core::table.per_page')"
      label-for="tableSettingsPerPage"
    >
      <IFormSelect v-model="perPage" id="tableSettingsPerPage">
        <option
          v-for="perPage in [25, 50, 100]"
          :key="perPage"
          :value="perPage"
        >
          {{ perPage }}
        </option>
      </IFormSelect>
    </IFormGroup>
    <IFormGroup
      :label="$t('core::table.max_height')"
      :description="$t('core::table.max_height_info')"
      label-for="tableSettingsMaxHeight"
    >
      <div class="relative mt-1 rounded-md shadow-sm">
        <IFormInput
          type="number"
          min="200"
          step="50"
          class="pr-10"
          list="maxHeight"
          id="tableSettingsMaxHeight"
          v-model="maxHeight"
        />

        <datalist id="maxHeight">
          <option value="200" />
          <option value="250" />
          <option value="300" />
          <option value="350" />
          <option value="400" />
          <option value="500" />
        </datalist>
        <div
          class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
        >
          <span class="-mt-1 text-neutral-400">px</span>
        </div>
      </div>
    </IFormGroup>

    <IFormGroup class="mt-5">
      <IFormCheckbox v-model:checked="condensed">
        {{ $t('core::table.condensed') }}
      </IFormCheckbox>
    </IFormGroup>

    <template #modal-footer>
      <div class="space-x-2 text-right">
        <IButton
          variant="white"
          @click="hideModal"
          size="sm"
          :text="$t('core::app.cancel')"
        />
        <IButton
          variant="white"
          @click="reset"
          size="sm"
          :text="$t('core::app.reset')"
        />
        <IButton
          variant="primary"
          @click="save()"
          size="sm"
          :text="$t('core::app.save')"
        />
      </div>
    </template>
  </IModal>
</template>
<script setup>
import { watch, computed, ref, nextTick } from 'vue'
import draggable from 'vuedraggable'
import filter from 'lodash/filter'
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'
import { useStore } from 'vuex'
import { useDraggable } from '~/Core/resources/js/composables/useDraggable'
import { useTable } from './useTable'
import { useForm } from '~/Core/resources/js/composables/useForm'

const props = defineProps({
  config: { required: true, type: Object },
  tableId: { required: true, type: String },
  urlPath: { required: true, type: String },
})

const { draggableOptions, scrollableDraggableOptions } = useDraggable()
const { reloadTable, customizeTable } = useTable()
const store = useStore()

const sorted = ref([])
const visibleColumns = ref([])
const columnsForDraggable = ref([])
const search = ref(null)
const maxHeight = ref(null)
const condensed = ref(false)
const perPage = ref(null)

const { form } = useForm()

const modalIsVisible = computed(
  () => store.state.table.customize[props.tableId]
)

const customizeableColumns = computed(() =>
  filter(props.config.columns, column => column.customizeable)
)

const sortable = computed(() =>
  filter(customizeableColumns.value, column => column.sortable)
)

const isAddSortColumnDisabled = computed(() => {
  if (sorted.value.length === sortable.value.length) {
    return true
  }

  // Do not allow the user to add new column before selecting a column from the latest added
  // Causing error with draggable and index/keys
  let notSelectedColumns = filter(
    sorted.value,
    column => column.attribute == ''
  )

  return notSelectedColumns.length > 0 ? true : false
})

watch(modalIsVisible, newVal => {
  newVal ? showModal() : hideModal()
})

function onColumnMove(data) {
  // You can't reorder primary columns
  // you can't add new columns before the first primary column
  // as the first primary column contains specific data table related to the table
  // You can't add new columns after the last primary column
  if (
    columnsForDraggable.value[data.draggedContext.index].primary ||
    (data.draggedContext.futureIndex === 0 &&
      columnsForDraggable.value[data.draggedContext.futureIndex].primary) ||
    (data.draggedContext.futureIndex === sortable.length - 1 &&
      columnsForDraggable.value[data.draggedContext.futureIndex].primary)
  ) {
    return false
  }
}

function isSortedColumnDisabled(attribute) {
  return Boolean(find(sorted.value, ['attribute', attribute]))
}

function addSortedColumn() {
  sorted.value.push({
    attribute: '',
    direction: 'asc',
  })
}

function removeSorted(index) {
  sorted.value.splice(index, 1)
}

function setDefaults() {
  setTableConfig()
  setColumnsForDraggable()
  setDefaultVisibleColumns()
}

function setColumnsForDraggable() {
  columnsForDraggable.value = filter(customizeableColumns.value, column => {
    if (search.value) {
      return column.label.toLowerCase().includes(search.value.toLowerCase())
    }

    return true
  })
}

function setDefaultVisibleColumns() {
  visibleColumns.value = []

  columnsForDraggable.value.forEach(
    column =>
      column.hidden !== true && visibleColumns.value.push(column.attribute)
  )
}

function prepareColumnsForStorage(columns) {
  return columns.map((column, index) => ({
    attribute: column.attribute,
    order: index + 1,
    hidden: !visibleColumns.value.includes(column.attribute),
  }))
}

function reset() {
  request(form.clear()).then(initializeComponent)
}

function save(columns) {
  return request(
    form.clear().set({
      // Remove any empty columns which the user used to add them via the + button but didn't selected a column
      order: filter(sorted.value, column => column.attribute != ''),
      columns: prepareColumnsForStorage(columns || columnsForDraggable.value),
      maxHeight: maxHeight.value,
      condensed: condensed.value,
      perPage: perPage.value,
    })
  )
}

async function request(form) {
  const data = await form.post(`${props.urlPath}/settings`)

  store.commit('table/UPDATE_SETTINGS', {
    id: props.tableId,
    settings: data,
  })

  nextTick(setDefaults)

  // We will re-query the table because the hidden columns are not queried
  // and in this case the data won't be shown
  nextTick(() => reloadTable(props.tableId))
  hideModal()
}

function showModal() {
  Innoclapps.modal().show(props.tableId + 'listSettings')
}

function hideModal() {
  Innoclapps.modal().hide(props.tableId + 'listSettings')
}

function setTableConfig() {
  maxHeight.value = props.config.maxHeight
  condensed.value = props.config.condensed
  perPage.value = props.config.perPage
}

function initializeComponent() {
  setDefaults()
  sorted.value = cloneDeep(props.config.order)
}

initializeComponent()

defineExpose({ showModal, save, onColumnMove })
</script>
