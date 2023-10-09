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
  <IPopover
    flip
    :busy="disabled"
    :class="widthClass"
    :title="$t('core::app.associate_with_record')"
    :placement="placement"
    @hide="cancelSearch"
  >
    <button
      type="button"
      class="link text-sm focus:outline-none"
      v-text="associationsText"
    />

    <template #popper>
      <div class="p-4">
        <IFormGroup class="relative">
          <IFormInput
            @input="search"
            v-model="searchQuery"
            class="pr-8"
            :placeholder="searchInputPlaceholder"
          />
          <a
            href="#"
            @click.prevent="cancelSearch"
            v-show="searchQuery"
            class="absolute right-3 top-2.5 focus:outline-none"
          >
            <Icon icon="X" class="h-5 w-5 text-neutral-400" />
          </a>
        </IFormGroup>
        <IOverlay :show="isLoading">
          <p
            v-show="
              isSearching &&
              !hasSearchResults &&
              !isLoading &&
              !minimumAsyncCharactersRequirement
            "
            class="text-center text-sm text-neutral-600 dark:text-neutral-300"
            v-t="'core::app.no_search_results'"
          />
          <p
            v-show="isSearching && minimumAsyncCharactersRequirement"
            class="text-center text-sm text-neutral-600 dark:text-neutral-300"
            v-text="
              $t('core::app.type_more_to_search', {
                characters: totalCharactersLeftToPerformSearch,
              })
            "
          />
          <div v-for="data in records" :key="data.resource">
            <p
              v-show="data.records.length > 0"
              class="mb-2 mt-3 text-sm font-medium text-neutral-700 dark:text-neutral-100"
              v-text="data.title"
            />
            <div
              v-for="(record, index) in data.records"
              :key="data.resource + '-' + record.id"
              class="flex items-center"
            >
              <IFormCheckbox
                class="grow"
                :id="data.resource + '-' + record.id"
                :value="record.id"
                :disabled="
                  record.disabled ||
                  (primaryRecordDisabled === true &&
                    primaryResourceName === data.resource &&
                    hasPrimaryRecord &&
                    Number(primaryRecord.id) === Number(record.id))
                "
                @change="
                  onCheckboxChange(record, data.resource, data.is_search)
                "
                v-model:checked="selected[data.resource]"
              >
                {{ record.display_name }}
              </IFormCheckbox>

              <a
                :href="record.path"
                class="mt-0.5 self-start text-neutral-500 dark:text-neutral-400"
              >
                <Icon icon="Eye" class="ml-2 h-4 w-4" />
              </a>

              <slot
                name="after-record"
                :index="index"
                :title="data.title"
                :resource="data.resource"
                :record="record"
                :isSearching="isSearching"
                :isSelected="selected[data.resource].includes(record.id)"
                :selectedRecords="selected[data.resource]"
              />
            </div>
          </div>
        </IOverlay>
      </div>
    </template>
  </IPopover>
</template>
<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import findIndex from 'lodash/findIndex'
import debounce from 'lodash/debounce'
import orderBy from 'lodash/orderBy'
import sortBy from 'lodash/sortBy'
import map from 'lodash/map'
import uniq from 'lodash/uniq'
import isObject from 'lodash/isObject'
import cloneDeep from 'lodash/cloneDeep'
import castArray from 'lodash/castArray'
import { CancelToken } from '~/Core/resources/js/services/HTTP'
import { useI18n } from 'vue-i18n'
import { useLoader } from '~/Core/resources/js/composables/useLoader'

const emit = defineEmits(['update:modelValue', 'change'])

const props = defineProps({
  widthClass: { type: String, default: 'w-72' },
  // The actual v-model for the selected associations
  modelValue: Object,

  primaryResourceName: String,

  primaryRecordDisabled: Boolean,

  primaryRecord: Object,

  initialAssociateables: Object,
  associateables: [Object, Array],

  limitInitialAssociateables: { type: Number, default: 3 },

  // Indicates whether the popover is disabled
  disabled: { type: Boolean, default: false },

  // The popover placement
  placement: { type: String, default: 'bottom' },

  excludedResources: [String, Array],
})

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()

const minimumAsyncCharacters = 2
const totalAsyncSearchCharacters = ref(0)
const minimumAsyncCharactersRequirement = ref(false)
const limitSearchResults = 5
const searchQuery = ref('')
// The selected associations
const selected = ref({})
// Associations selected from search results
const selectedFromSearchResults = ref({})
const searchResults = ref({})
const cancelTokens = {}

const availableAssociateables = {
  contacts: {
    title: t('contacts::contact.contacts'),
    resource: 'contacts',
  },
  companies: {
    title: t('contacts::company.companies'),
    resource: 'companies',
  },
  deals: {
    title: t('deals::deal.deals'),
    resource: 'deals',
  },
}

if (props.excludedResources) {
  castArray(props.excludedResources).forEach(resourceName => {
    delete availableAssociateables[resourceName]
  })
}

const totalCharactersLeftToPerformSearch = computed(
  () => minimumAsyncCharacters - totalAsyncSearchCharacters.value
)

const searchInputPlaceholder = computed(() => t('core::app.search_records'))

const hasPrimaryRecord = computed(() => Boolean(props.primaryRecord))

/**
 * The available associations resources, sorted as the primary is always first
 */
const resources = computed(() =>
  sortBy(Object.keys(availableAssociateables), resource => {
    return [resource !== props.primaryResourceName, resource]
  })
)

const hasSearchResults = computed(() => {
  let result = false
  resources.value.every(resource => {
    result = searchResults.value[resource]
      ? searchResults.value[resource].records.length > 0
      : false
    return result ? false : true
  })

  return result
})

const records = computed(() => {
  if (hasSearchResults.value) {
    return searchResults.value
  }

  let data = {}

  let addRecord = (resourceName, record) => {
    if (
      findIndex(data[resourceName].records, ['id', Number(record.id)]) === -1
    ) {
      data[resourceName].records.push(record)
    }
  }

  resources.value.forEach(resource => {
    data[resource] = Object.assign({}, availableAssociateables[resource], {
      records: [],
    })

    // Push the primary associateable
    if (resource === props.primaryResourceName && hasPrimaryRecord.value) {
      addRecord(resource, props.primaryRecord)
    }

    if (props.initialAssociateables) {
      getParsedAssociateablesFromInitialData(resource).forEach(record =>
        addRecord(resource, record)
      )
    }

    if (Array.isArray(props.associateables)) {
      props.associateables.forEach((resources, key) => {
        Object.keys(resources).forEach(associateableResource => {
          if (associateableResource === resource) {
            props.associateables[key][associateableResource].forEach(record =>
              addRecord(resource, record)
            )
          }
        })
      })
    } else if (
      props.associateables &&
      typeof (props.associateables === 'object') &&
      props.associateables.hasOwnProperty(resource)
    ) {
      props.associateables[resource].forEach(record =>
        addRecord(resource, record)
      )
    }

    // Check for any selected from search
    if (selectedFromSearchResults.value[resource]) {
      selectedFromSearchResults.value[resource].records.forEach(record =>
        addRecord(resource, record)
      )
    }
  })

  return data
})

const isSearching = computed(() => searchQuery.value != '')

const associationsText = computed(() => {
  let totalSelected = 0
  resources.value.forEach(resource => {
    totalSelected += selected.value[resource]
      ? selected.value[resource].length
      : 0
  })

  if (totalSelected === 0) {
    return t('core::app.no_associations')
  }

  return t('core::app.associated_with_total_records', {
    total: totalSelected,
  })
})

function getParsedAssociateablesFromInitialData(resource) {
  return orderBy(
    (props.initialAssociateables[resource] || []).slice(
      0,
      props.limitInitialAssociateables
    ),
    'created_at',
    'desc'
  )
}

function createResolveableRequests(q) {
  // The order of the promises must be the same
  // like in the order of the availableAssociateables keys data variable
  let promises = []
  resources.value.forEach(resource => {
    promises.push(
      Innoclapps.request().get(`/${resource}/search`, {
        params: {
          q: q,
          take: limitSearchResults,
        },
        cancelToken: new CancelToken(token => (cancelTokens[resource] = token)),
      })
    )
  })

  return promises
}

function cancelPreviousRequests() {
  Object.keys(cancelTokens).forEach(resource => {
    if (cancelTokens[resource]) {
      cancelTokens[resource]()
    }
  })
}

function onCheckboxChange(record, resource, fromSearch) {
  if (!selectedFromSearchResults.value[resource] && fromSearch) {
    selectedFromSearchResults.value[resource] = {
      records: [],
      is_search: fromSearch,
    }
  }

  nextTick(() => {
    // User checked record selected from search
    if (selected.value[resource].includes(record.id) && fromSearch) {
      selectedFromSearchResults.value[resource].records.push(record)
    } else if (selectedFromSearchResults.value[resource]) {
      // Unchecked, now remove it it from the selectedFromSearchResults
      let selectedIndex = findIndex(
        selectedFromSearchResults.value[resource].records,
        ['id', Number(record.id)]
      )

      if (selectedIndex != -1) {
        selectedFromSearchResults.value[resource].records.splice(
          selectedIndex,
          1
        )
      }
    }

    emit('change', selected.value)
  })
}

function cancelSearch() {
  searchQuery.value = ''
  search('')
  cancelPreviousRequests()
}

const search = debounce(function (q) {
  const totalCharacters = q.length

  if (totalCharacters === 0) {
    searchResults.value = {}
    return
  }

  totalAsyncSearchCharacters.value = totalCharacters

  if (totalCharacters < minimumAsyncCharacters) {
    minimumAsyncCharactersRequirement.value = true

    return q
  }

  minimumAsyncCharactersRequirement.value = false
  cancelPreviousRequests()
  setLoading(true)

  Promise.all(createResolveableRequests(q)).then(values => {
    resources.value.forEach((resource, key) => {
      searchResults.value[resource] = Object.assign(
        {},
        availableAssociateables[resource],
        {
          records: map(values[key].data, record => {
            record.from_search = true
            return record
          }),
          is_search: true,
        }
      )
    })

    setLoading(false)
  })
}, 650)

function getModelValueResourceIds(resourceName) {
  if (!props.modelValue || !props.modelValue[resourceName]) {
    return []
  }

  return isObject(props.modelValue[resourceName][0])
    ? props.modelValue[resourceName].map(record => record.id)
    : props.modelValue[resourceName]
}

function setSelectedRecords() {
  let allSelected = {}

  resources.value.forEach(resource => {
    let resourceSelected = cloneDeep(getModelValueResourceIds(resource))

    // When provided and not disabled, the primary resource is always selected.
    if (
      resource === props.primaryResourceName &&
      props.primaryRecordDisabled === true &&
      hasPrimaryRecord.value
    ) {
      resourceSelected.push(props.primaryRecord.id)
    }

    allSelected[resource] = uniq(resourceSelected)
  })

  selected.value = allSelected

  emit('update:modelValue', allSelected)
}

// Watcher for all associated ID's via the model value
watch(
  () => {
    let ids = []
    resources.value.forEach(resourceName => {
      ids = ids.concat(getModelValueResourceIds(resourceName))
    })

    return ids.join(',')
  },
  () => {
    setSelectedRecords()
  }
)

onMounted(setSelectedRecords)
</script>
