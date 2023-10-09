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
  <div>
    <div
      class="flex flex-col items-center justify-between px-7 py-3 md:flex-row"
    >
      <TablePerPageOptions
        :collection="collection"
        class="mb-2 md:mb-0"
        @change="loadItems"
        :disabled="isLoading"
      />
      <div class="w-full md:w-auto">
        <SearchInput
          v-model="search"
          @input="performSearch"
        />
      </div>
    </div>
    <IOverlay :show="isLoading">
      <ITable :id="tableId" v-bind="tableProps" ref="tableRef">
        <thead>
          <tr>
            <HeaderCell
              v-for="field in fields"
              :key="'th-' + field.key"
              ref="tableHeadingsRef"
              :class="{
                hidden: stacked[field.key],
              }"
              :is-sortable="field.sortable"
              :heading="field.label"
              :heading-key="field.key"
              v-model:ctx="ctx"
              @update:ctx="loadItems"
            />
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in collection.items"
            :key="'tr-' + item.id"
            :class="[item.trClass ? item.trClass : null]"
          >
            <DataCell
              v-for="field in fields"
              :key="'td-' + field.key"
              :field="field"
              :item="item"
              :formatter="dataCellFormatter"
              :class="{
                hidden: stacked[field.key],
              }"
            >
              <template v-slot="slotProps">
                <slot v-bind="slotProps" :name="field.key">
                  <span v-if="field.key === fields[0].key && item.path">
                    <router-link class="link" :to="item.path">
                      {{ slotProps.formatted }}
                    </router-link>
                  </span>
                  <span v-else v-text="slotProps.formatted"> </span>
                </slot>
                <!-- Stacked -->
                <template v-if="field.key === fields[0].key">
                  <StackedDataCell
                    v-for="stackedField in stackedFields"
                    :key="'stacked-' + stackedField.key"
                    :field="stackedField"
                    :item="item"
                    :formatter="dataCellFormatter"
                  >
                    <template v-slot="stackedSlotProps">
                      <slot v-bind="stackedSlotProps" :name="stackedField.key">
                        <span class="text-neutral-700 dark:text-neutral-300">
                          {{ stackedSlotProps.formatted }}
                        </span>
                      </slot>
                    </template>
                  </StackedDataCell>
                </template>
              </template>
            </DataCell>
          </tr>
          <tr v-if="!collection.hasItems">
            <td :colspan="totalFields" class="!text-sm !font-normal">
              <slot
                name="empty"
                :text="emptyText"
                :loading="isLoading"
                :search="search"
              >
                {{ emptyText }}
              </slot>
            </td>
          </tr>
        </tbody>
      </ITable>
    </IOverlay>
    <TablePagination
      v-if="collection.hasPagination"
      class="px-7 py-3"
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
</template>
<script setup>
import { ref, reactive, computed, watch, onUnmounted, nextTick } from 'vue'
import Paginator from '~/Core/resources/js/services/ResourcePaginator'
import TablePagination from './../TablePagination.vue'
import TablePerPageOptions from './../TablePerPageOptions.vue'
import { CancelToken } from '~/Core/resources/js/services/HTTP'
import debounce from 'lodash/debounce'
import HeaderCell from './TableSimpleHeaderCell.vue'
import DataCell from './TableSimpleDataCell.vue'
import StackedDataCell from './TableSimpleStackedDataCell.vue'
import { useI18n } from 'vue-i18n'
import { useLoader } from '~/Core/resources/js/composables/useLoader'
import { useEventListener } from '@vueuse/core'
import { useResponsiveTable } from '../useResponsiveTable'

const emit = defineEmits(['data-loaded'])

const props = defineProps({
  stackable: Boolean,
  requestUri: { required: true, type: String },
  requestQueryString: Object,
  tableId: { required: true, type: String },
  actionColumn: Boolean,
  initialData: Object,
  fields: Array,
  // Initial sort by field key/name
  sortBy: String,
  tableProps: {
    type: Object,
    default() {
      return {}
    },
  },
})

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()
const { isColumnVisible } = useResponsiveTable()

const tableRef = ref(null)
const search = ref('')
const initialDataSet = ref(false)

let requestCancelToken = null

const collection = ref(new Paginator())
const replaceCollectionData = ref(null)
const stacked = reactive({})

const ctx = ref({
  sortBy: null,
  direction: null,
})

const tableHeadingsRef = ref([])

const emptyText = computed(() => {
  if (collection.value.hasItems) {
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

const stackedFields = computed(() =>
  props.fields.filter(field => stacked[field.key])
)

const totalFields = computed(() => props.fields.length)

const queryString = computed(() => ({
  page: collection.value.currentPage,
  per_page: collection.value.perPage,
  q: search.value,
  order: [
    {
      field: ctx.value.sortBy || props.sortBy,
      direction: ctx.value.direction || 'asc',
    },
  ],
  ...(props.requestQueryString || {}),
}))

const performSearch = debounce(loadItems, 400)

watch(() => collection.value.currentPage, loadItems, { immediate: true })

function dataCellFormatter(item, field) {
  return field.formatter
    ? field.formatter(item[field.key], field.key, item)
    : item[field.key]
}

function replaceCollection(data) {
  replaceCollectionData.value = data
  reload()
}

function reload() {
  loadItems()
}

function request() {
  cancelPreviousRequest()

  setLoading(true)

  Innoclapps.request()
    .get(`/${props.requestUri}`, {
      params: queryString.value,
      cancelToken: new CancelToken(token => (requestCancelToken = token)),
    })
    .then(({ data }) => {
      // cards support data.items
      collection.value.setState(data.items ? data.items : data)

      emit('data-loaded', {
        items: collection.value.items,
        requestQueryString: queryString.value,
      })

      props.stackable && nextTick(stackColumns)
    })
    .finally(() => setLoading(false))
}

function loadItems() {
  if (!initialDataSet.value && props.initialData) {
    initialDataSet.value = true
    collection.value.setState(props.initialData)
    props.stackable && nextTick(stackColumns)
  } else if (replaceCollectionData.value !== null) {
    collection.value.setState(replaceCollectionData.value)
    replaceCollectionData.value = null
    props.stackable && nextTick(stackColumns)
  } else {
    request()
  }
}

function cancelPreviousRequest() {
  if (requestCancelToken) {
    requestCancelToken()
  }
}

function stackColumns() {
  props.fields.forEach((field, idx) => {
    if (idx > 0 && tableHeadingsRef.value[idx]) {
      stacked[field.key] = !isColumnVisible(
        tableHeadingsRef.value[idx].$el,
        tableRef.value.$el
      )
    }
  })
}

if (props.stackable) {
  useEventListener(window, 'resize', stackColumns)
}

onUnmounted(() => {
  collection.value.flush()
})

defineExpose({ reload, replaceCollection })
</script>
