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
  <ICard no-body>
    <template #header>
      <slot name="header">
        <ICardHeading>
          {{ header || $t('core::import.import_records') }}
        </ICardHeading>
      </slot>
    </template>
    <template #actions>
      <IButton
        variant="white"
        @click="downloadSample(`/${resourceName}/import/sample`)"
        size="sm"
        :text="$t('core::import.download_sample')"
      />
    </template>

    <ImportSteps :steps="steps" />

    <ICardBody>
      <IAlert
        v-if="rowsExceededMessage"
        dismissible
        variant="danger"
        class="mb-5"
      >
        {{ rowsExceededMessage }}
      </IAlert>

      <MediaUpload
        class="ml-5"
        :action-url="uploadUrl"
        extensions="csv"
        @file-uploaded="handleFileUploaded"
        :multiple="false"
        :show-output="false"
        :upload-text="$t('core::import.start')"
      />

      <div class="mt-5 text-sm" v-if="importInstance">
        <h3
          class="mb-3 text-lg font-medium text-neutral-700 dark:text-neutral-200"
          v-t="'core::import.spreadsheet_columns'"
        />
        <div class="flex">
          <div class="w-1/2">
            <div
              v-for="(column, index) in importInstance.mappings"
              :key="'mapping-' + index"
              :class="[
                {
                  'bg-neutral-100 dark:bg-neutral-800': !column.attribute,
                  'bg-white dark:bg-neutral-700': column.attribute,
                },
                'mb-2 mr-3 flex h-16 flex-col justify-center rounded border border-neutral-300 px-4 dark:border-neutral-500',
              ]"
            >
              <IFormLabel :required="isColumnRequired(column)">
                {{ column.original }}
                <span
                  v-if="column.skip && !isColumnRequired(column)"
                  class="text-xs"
                >
                  ({{ $t('core::import.column_will_not_import') }})
                </span>
              </IFormLabel>
              <p
                class="truncate text-neutral-500 dark:text-neutral-300"
                v-text="column.preview"
              />
            </div>
          </div>

          <div class="w-1/2">
            <div
              v-for="(column, index) in importInstance.mappings"
              :key="'field-' + index"
              class="mb-2 flex h-16 items-center"
            >
              <Icon
                icon="ChevronRight"
                class="mr-3 h-5 w-5 text-neutral-800 dark:text-neutral-200"
              />

              <IFormSelect
                :size="false"
                :rounded="false"
                class="h-16 rounded px-4 py-5 hover:border-primary-500"
                v-model="importInstance.mappings[index].attribute"
                @input="importInstance.mappings[index].skip = !$event"
              >
                <option value="" v-if="!isColumnRequired(column)">N/A</option>
                <option
                  v-for="field in importInstance.fields.all()"
                  :key="'field-' + index + '-' + field.attribute"
                  :disabled="isFieldMapped(field.attribute)"
                  :value="field.attribute"
                >
                  {{ field.label }}
                </option>
              </IFormSelect>
            </div>
          </div>
        </div>
      </div>
    </ICardBody>
    <template v-if="importInstance" #footer>
      <div class="flex items-center justify-end space-x-2">
        <IButton
          @click="destroy(importInstance.id)"
          :disabled="importIsInProgress"
          variant="white"
          :text="$t('core::app.cancel')"
        />
        <IButton
          @click="performImport"
          :loading="importIsInProgress"
          :disabled="importIsInProgress"
          :text="
            importIsInProgress
              ? $t('core::app.please_wait')
              : $t('core::import.import')
          "
        />
      </div>
    </template>
  </ICard>
  <ICard
    class="mt-5"
    :overlay="loadingImportHistory"
    header-class="relative overflow-hidden"
    :header="$t('core::import.history')"
    no-body
  >
    <ITable v-if="hasImportHistory" class="-mt-px">
      <thead>
        <tr>
          <th class="text-left" v-t="'core::import.date'"></th>
          <th class="text-left" v-t="'core::import.file_name'"></th>
          <th class="text-left" v-t="'core::import.user'"></th>
          <th class="text-center" v-t="'core::import.total_imported'"></th>
          <th class="text-center" v-t="'core::import.total_duplicates'"></th>
          <th class="text-center" v-t="'core::import.total_skipped'"></th>
          <th class="text-center" v-t="'core::import.status'"></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <template v-for="history in computedImports" :key="history.id">
          <tr>
            <td class="text-left">
              {{ localizedDateTime(history.created_at) }}
            </td>
            <td class="text-left">{{ history.file_name }}</td>
            <td class="text-left">{{ history.user.name }}</td>
            <td class="text-center">{{ history.imported }}</td>
            <td class="text-center">{{ history.duplicates }}</td>
            <td class="text-center">
              {{ history.skipped }}
              <span
                v-if="
                  history.skip_file_filename &&
                  (history.authorizations.downloadSkipFile ||
                    history.authorizations.uploadFixedSkipFile)
                "
              >
                <a
                  href="#"
                  @click.prevent="
                    showSkipInfoFor === history.id
                      ? (showSkipInfoFor = null)
                      : (showSkipInfoFor = history.id)
                  "
                  class="link text-xs"
                  v-t="'core::import.why_skipped'"
                />
              </span>
            </td>
            <td class="text-center">
              <span v-i-tooltip="history.status" class="inline-block">
                <Icon
                  v-if="history.status === 'mapping'"
                  icon="Bars3CenterLeft"
                  class="h-5 w-5 animate-pulse text-neutral-500 dark:text-neutral-400"
                />
                <Icon
                  v-else-if="history.status === 'finished'"
                  icon="CheckCircle"
                  class="h-5 w-5 text-success-500 dark:text-success-400"
                />
                <Icon
                  v-else-if="history.status === 'in-progress'"
                  icon="DotsHorizontal"
                  class="h-5 w-5 animate-bounce text-neutral-500 dark:text-neutral-400"
                />
              </span>
            </td>
            <td>
              <div class="flex justify-end space-x-2">
                <a
                  v-if="
                    history.status === 'mapping' &&
                    (!importInstance ||
                      (importInstance && importInstance.id != history.id))
                  "
                  href="#"
                  class="link"
                  @click.prevent="continueMapping(history.id)"
                  v-t="'core::app.continue'"
                />
                <IButtonIcon
                  v-if="history.authorizations.delete"
                  icon="Trash"
                  @click="destroy(history.id, true)"
                />
              </div>
            </td>
          </tr>
          <tr
            v-if="
              history.skip_file_filename &&
              showSkipInfoFor === history.id &&
              (history.authorizations.downloadSkipFile ||
                history.authorizations.uploadFixedSkipFile)
            "
          >
            <td colspan="4">
              <h3
                class="text-lg/6 font-medium text-neutral-900 dark:text-white"
              >
                {{ $t('core::import.skip_file') }}
                <span class="text-sm text-neutral-500 dark:text-neutral-300">
                  {{
                    $t('core::import.total_rows_skipped', {
                      count: history.skipped,
                    })
                  }}
                </span>
              </h3>

              <p
                class="mt-3 max-w-4xl font-normal"
                v-t="'core::import.skip_file_generation_info'"
              />

              <p
                class="my-2 max-w-3xl font-normal"
                v-t="'core::import.skip_file_fix_and_continue'"
              />

              <div class="mb-4 mt-8 flex items-center">
                <a
                  v-if="history.authorizations.downloadSkipFile"
                  href="#"
                  @click.prevent="downloadSkipFile(history.id)"
                  class="link mr-4 text-sm"
                  v-t="'core::import.download_skip_file'"
                />

                <media-upload
                  v-if="history.authorizations.uploadFixedSkipFile"
                  @file-uploaded="handleSkipFileUploaded($event)"
                  :action-url="createSkipFileUploadUrl(history)"
                  extensions="csv"
                  name="skip_file"
                  input-id="skip_file"
                  :multiple="false"
                  :show-output="false"
                  :select-file-text="$t('core::import.upload_fixed_skip_file')"
                  :upload-text="$t('core::import.start')"
                />
              </div>
            </td>
            <td colspan="4"></td>
          </tr>
        </template>
      </tbody>
    </ITable>
    <ICardBody v-else v-show="!loadingImportHistory" class="text-center">
      <Icon icon="EmojiSad" class="mx-auto h-12 w-12 text-neutral-400" />
      <h3
        class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
        v-t="'core::import.no_history'"
      />
    </ICardBody>
  </ICard>
</template>
<script setup>
import { ref, computed } from 'vue'
import MediaUpload from '~/Core/resources/js/components/Media/MediaUpload.vue'
import Fields from '~/Core/resources/js/components/Fields/Fields'
import ImportSteps from './ImportSteps.vue'
import orderBy from 'lodash/orderBy'
import findIndex from 'lodash/findIndex'
import find from 'lodash/find'
import FileDownload from 'js-file-download'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useDates } from '~/Core/resources/js/composables/useDates'

const props = defineProps({
  header: String,
  resourceName: { required: true, type: String },

  requestQueryString: {
    type: Object,
    default() {
      return {}
    },
  },
})

const { t } = useI18n()
const store = useStore()
const { resetStoreState } = useApp()
const { localizedDateTime } = useDates()

const imports = ref([])
const usingSkipFile = ref(false)
const showSkipInfoFor = ref(null)
const importInstance = ref(null)
const rowsExceededMessage = ref(null)
const loadingImportHistory = ref(false)
const importIsInProgress = ref(false)

const steps = ref([
  {
    id: '01',
    name: t('core::import.steps.step_1.name'),
    description: t('core::import.steps.step_1.description'),
    status: 'current',
  },
  {
    id: '02',
    name: t('core::import.steps.step_2.name'),
    description: t('core::import.steps.step_2.description'),
    status: 'upcoming',
  },
  {
    id: '03',
    name: t('core::import.steps.step_3.name'),
    description: t('core::import.steps.step_3.description'),
    status: 'upcoming',
  },
  {
    id: '04',
    name: t('core::import.steps.step_4.name'),
    description: t('core::import.steps.step_4.description'),
    status: 'upcoming',
  },
])

/**
 * Get the computed imports ordered by date
 */
const computedImports = computed(() =>
  orderBy(imports.value, 'created_at', 'desc')
)

/**
 * Indicates whether the resource has import history
 */
const hasImportHistory = computed(() => computedImports.value.length > 0)

/**
 * Get the URL for upload
 */
const uploadUrl = computed(() => {
  let url = `${store.state.apiURL}/${props.resourceName}/import/upload`

  return appendQueryString(url)
})

/**
 * Get the URL for import
 */
const importUrl = computed(() => {
  let url = `/${props.resourceName}/import/${importInstance.value.id}`

  return appendQueryString(url)
})

/**
 * Get the URL for uploading a fixed skip file
 */
function createSkipFileUploadUrl(history) {
  let url = `${store.state.apiURL}/${props.resourceName}/import/${history.id}/skip-file`

  return appendQueryString(url)
}

/**
 * Change the current import step to
 */
function changeCurrentStep(id, status) {
  // When changing to "complete" or "current" we will
  // update all other steps below this step to complete
  if (status === 'complete' || status === 'current') {
    let stepsBelowStep = steps.value.filter(
      step => parseInt(step.id) < parseInt(id)
    )
    stepsBelowStep.forEach(step => (step.status = 'complete'))
  }

  if (status === 'current') {
    // When changing to current, all steps above this step will be upcoming
    let stepsAboveStep = steps.value.filter(
      step => parseInt(step.id) > parseInt(id)
    )
    stepsAboveStep.forEach(step => (step.status = 'upcoming'))
  }

  steps.value[findIndex(steps.value, ['id', id])].status = status
}

/**
 * Append query string to the given url
 */
function appendQueryString(url, queryString = {}) {
  if (
    Object.keys(props.requestQueryString).length > 0 ||
    Object.keys(queryString).length > 0
  ) {
    let str = []
    let allQueryString = { ...props.requestQueryString, ...queryString }

    for (var q in allQueryString)
      str.push(
        encodeURIComponent(q) + '=' + encodeURIComponent(allQueryString[q])
      )

    url += '?' + str.join('&')
  }

  return url
}

/**
 * Download skip file for the give import id
 * */
function downloadSkipFile(id) {
  Innoclapps.request()
    .get(`/${props.resourceName}/import/${id}/skip-file`, {
      responseType: 'blob',
    })
    .then(response => {
      FileDownload(
        response.data,
        response.headers['content-disposition'].split('filename=')[1]
      )
    })
}

/**
 * Download sample import file
 */
function downloadSample(route) {
  Innoclapps.request()
    .get(route)
    .then(({ data }) => {
      FileDownload(data, 'sample.csv')
      if (
        steps.value[0].status === 'current' ||
        steps.value[3].status === 'complete'
      ) {
        changeCurrentStep('02', 'current')
      }
    })
}

/**
 * Check whether the field is mapped in a column
 */
function isFieldMapped(attribute) {
  return Boolean(find(importInstance.value.mappings, ['attribute', attribute]))
}

/**
 * Delete the given history
 */
async function destroy(id, force) {
  if (usingSkipFile.value && force !== true) {
    importInstance.value = null
    return
  }

  await Innoclapps.dialog().confirm()
  await Innoclapps.request().delete(`/${props.resourceName}/import/${id}`)

  handleAfterDelete(id)
}

function handleAfterDelete(id) {
  imports.value.splice(findIndex(imports.value, ['id', id]), 1)

  if (importInstance.value && id == importInstance.value.id) {
    importInstance.value = null
    usingSkipFile.value = false
    changeCurrentStep('01', 'current')
  }
}

/**
 * Continue mapping the given import
 */
function continueMapping(id) {
  setImportForMapping(find(imports.value, ['id', Number(id)]))
  changeCurrentStep('03', 'current')
}

/**
 * Set the import instance for mapping
 */
function setImportForMapping(instance) {
  instance.fields = new Fields(instance.fields)
  importInstance.value = instance
}

/**
 * Retrieve the current resource imports
 */
function retrieveImports() {
  loadingImportHistory.value = true

  Innoclapps.request()
    .get(`${props.resourceName}/import`)
    .then(({ data }) => (imports.value = data))
    .finally(() => (loadingImportHistory.value = false))
}

/**
 * Check whether the given import column is required
 */
function isColumnRequired(column) {
  if (
    !column.detected_attribute ||
    !importInstance.value.fields.has(column.detected_attribute)
  ) {
    return false
  }

  return importInstance.value.fields.find(column.detected_attribute).isRequired
}

/**
 * Handle file uploaded
 */
function handleFileUploaded(importInstance) {
  setImportForMapping(importInstance)
  imports.value.push(importInstance)
  changeCurrentStep('03', 'current')
  rowsExceededMessage.value = null
}

/**
 * Handle skip file uploaded
 */
function handleSkipFileUploaded(importInstance) {
  setImportForMapping(importInstance)
  let index = findIndex(imports.value, ['id', Number(importInstance.id)])
  imports.value[index] = importInstance
  changeCurrentStep('03', 'current')
  usingSkipFile.value = true
}

/**
 * Perform the import for the current import instance
 */
function performImport() {
  importIsInProgress.value = true
  Innoclapps.request()
    .post(importUrl.value, {
      mappings: importInstance.value.mappings,
    })
    .then(({ data }) => {
      Innoclapps.success(t('core::import.imported'))
      importInstance.value = null
      let index = findIndex(imports.value, ['id', Number(data.id)])

      if (index !== -1) {
        imports.value[index] = data
      } else {
        imports.value.push(data)
      }

      changeCurrentStep('04', 'complete')

      // In case of any custom options created, reset the
      // store state for the cached fields
      resetStoreState()
    })
    .catch(error => {
      let data = error.response.data
      if (data.deleted || data.rows_exceeded) {
        if (data.deleted) {
          handleAfterDelete(importInstance.value.id)
        }

        if (data.rows_exceeded) {
          rowsExceededMessage.value = error.response.data.message
        }
      } else {
        changeCurrentStep('04', 'current')
      }
    })
    .finally(() => {
      importIsInProgress.value = false
      usingSkipFile.value = false
    })
}

retrieveImports()
</script>
