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
    :icon="log.properties.icon || 'User'"
    :heading="$t(log.properties.lang.key, langAttributes)"
  />
</template>

<script setup>
import { computed } from 'vue'
import get from 'lodash/get'
import { getLocale } from '@/utils'
import TimelineEntry from './RecordTabTimelineTemplate.vue'
import propsDefinition from './props'
const props = defineProps(propsDefinition)
const langAttributes = computed(() => {
  // Create new object of the attributes
  // because we are mutating the store below
  let attributes = props.log.properties.lang.attrs

  if (!attributes) {
    return null
  }

  // Automatically add causer_name in case user attr is
  // provided with null value or the lang key has :user attribute but
  // user attribute is not provided
  if (
    (get(
      window.lang[getLocale()],
      props.log.properties.lang.key.replace('::', '.')
    ).indexOf('{user}') > -1 &&
      Object.keys(attributes).indexOf('user') === -1) ||
    (Object.keys(attributes).indexOf('user') > -1 &&
      attributes['user'] === null)
  ) {
    // To avoid mutations errors, assign new object
    attributes = Object.assign({}, attributes, {
      user: props.log.causer_name,
    })
  }

  return attributes
})
</script>
