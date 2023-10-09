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
  <div ref="chartRef" class="ct-chart" />
</template>
<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { LineChart, Interpolation } from 'chartist'
import ChartistTooltip from 'chartist-plugin-tooltips-updated'
import 'chartist/dist/index.css'
import 'chartist-plugin-tooltips-updated/dist/chartist-plugin-tooltip.css'
import { useAccounting } from '~/Core/resources/js/composables/useAccounting'

const props = defineProps(['chartData', 'amountValue'])

let chartist = null
const chartRef = ref(null)

const { formatMoney } = useAccounting()

function refreshChart() {
  chartist.update(props.chartData)
}

function destroy() {
  if (chartist) {
    chartist.detach()
  }
}

watch(() => props.chartData, refreshChart)

onMounted(() => {
  chartist = new LineChart(chartRef.value, props.chartData, {
    lineSmooth: Interpolation.none(),
    fullWidth: true,
    showPoint: true,
    showLine: true,
    showArea: true,
    chartPadding: {
      top: 10,
      right: 1,
      bottom: 1,
      left: 1,
    },
    low: 0,
    axisX: {
      showGrid: false,
      showLabel: true,
      offset: 0,
    },
    axisY: {
      showGrid: false,
      showLabel: true,
      offset: 0,
    },
    plugins: [
      ChartistTooltip({
        pointClass: 'ct-point',
        anchorToPoint: false,
        appendToBody: false,
        transformTooltipTextFnc: value => {
          return props.amountValue ? formatMoney(value) : value
        },
      }),
    ],
  })
})

onBeforeUnmount(destroy)
</script>
