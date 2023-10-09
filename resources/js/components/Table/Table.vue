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
  <div v-if="showEmptyState" class="m-auto mt-8 max-w-5xl">
    <IEmptyState v-bind="emptyState" />
  </div>

  <IOverlay :show="!initialDataLoaded && !showEmptyState">
    <div v-show="!showEmptyState && initialDataLoaded">
      <div class="mb-2 flex flex-wrap items-center md:mb-4 md:flex-nowrap">
        <div
          class="order-last w-full shrink-0 md:order-first md:mb-0 md:w-auto"
        >
          <SearchInput v-model="search" @input="request(true)" />
        </div>

        <!-- <TablePerPageOptions :collection="collection" class="ml-4" /> -->

        <div class="flex grow">
          <slot name="after-search" :collection="collection"></slot>
        </div>

        <div>
          <TableSettings
            v-if="componentReady"
            ref="settingsRef"
            :config="config"
            :with-customize-button="withCustomizeButton"
            :url-path="computedUrlPath"
            :table-id="tableId"
            :resource-name="resourceName"
          />
        </div>
      </div>

      <div
        v-if="componentReady && hasRules"
        class="mb-2 flex flex-col items-start md:mb-4 md:flex-row"
      >
        <div
          class="mb-2 flex w-full shrink-0 content-center space-x-1 sm:w-auto md:mb-0"
        >
          <FiltersDropdown
            :view="filtersView"
            :identifier="filtersIdentifier"
            @apply="applyFilters"
            class="flex-1"
            placement="bottom-start"
          />

          <IButton
            variant="white"
            @click="toggleFiltersRules"
            v-show="hasRulesApplied && !rulesAreVisible"
            icon="PencilAlt"
          />

          <IButton
            v-show="!hasRulesApplied && !rulesAreVisible"
            variant="white"
            @click="toggleFiltersRules"
            icon="Plus"
            :text="$t('core::filters.add_filter')"
          />
        </div>

        <div class="mb-2 ml-0 md:mb-0 md:ml-4">
          <RulesDisplay :identifier="filtersIdentifier" :view="filtersView" />
        </div>
      </div>

      <Filters
        v-if="componentReady"
        :view="filtersView"
        :identifier="filtersIdentifier"
        :active-filter-id="filterId"
        @apply="applyFilters"
      />

      <ICard no-body :overlay="isLoading">
        <div
          v-if="isSelectable && hasSelectedRows"
          class="fixed left-auto right-1 z-50 w-full max-w-xs rounded-md border border-neutral-600 bg-neutral-800 px-6 py-5 shadow-lg shadow-primary-400/40 dark:shadow-primary-900/40 md:left-80 md:right-auto md:max-w-md"
          :class="collection.total <= 5 ? 'bottom-20' : 'bottom-[3.3rem]'"
        >
          <div class="flex flex-col md:flex-row md:items-center">
            <div class="mr-8 flex shrink-0 items-center">
              <IFormCheckbox
                :id="`${tableId}ToggleAll`"
                :checked="hasSelectedRows"
                @change="unselectAllRows"
              />
              <label
                class="ml-1 text-sm text-neutral-100"
                :for="`${tableId}ToggleAll`"
                v-text="
                  $t('core::actions.records_count', {
                    count: selectedRowsCount,
                  })
                "
              />
            </div>
            <div class="md:w-68 mt-3 w-full md:mt-0">
              <ActionSelect
                ref="actionsRef"
                size="sm"
                view="index"
                :ids="selectedRowsIds"
                :action-request-params="actionRequestParams"
                :actions="config.actions || []"
                :resource-name="resourceName"
              />
            </div>
          </div>
        </div>
        <!-- When no maxHeight is provided, just set the maxHeight to big number e.q. 10000px
                    because when the user previous had height, and updated resetted the table, VueJS won't set the height to auto
                    or remove the previous height -->
        <ITable
          v-show="componentReady"
          :sticky="config.maxHeight !== null"
          :condensed="config.condensed"
          :id="'table-' + tableId"
          class="rounded-lg"
          wrapper-class="-mt-px"
          :max-height="
            config.maxHeight !== null ? config.maxHeight + 'px' : '10000px'
          "
        >
          <thead>
            <draggable
              v-model="visibleColumns"
              :disabled="!config.customizeable"
              :move="e => $refs.settingsRef.customizationRef.onColumnMove(e)"
              tag="tr"
              :item-key="item => 'th' + item.attribute"
              v-bind="headingReorderDraggableOptions"
            >
              <template #item="{ element, index }">
                <HeaderCell
                  :column="element"
                  :condensed="config.condensed"
                  :is-selectable="isSelectable && index === 0"
                  :all-rows-selected="
                    isSelectable && index === 0 ? allRowsSelected : undefined
                  "
                  :resource-name="resourceName"
                  :is-ordered="collection.isOrderedBy(element.attribute)"
                  :is-sorted-ascending="
                    collection.isSorted('asc', element.attribute)
                  "
                  @sort-requested="attr => collection.toggleSortable(attr)"
                  @toggle-select-all="toggleSelectAll"
                />
              </template>
            </draggable>
          </thead>
          <tbody>
            <tr
              v-for="(row, index) in collection.items"
              :key="index"
              :class="[
                rowClass ? rowClass(row) : undefined,
                row.tSelected && '!bg-neutral-50 dark:!bg-neutral-800',
              ]"
              @click="selectOnRowClick($event, row)"
            >
              <DataCell
                v-for="(column, cidx) in visibleColumns"
                :key="'td-' + column.attribute"
                :condensed="config.condensed"
                :is-selected="row.tSelected || false"
                :is-centered="column.centered || false"
                :is-sortable="column.sortable || false"
                :is-selectable="isSelectable && cidx === 0"
                :column="column"
                :row="row"
                @selected="onRowSelected"
              >
                <template v-slot="slotProps">
                  <slot
                    v-bind="{ ...slotProps, column, row }"
                    :name="column.attribute"
                  >
                    <component
                      :is="
                        dataComponents.hasOwnProperty(column.component)
                          ? dataComponents[column.component]
                          : column.component
                      "
                      v-bind="{ ...slotProps, column, row }"
                      :resource-name="resourceName"
                    />
                  </slot>
                </template>
              </DataCell>
            </tr>
            <tr v-if="!collection.hasItems">
              <td
                v-if="!isLoading && initialDataLoaded"
                class="!p-4 !text-sm !font-normal"
                :colspan="totalColumns"
                v-text="emptyText"
              />
            </tr>
          </tbody>
        </ITable>
      </ICard>

      <TablePagination
        class="mt-4 sm:mt-6"
        v-if="collection.hasPagination"
        @go-to-next="collection.nextPage()"
        @go-to-previous="collection.previousPage()"
        @go-to-page="collection.page($event)"
        :is-current-page-check="page => collection.isCurrentPage(page)"
        :has-next-page="collection.hasNextPage"
        :has-previous-page="collection.hasPreviousPage"
        :links="collection.pagination"
        :render-links="collection.shouldRenderLinks"
        :from="collection.from"
        :to="collection.to"
        :total="collection.total"
        :loading="isLoading"
        size="sm"
      />
    </div>
  </IOverlay>
</template>
<script setup>
import {
  ref,
  reactive,
  computed,
  watch,
  nextTick,
  onMounted,
  onBeforeUnmount,
  onUnmounted,
} from 'vue'
import ActionSelect from '~/Core/resources/js/components/Actions/ActionsSelect.vue'
import TablePagination from './TablePagination.vue'
import Collection from './Collection'
import FiltersDropdown from '~/Core/resources/js/components/Filters/FilterDropdown.vue'
import Filters from '~/Core/resources/js/components/Filters'
import { useFilterable } from '~/Core/resources/js/components/Filters/useFilterable'
import { useQueryBuilder } from '~/Core/resources/js/components/QueryBuilder/useQueryBuilder'
import TableSettings from './TableSettings.vue'
// import TablePerPageOptions from './TablePerPageOptions.vue'
import TableDataColumn from './TableData/TableDataColumn.vue'
import TableDataBooleanColumn from './TableData/TableDataBooleanColumn.vue'
import TableDataPresentableColumn from './TableData/TableDataPresentableColumn.vue'
import TableDataOptionColumn from './TableData/TableDataOptionColumn.vue'
import TableDataAvatarColumn from './TableData/TableDataAvatarColumn.vue'
import TableDataEmailColumn from './TableData/TableDataEmailColumn.vue'
import DataCell from './TableDataCell.vue'
import HeaderCell from './TableHeaderCell.vue'
import RulesDisplay from '~/Core/resources/js/components/QueryBuilder/RulesDisplay.vue'
import { CancelToken } from '~/Core/resources/js/services/HTTP'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import { useLoader } from '~/Core/resources/js/composables/useLoader'
import { useGlobalEventListener } from '~/Core/resources/js/composables/useGlobalEventListener'
import isEqual from 'lodash/isEqual'
import sortBy from 'lodash/sortBy'
import cloneDeep from 'lodash/cloneDeep'
import draggable from 'vuedraggable'
import { useDraggable } from '~/Core/resources/js/composables/useDraggable'

const emit = defineEmits(['loaded'])

const props = defineProps({
  tableId: { type: String, required: true },
  resourceName: { type: String, required: true },
  actionRequestParams: {
    type: Object,
    default() {
      return {}
    },
  },
  dataRequestQueryString: {
    type: Object,
    default() {
      return {}
    },
  },
  withCustomizeButton: { type: Boolean, default: false },
  emptyState: Object,
  rowClass: Function,
  urlPath: String,
  /**
   * The filter id to intially apply to the table
   * If not provided, the default one will be used (if any)
   */
  filterId: Number,
})

const dataComponents = {
  'table-data-column': TableDataColumn,
  'table-data-boolean-column': TableDataBooleanColumn,
  'table-data-presentable-column': TableDataPresentableColumn,
  'table-data-option-column': TableDataOptionColumn,
  'table-data-avatar-column': TableDataAvatarColumn,
  'table-data-email-column': TableDataEmailColumn,
}

const { t } = useI18n()
const store = useStore()

const { scrollableDraggableOptions } = useDraggable()
const headingReorderDraggableOptions = computed(() => {
  return {
    ...scrollableDraggableOptions,
    ...{
      delay: 30,
    },
  }
})

const { setLoading, isLoading } = useLoader()

const collection = reactive(new Collection())
const search = ref('')
const componentReady = ref(false)
const watchersInitialized = ref(false)
const initialDataLoaded = ref(false)
const settingsRef = ref(null)

let requestCancelToken = null

const emptyText = computed(() => {
  if (collection.hasItems) {
    return ''
  }

  if (isLoading.value) {
    return '...'
  }

  if (search.value) {
    return t('core::app.no_search_results')
  }

  return t('core::table.empty')
})

const config = computed(() => store.state.table.settings[props.tableId] || {})

const filtersIdentifier = computed(() => config.value.identifier)

const filtersView = computed(() => props.tableId)

const isEmpty = computed(() => collection.state.meta.all_time_total === 0)

const showEmptyState = computed(() => {
  // Indicates whether there is performed any request to the server for data
  if (typeof collection.state.meta.all_time_total == 'undefined') {
    return false
  }

  return isEmpty.value && props.emptyState != undefined
})

const requestQueryStringParams = computed(() => ({
  ...collection.urlParams,
  ...config.value.requestQueryString, // Additional server params passed from table php file
  ...props.dataRequestQueryString,
}))

// Ensure they are ordered by order so they are immediately updated when dragging the headings
const columns = computed(() => sortBy(config.value.columns || [], 'order'))

const visibleColumns = computed({
  get() {
    return columns.value.filter(
      column => (!column.hidden || column.hidden == false) && column.attribute
    )
  },
  set(value) {
    let currentColumns = cloneDeep(config.value.columns)
    // We will make sure to update the store before the "save" request
    // so the changes are reflected on the ui immediately without
    // the user to wait the "save" request to finish.
    value.forEach((column, cidx) => {
      let currentColumnIndex = currentColumns.findIndex(
        c => c.attribute === column.attribute
      )

      if (currentColumnIndex !== -1) {
        currentColumns[currentColumnIndex] = Object.assign(
          {},
          column,
          config.value.columns[currentColumnIndex],
          {
            order: cidx + 1,
          }
        )
      }
    })

    store.commit('table/UPDATE_SETTINGS', {
      id: props.tableId,
      settings: {
        columns: currentColumns,
      },
    })

    settingsRef.value.customizationRef.save(value)
  },
})

const totalColumns = computed(() => visibleColumns.value.length)

const computedUrlPath = computed(() => {
  if (props.urlPath) {
    return props.urlPath
  }

  return '/' + props.resourceName + '/' + 'table'
})

const selectedRows = computed(() =>
  collection.items.filter(row => row.tSelected)
)

const selectedRowsCount = computed(() => selectedRows.value.length)

const selectedRowsIds = computed(() => selectedRows.value.map(row => row.id))

const hasSelectedRows = computed(() => selectedRowsCount.value > 0)

const allRowsSelected = computed(
  () => selectedRowsCount.value === collection.items.length
)

const isSelectable = computed(() => {
  if (collection.items.length === 0) {
    return false
  }

  if (config.value.hasCustomActions !== null && config.value.hasCustomActions) {
    return true
  }

  return config.value.actions.length > 0
})

const {
  queryBuilderRules: rules,
  rulesAreValid,
  hasRulesApplied,
  rulesAreVisible,
  toggleFiltersRules,
  resetQueryBuilderRules,
} = useQueryBuilder(filtersIdentifier, filtersView)

const { hasRules, activeFilter } = useFilterable(filtersIdentifier, filtersView)

/**
 * Make new HTTP request
 *
 * @param {Boolean} viaUserSearch
 */
function request(viaUserSearch = false) {
  if (isLoading.value) {
    return
  }

  cancelPreviousRequest()

  setLoading(true)

  // Reset the current page as the search won't be accurate as there will
  // be offset on the query and if any results are found, won't be queried
  if (viaUserSearch && collection.currentPage !== 1) {
    setPage(1)
  }

  let queryStringParams = requestQueryStringParams.value

  if (rulesAreValid.value) {
    queryStringParams.rules = rules.value
  }

  if (search.value) {
    queryStringParams.q = search.value
  }

  Innoclapps.request()
    .get(computedUrlPath.value, {
      params: queryStringParams,
      cancelToken: new CancelToken(token => (requestCancelToken = token)),
    })
    .then(({ data }) => {
      collection.setState(data)
      configureWatchers()
      emit('loaded', { empty: isEmpty.value })
    })
    .finally(() => {
      setLoading(false)
      if (!initialDataLoaded.value) {
        // Add a little timeout so if there is no record and empty state
        // exists the table is not shown together with the empty state then hidden
        setTimeout(() => (initialDataLoaded.value = true), 150)
      }
    })
}

/**
 * Prepare the component
 */
function prepareComponent(settings) {
  collection.perPage = Number(settings.perPage)
  collection.set('order', settings.order)

  // Set the watchers after the inital data setup
  // This helps to immediately trigger watcher change|new value before setting the data
  nextTick(() => {
    if (hasRules.value) {
      // Configure the watchers for filters, the filters will update the data
      // and the watchers will catch the change in "requestQueryStringParams" to invoke the request
      configureWatchers()
    } else {
      request()
    }
    componentReady.value = true
  })
}

/**
 * Fetch the table settings
 *
 * @param {Boolean} force Indicates whether to force fetching the settings instead
 * of using directly from the store if they are already fetched
 */
async function fetchSettings(force = false) {
  let settings = await store.dispatch('table/getSettings', {
    resourceName: props.resourceName,
    params: props.dataRequestQueryString,
    id: props.tableId,
    force: force,
  })

  return settings
}

/**
 * Re-fetch the table actions
 *
 * @return {Array}
 */
async function refetchActions() {
  let actions = await store.dispatch('table/fetchActions', {
    resourceName: props.resourceName,
    params: props.dataRequestQueryString,
    id: props.tableId,
  })

  return actions
}

function setPage(page) {
  collection.currentPage = page
}

/**
 * Register the table reload listener
 *
 * @return {Void}
 */
function registerReloaders() {
  useGlobalEventListener(`${props.resourceName}-record-updated`, request)
  useGlobalEventListener('action-executed', actionExecutedRefresher)
  useGlobalEventListener('reload-resource-table', tableIdRefresher)
}

function actionExecutedRefresher(action) {
  if (action.resourceName === props.resourceName) {
    request()
  }
}

function tableIdRefresher(id) {
  if (id === props.tableId) {
    request()
  }
}

/**
 * Checks if there is previous request and cancel it
 */
function cancelPreviousRequest() {
  if (!requestCancelToken) {
    return
  }

  requestCancelToken()
}

/**
 * Apply filters
 *
 * @param {Object} rules
 *
 * @return {Void}
 */
function applyFilters(rules) {
  // Wait till Vuex is updated
  nextTick(request)
}

/**
 * Select row on table row click
 */
function selectOnRowClick(e, row) {
  if (!isSelectable.value) {
    return
  }

  // Only works when there is at least one row selected
  if (selectedRowsCount.value === 0) {
    return
  }

  if (
    e.target.tagName == 'INPUT' ||
    e.target.tagName == 'SELECT' ||
    e.target.tagName == 'TEXTAREA' ||
    e.target.isContentEditable ||
    e.target.tagName == 'A' ||
    e.target.tagName == 'BUTTON'
  ) {
    return
  }

  onRowSelected(row)
}

function onRowSelected(row) {
  row.tSelected = !row.tSelected
}

function toggleSelectAll() {
  if (allRowsSelected.value) {
    unselectAllRows()
  } else {
    selectAllRows()
  }
}

function unselectAllRows() {
  collection.items.forEach(row => (row.tSelected = false))
}

function selectAllRows() {
  collection.items.forEach(row => (row.tSelected = true))
}

/**
 * Configure the component necessary watched
 */
function configureWatchers() {
  if (watchersInitialized.value === true) {
    return
  }

  watchersInitialized.value = true

  watch(
    requestQueryStringParams,
    (newVal, oldVal) => {
      if (!isEqual(newVal, oldVal)) {
        request()
      }
    },
    { deep: true }
  )

  watch(
    () => config.value.perPage,
    function (newVal) {
      collection.perPage = Number(newVal)
    }
  )

  watch(
    () => config.value.order,
    newVal => {
      // Sometimes when fast switching through tables
      // the order is undefined
      if (newVal) {
        collection.set('order', newVal)
      }
    },
    {
      deep: true,
    }
  )
}

function handleMountedLifeCycle() {
  registerReloaders()
  fetchSettings().then(settings => prepareComponent(settings))
}

onMounted(handleMountedLifeCycle)

onBeforeUnmount(() => {
  cancelPreviousRequest()
})

onUnmounted(() => {
  // We will check if there is an active filter already applied in store before clearing QB
  // helps keeping the previous filter applied when navigating from the page
  // where the filters are mounted and then going back
  if (!activeFilter.value) {
    resetQueryBuilderRules()
  }
  rulesAreVisible.value = false
  collection.flush()
  setLoading(false)
})

defineExpose({
  refetchActions,
  setPage,
})
</script>
<style>
th.sortable-chosen.sortable-ghost {
  @apply text-primary-600 !important;
}

th.sortable-chosen.sortable-drag {
  @apply border border-dashed border-primary-600;
}
</style>
