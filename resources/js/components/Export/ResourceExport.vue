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
  <IModal
    :id="modalId"
    :title="computedTitle"
    :ok-loading="exportInProgress"
    :ok-disabled="exportInProgress"
    :ok-title="$t('core::app.export.export')"
    @ok="performExport"
  >
    <IFormGroup :label="$t('core::app.export.type')">
      <IFormSelect v-model="form.type">
        <option value="csv">CSV</option>
        <option value="xls">XLS</option>
        <option value="xlsx">XLSX</option>
      </IFormSelect>
    </IFormGroup>
    <IFormGroup
      class="space-y-1"
      :label="
        $t('core::dates.range') + ' (' + $t('core::app.creation_date') + ')'
      "
    >
      <IFormRadio
        v-for="period in periods"
        :key="period.text"
        v-model="form.period"
        :value="period.value"
        name="period"
        :id="period.id"
        :label="period.text"
      />
    </IFormGroup>
    <IFormGroup v-if="isCustomOptionSelected">
      <div class="sm:ml-6">
        <IFormLabel
          label="Select Range"
          for="custom-period-start"
          class="mb-1"
        />

        <DateRangePicker
          v-model="form.customPeriod"
          name="custom-period"
          id="custom-period"
        />
      </div>
    </IFormGroup>
    <div
      v-show="canUseFilterForExport"
      class="mt-5 rounded-md border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-500 dark:bg-neutral-700"
    >
      <IFormCheckbox
        v-model:checked="shouldApplyFilters"
        :label="$t('core::app.export.apply_filters')"
      />
    </div>
  </IModal>
</template>
<script setup>
import { ref, computed } from 'vue'
import { useDates } from '~/Core/resources/js/composables/useDates'
import FileDownload from 'js-file-download'
import { useI18n } from 'vue-i18n'
import { useQueryBuilder } from '~/Core/resources/js/components/QueryBuilder/useQueryBuilder'
import { useForm } from '~/Core/resources/js/composables/useForm'

const props = defineProps({
  resourceName: String,
  filtersView: String,
  title: String,
  urlPath: { required: true, type: String },
  modalId: { default: 'export-modal', type: String },
})

const { t } = useI18n()
const { appMoment } = useDates()

const { form } = useForm({
  period: 'last_7_days',
  type: 'csv',
  customPeriod: {
    start: appMoment().startOf('month').format('YYYY-MM-DD'),
    end: appMoment().format('YYYY-MM-DD'),
  },
})

const periods = [
  {
    text: t('core::dates.today'),
    value: 'today',
  },
  {
    text: t('core::dates.periods.7_days'),
    value: 'last_7_days',
  },
  {
    text: t('core::dates.this_month'),
    value: 'this_month',
  },
  {
    text: t('core::dates.last_month'),
    value: 'last_month',
  },
  {
    text: t('core::app.all'),
    value: 'all',
    id: 'all',
  },
  {
    text: t('core::dates.custom'),
    value: 'custom',
    id: 'custom',
  },
]

const {
  queryBuilderRules,
  hasRulesApplied,
  rulesAreValid: hasValidFilterRules,
} = useQueryBuilder(props.resourceName, props.filtersView)

const shouldApplyFilters = ref(true)

const exportInProgress = ref(false)

const computedTitle = computed(
  () => props.title || t('core::app.export.export')
)

const isCustomOptionSelected = computed(() => form.period === 'custom')

const canUseFilterForExport = computed(
  () => hasRulesApplied.value && hasValidFilterRules.value
)

function getFileNameFromResponseHeaders(response) {
  return response.headers['content-disposition'].split('filename=')[1]
}

function performExport() {
  exportInProgress.value = true

  Innoclapps.request()
    .post(
      props.urlPath,
      {
        period: !isCustomOptionSelected.value
          ? form.period === 'all'
            ? null
            : form.period
          : form.customPeriod,
        type: form.type,
        filters:
          shouldApplyFilters.value && canUseFilterForExport.value
            ? queryBuilderRules.value
            : null,
      },
      {
        responseType: 'blob',
      }
    )
    .then(response => {
      FileDownload(response.data, getFileNameFromResponseHeaders(response))
    })
    .finally(() => (exportInProgress.value = false))
}
</script>
