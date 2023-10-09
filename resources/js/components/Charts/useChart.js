/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */
import { computed } from 'vue'
import map from 'lodash/map'

export function useChart(result) {
  /**
   * Check whether there is a chart data to display
   */
  const hasData = computed(() => {
    const data = chartData.value
    const totalSeries = data.series.length

    if (totalSeries === 0) {
      return false
    }

    let anySerieHasData = false
    for (let i = 0; i < totalSeries; i++) {
      if (data.series[i].length > 0) {
        anySerieHasData = data.series[i].some(val => val.value > 0)

        if (anySerieHasData) {
          break
        }
      }
    }
    return anySerieHasData
  })

  /**
   * Get the chart ready data
   */
  const chartData = computed(() => {
    return {
      labels: map(result.value, data => data.label),
      series: [
        map(result.value, data => {
          return {
            meta: data.label,
            value: data.value,
            color: data.color,
          }
        }),
      ],
    }
  })

  return { chartData, hasData }
}
