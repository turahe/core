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
  <a
    v-if="!isPinned"
    href="#"
    @click.prevent="pin"
    class="text-xs text-neutral-800 focus:outline-none hover:text-neutral-500 dark:text-neutral-200 dark:hover:text-neutral-400"
    v-t="'core::timeline.pin'"
  />
  <a
    v-else
    href="#"
    class="text-xs text-neutral-800 focus:outline-none hover:text-neutral-500 dark:text-neutral-200 dark:hover:text-neutral-400"
    @click.prevent="unpin"
    v-t="'core::timeline.unpin'"
  />
</template>
<script setup>
import { useRecordStore } from '~/Core/resources/js/composables/useRecordStore'
import { useDates } from '~/Core/resources/js/composables/useDates'

const props = defineProps({
  resourceName: { type: String, required: true },
  isPinned: { type: Boolean, required: true },
  timelineRelationship: { type: String, required: true },
  timelineableKey: { type: String, required: true },
  timelineableId: { type: [Number, String], required: true },
})

const { appMoment } = useDates()

const { record, updateResourceRecordHasManyRelationship } = useRecordStore()

function pin() {
  Innoclapps.request()
    .post('timeline/pin', {
      subject_id: Number(record.value.id),
      subject_type: record.value.timeline_subject_key,
      timelineable_id: Number(props.timelineableId),
      timelineable_type: props.timelineableKey,
    })
    .then(() => {
      updateResourceRecordHasManyRelationship(
        {
          id: props.timelineableId,
          is_pinned: true,
          pinned_date: appMoment().toISOString(), // toISOString allowing consistency with the back-end dates
        },
        props.timelineRelationship
      )
    })
}

function unpin() {
  Innoclapps.request()
    .post('timeline/unpin', {
      subject_id: Number(record.value.id),
      subject_type: record.value.timeline_subject_key,
      timelineable_id: Number(props.timelineableId),
      timelineable_type: props.timelineableKey,
    })
    .then(() => {
      updateResourceRecordHasManyRelationship(
        {
          id: props.timelineableId,
          is_pinned: false,
          pinned_date: null,
        },
        props.timelineRelationship
      )
    })
}
</script>
