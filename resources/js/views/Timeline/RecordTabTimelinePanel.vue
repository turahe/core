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
  <ITabPanel>
    <div class="mt-4 inline-flex items-center sm:ml-1 sm:mt-2">
      <p
        class="-mt-0.5 mr-1.5 text-sm font-medium text-neutral-700 dark:text-neutral-300"
        v-t="'core::filters.filter_by'"
      />
      <DropdownSelectInput
        :items="filters"
        value-key="id"
        label-key="name"
        @change="loadData"
        v-model="filter"
      />
    </div>
    <div class="pt-6">
      <div class="flow-root">
        <ul role="list" class="sm:-mb-8">
          <li
            v-for="(entry, index) in timeline"
            :key="'timeline-' + entry.timeline_component + '-' + entry.id"
          >
            <div class="relative sm:pb-8">
              <span
                v-if="index !== timeline.length - 1"
                class="absolute left-5 top-5 -ml-px hidden h-full w-0.5 bg-neutral-200 dark:bg-neutral-600 sm:block"
                aria-hidden="true"
              />
              <div class="relative flex items-start sm:space-x-3">
                <component
                  :is="
                    timelineComponents.hasOwnProperty(
                      'timeline-' + entry.timeline_component
                    )
                      ? timelineComponents[
                          'timeline-' + entry.timeline_component
                        ]
                      : entry.timeline_component
                  "
                  :log="entry"
                  :resource-name="resourceName"
                  :resource-record="record"
                />
              </div>
            </div>
            <div
              v-if="index !== timeline.length - 1"
              class="block sm:hidden"
              aria-hidden="true"
            >
              <div class="py-5">
                <div
                  class="border-t border-neutral-200 dark:border-neutral-600"
                />
              </div>
            </div>
          </li>
        </ul>
        <InfinityLoader
          @handle="infiniteHandler($event, 'changelog')"
          :scroll-element="scrollElement"
          ref="infinityRef"
        />
      </div>
    </div>
  </ITabPanel>
</template>
<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import TimelineCreated from './RecordTabTimelineCreated.vue'
import TimelineUpdated from './RecordTabTimelineUpdated.vue'
import TimelineAttached from './RecordTabTimelineAttached.vue'
import TimelineDetached from './RecordTabTimelineDetached.vue'
import TimelineGeneric from './RecordTabTimelineGeneric.vue'
import TimelineDeleted from './RecordTabTimelineDeleted.vue'
import TimelineRestored from './RecordTabTimelineRestored.vue'
import InfinityLoader from '~/Core/resources/js/components/InfinityLoader.vue'
import orderBy from 'lodash/orderBy'
import findIndex from 'lodash/findIndex'
import { useRecordTab } from '~/Core/resources/js/composables/useRecordTab'
import { useI18n } from 'vue-i18n'
import { useRecordStore } from '~/Core/resources/js/composables/useRecordStore'

const props = defineProps({
  resourceName: { required: true, type: String },
  scrollElement: { type: String },
  resources: {
    type: Array,
    default() {
      return ['notes', 'calls', 'activities', 'emails', 'documents']
    },
  },
})

const timelineComponents = {
  'timeline-restored': TimelineRestored,
  'timeline-deleted': TimelineDeleted,
  'timeline-created': TimelineCreated,
  'timeline-updated': TimelineUpdated,
  'timeline-attached': TimelineAttached,
  'timeline-detached': TimelineDetached,
  'timeline-generic': TimelineGeneric,
}

const { t } = useI18n()
const infinityRef = ref(null)

const {
  addResourceRecordHasManyRelationship,
  setResourceRecordHasManyRelationship,
  updateResourceRecordHasManyRelationship,
} = useRecordStore()

const {
  record,
  loadData,
  infiniteHandler,
  defaultPerPage: perPage,
  search,
} = useRecordTab({
  resourceName: props.resourceName,
  infinityRef: infinityRef,
  scrollElement: props.scrollElement,
  handleInfinityResult: handleInfinityResult,
  makeRequestForData: makeRequestForData,
})

const filters = [
  { id: null, name: t('core::app.all') },
  { id: 'changelog', name: t('core::app.changelog') },
  { id: 'activities', name: t('activities::activity.activities') },
  { id: 'emails', name: t('mailclient::mail.emails') },
  { id: 'documents', name: t('documents::document.documents') },
  { id: 'calls', name: t('calls::call.calls') },
  { id: 'notes', name: t('notes::note.notes') },
].filter(
  filter =>
    props.resources.indexOf(filter.id) > -1 ||
    filter.id === null ||
    filter.id === 'changelog'
)

const filter = ref({
  id: null,
  name: t('core::app.all'),
})

const changelog = computed(() => {
  // The changelog is returned too from the record request
  // these are the general changelog related to the model
  // in this case, when the record is updated the new changelog
  // are able to be reflected and shown in the tab
  return !filter.value.id || filter.value.id === 'changelog'
    ? record.value.changelog || []
    : []
})

const notes = computed(() => {
  return !filter.value.id || filter.value.id === 'notes'
    ? record.value.notes || []
    : []
})

const calls = computed(() => {
  return !filter.value.id || filter.value.id === 'calls'
    ? record.value.calls || []
    : []
})

const emails = computed(() => {
  return !filter.value.id || filter.value.id === 'emails'
    ? record.value.emails || []
    : []
})

const documents = computed(() => {
  return !filter.value.id || filter.value.id === 'documents'
    ? record.value.documents || []
    : []
})

const activities = computed(() => {
  return !filter.value.id || filter.value.id === 'activities'
    ? record.value.activities || []
    : []
})

const timeline = computed(() => {
  return orderBy(
    [
      ...changelog.value,
      ...notes.value,
      ...calls.value,
      ...emails.value,
      ...documents.value,
      ...activities.value,
    ],
    ['is_pinned', 'pinned_date', log => new Date(log[log.timeline_sort_column])],
    ['desc', 'desc', 'desc']
  )
})

function makeRequestForData(associateable, page) {
  return Innoclapps.request().get(`${record.value.path}/timeline`, {
    params: {
      page: page,
      q: search.value,
      per_page: perPage.value,
      resources: props.resources,
    },
  })
}

function handleInfinityResult(data) {
  let newRecords = {}

  data.data.forEach(entry => {
    let relationship = entry.timeline_relation

    if (!newRecords.hasOwnProperty(relationship)) {
      newRecords[relationship] = []
    }

    let existsInStore =
      findIndex(record.value[relationship], ['id', Number(entry.id)]) !== -1

    if (!existsInStore) {
      newRecords[relationship].push(entry)
    } else {
      updateResourceRecordHasManyRelationship(entry, relationship)
    }
  })

  // For initial load records, do full replace of the relationship to avoid
  // triggering multiple updates, for new records in an existing relationship, perform push
  Object.keys(newRecords).forEach(relationship => {
    if (!record.value[relationship]) {
      setResourceRecordHasManyRelationship(
        relationship,
        newRecords[relationship]
      )
    } else {
      newRecords[relationship].forEach(entry => {
        addResourceRecordHasManyRelationship(entry, relationship, true)
      })
    }
  })

  newRecords = null
}

onMounted(() => {
  nextTick(loadData)
})
</script>
