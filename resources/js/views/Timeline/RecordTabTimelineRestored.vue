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
  <TimelineEntry
    :resource-name="resourceName"
    :created-at="log.created_at"
    :is-pinned="log.is_pinned"
    :timelineable-id="log.id"
    :timeline-relationship="log.timeline_relation"
    :timelineable-key="log.timeline_key"
    icon="Refresh"
  >
    <template #heading>
      <i18n-t v-once scope="global" keypath="core::timeline.restored">
        <template #causer>
          <span class="font-medium" v-text="log.causer_name"></span>
        </template>
      </i18n-t>
    </template>
  </TimelineEntry>
</template>
<script setup>
import TimelineEntry from './RecordTabTimelineTemplate.vue'
import propsDefinition from './props'
const props = defineProps(propsDefinition)
</script>
