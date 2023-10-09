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
  <timeline-entry
    :resource-name="resourceName"
    :created-at="log.created_at"
    :is-pinned="log.is_pinned"
    :timelineable-id="log.id"
    :timeline-relationship="log.timeline_relation"
    :timelineable-key="log.timeline_key"
    icon="PencilAlt"
  >
    <template #heading>
      <i18n-t
        v-once
        v-if="log.causer_name"
        scope="global"
        keypath="core::timeline.updated"
      >
        <template #causer>
          <span class="font-medium" v-text="log.causer_name"></span>
        </template>
      </i18n-t>
      <span v-else v-once v-text="$t('core::timeline.updated')"></span>
    </template>
    <div class="mt-2">
      <div v-if="changesVisible">
        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
          {{ $t('core::fields.updated') }} ({{ totalUpdatedAttributes }})
        </p>
        <div class="mt-2" v-once>
          <ITable class="overflow-hidden rounded-lg" bordered>
            <thead>
              <tr>
                <th class="text-left" v-t="'core::fields.updated_field'"></th>
                <th class="text-left" v-t="'core::fields.new_value'"></th>
                <th class="text-left" v-t="'core::fields.old_value'"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(attribute, key) in updatedAttributes"
                :key="resourceName + key"
              >
                <td>
                  {{ getLabel(attribute, key) }}
                </td>
                <td>
                  <!-- For custom fields -->
                  {{ determineChangedFieldValue(attribute, key) }}
                </td>
                <td>
                  <!-- For custom fields -->
                  {{ determineChangedFieldValue(log.properties.old[key], key) }}
                </td>
              </tr>
            </tbody>
          </ITable>
        </div>
      </div>
      <a
        href="#"
        @click.prevent="changesVisible = !changesVisible"
        class="link mt-2 block text-sm"
        v-text="updatedFieldsText"
      />
    </div>
  </timeline-entry>
</template>
<script setup>
import { ref, computed } from 'vue'
import pickBy from 'lodash/pickBy'
import TimelineEntry from './RecordTabTimelineTemplate.vue'
import propsDefinition from './props'
import { useI18n } from 'vue-i18n'
import { useDates } from '~/Core/resources/js/composables/useDates'

const props = defineProps(propsDefinition)

const { t, te } = useI18n()
const { maybeFormatDateValue } = useDates()
const changesVisible = ref(false)

const updatedFieldsText = computed(() =>
  changesVisible.value
    ? t('core::fields.hide_updated')
    : t('core::fields.view_updated') + ' (' + totalUpdatedAttributes.value + ')'
)

/**
 * Excluded the one that ends with _id because they are probably relation ID,
 * We are tracking the relations display name as well so we can display new and old value
 * in proper format not the actual ID.
 *
 */
const updatedAttributes = computed(() => {
  return pickBy(
    props.log.properties.attributes,
    (attribute, field) => field.indexOf('_id') === -1
  )
})

const totalUpdatedAttributes = computed(
  () => Object.keys(updatedAttributes.value).length
)

function getLabel(attribute, key) {
  // Check if the attributes has label key, usually used in custom fields where
  // label and value is stored separately e.q. {label: 'Label', value:'Value'}
  if (attribute && attribute.label) {
    return attribute.label
  }

  if (
    (props.log.module,
    props.log.module + '::fields.' + props.resourceName + '.' + key)
  ) {
    if (te(props.log.module + '::fields.' + props.resourceName + '.' + key)) {
      return t(props.log.module + '::fields.' + props.resourceName + '.' + key)
    }
  }

  return key
}

function determineChangedFieldValue(data, key) {
  return data && data.hasOwnProperty('value')
    ? maybeFormatDateValue(data.value, data.value)
    : te('core::timeline.' + key + '.' + data)
    ? t('core::timeline.' + key + '.' + data)
    : maybeFormatDateValue(data, data)
}
</script>
