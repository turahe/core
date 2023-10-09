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
  <Card
    class="h-56 md:h-52"
    :overflow-hidden="false"
    no-body
    :card="card"
    :request-query-string="requestQueryString"
    @retrieved="result = $event.card.result"
  >
    <template v-for="(_, name) in $slots" v-slot:[name]="slotData">
      <slot :name="name" v-bind="slotData" />
    </template>
    <div class="relative" :class="variant">
      <BasePresentationChart
        v-if="hasChartData"
        class="px-1"
        :chart-data="chartData"
        :amount-value="card.amount_value"
        :horizontal="card.horizontal"
        :only-integer="card.onlyInteger"
        :axis-y-offset="card.axisYOffset"
        :axis-x-offset="card.axisXOffset"
      />
      <p
        v-else
        class="mt-12 text-center text-sm text-neutral-400 dark:text-neutral-300"
        v-t="'core::app.not_enough_data'"
      />
    </div>
  </Card>
</template>
<script setup>
import { computed, shallowRef } from 'vue'

import BasePresentationChart from './Base/PresentationChart.vue'
import { useChart } from './useChart'

const props = defineProps({
  card: { required: true, type: Object },
  requestQueryString: {
    type: Object,
    default() {
      return {}
    },
  },
})

const result = shallowRef(props.card.result)
const variant = computed(() => props.card.color || 'chart-primary')
const { chartData, hasData: hasChartData } = useChart(result)
</script>
