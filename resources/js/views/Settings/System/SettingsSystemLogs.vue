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
  <ICard header="Logs" no-body>
    <template #actions>
      <div class="inline-flex">
        <IFormSelect
          :option="logTypes"
          v-model="logType"
          class="mr-2"
          @input="$router.replace({ query: { type: $event, date: date } })"
        >
          <option v-for="logType in logTypes" :key="logType" :value="logType">
            {{ logType }}
          </option>
        </IFormSelect>
        <IFormSelect
          v-model="date"
          @input="$router.replace({ query: { date: $event, type: logType } })"
        >
          <option v-for="date in log.log_dates" :key="date" :value="date">
            {{ date }}
          </option>
        </IFormSelect>
      </div>
    </template>
    <ITable class="-mt-px">
      <thead>
        <tr>
          <th class="text-left" width="100%">Log</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(line, index) in filteredLogs" :key="index">
          <td width="60%" class="whitespace-pre-line break-all">
            <div class="mb-3 flex justify-between">
              <div class="flex space-x-2">
                <IBadge
                  :text="line.type"
                  :variant="logTypesClassMaps[line.type]"
                />
                <IButtonCopy :text="line.message" />
              </div>
              <p v-text="line.timestamp" />
            </div>
            <code>
              <TextCollapse :text="line.message" :length="500" />
            </code>
          </td>
        </tr>
        <td
          colspan="4"
          class="p-4 text-center text-sm text-neutral-500 dark:text-neutral-300"
          v-show="!hasLogs"
        >
          {{ log.message || 'No logs to show.' }}
        </td>
      </tbody>
    </ITable>
  </ICard>
</template>
<script setup>
import { ref, shallowRef, computed, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const log = shallowRef({})

const date = ref(route.query.date || moment.utc().format('YYYY-MM-DD'))
const logType = ref(route.query.type || 'ALL')

const logTypes = [
  'ALL',
  'INFO',
  'EMERGENCY',
  'CRITICAL',
  'ALERT',
  'ERROR',
  'WARNING',
  'NOTICE',
  'DEBUG',
]

const logTypesClassMaps = {
  INFO: 'info',
  DEBUG: 'neutral',
  EMERGENCY: 'danger',
  CRITICAL: 'danger',
  NOTICE: 'neutral',
  WARNING: 'warning',
  ERROR: 'danger',
  ALERT: 'warning',
}

watch(date, retrieve)

const filteredLogs = computed(() => {
  if (!log.value.logs) {
    return []
  }

  if (!logType.value || logType.value === 'ALL') {
    return sortLogsByDate(log.value.logs)
  }

  return sortLogsByDate(log.value.logs.filter(l => l.type === logType.value))
})

const hasLogs = computed(
  () => filteredLogs.value && filteredLogs.value.length > 0
)

function sortLogsByDate(logs) {
  return logs.sort(function compare(a, b) {
    var dateA = new Date(a.timestamp)
    var dateB = new Date(b.timestamp)

    return dateB - dateA
  })
}

function retrieve() {
  Innoclapps.request()
    .get('/system/logs', {
      params: {
        date: date.value,
      },
    })
    .then(({ data }) => {
      log.value = data

      if (data.log_dates.indexOf(date.value) === -1) {
        log.value.log_dates.push(date.value)
      }
    })
}

retrieve()
</script>
