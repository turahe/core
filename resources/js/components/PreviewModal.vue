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
  <ISlideover
    @hidden="resetPreview"
    @submit="update"
    v-model:visible="isModalVisible"
    :title="record.display_name"
    :description="titleDescription"
    :initial-focus="modalCloseElement"
    static-backdrop
    form
    id="previewModal"
  >
    <FieldsPlaceholder v-if="!componentReady" />

    <component
      v-if="componentReady"
      :is="`${resourceSingular}Preview`"
      :record="record"
      :form="recordForm"
      :fields="fields"
      :has-collapsable-fields="hasCollapsableFields"
      :total-collapsable-fields="totalCollapsableFields"
    />

    <template #modal-footer>
      <div class="flex justify-end space-x-2">
        <IButton
          v-show="!isPreviewingSameResourceAsViaResource"
          variant="white"
          @click="view"
          :disabled="!componentReady"
          :text="$t('core::app.view_record')"
        />
        <IButton
          type="submit"
          variant="primary"
          :loading="recordForm.busy"
          :text="$t('core::app.save')"
          :disabled="
            !componentReady ||
            recordForm.busy ||
            (record.authorizations && !record.authorizations.update)
          "
        />
      </div>
    </template>
  </ISlideover>
</template>
<script setup>
import {
  ref,
  computed,
  nextTick,
  onMounted,
  onBeforeUnmount,
  watch,
  provide,
} from 'vue'
import { useStore } from 'vuex'
import { windowState } from '@/utils'
import { useI18n } from 'vue-i18n'
import { useResourceUpdate } from '~/Core/resources/js/composables/useResourceUpdate'
import { useResource } from '~/Core/resources/js/composables/useResource'
import { useRecordStore } from '~/Core/resources/js/composables/useRecordStore'
import { useRouter, useRoute } from 'vue-router'
import { computedWithControl } from '@vueuse/shared'

// Emits are not used anywhere ATM
const emit = defineEmits(['updated', 'action-executed'])

const props = defineProps({
  viaResource: String,
})

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const store = useStore()

const isModalVisible = ref(false)
const viaHistory = ref(false)
const titleDescription = ref(null)
const resourceReadyForPreview = ref(false)

let unregisterRouterGuard = null

// Provide initial focus element as the modal can be nested and it's not
// finding an element for some reason when the second modal is closed
// showing error "There are no focusable elements inside the <FocusTrap />"
const modalCloseElement = computedWithControl(
  () => null,
  () => document.querySelector('#modalClose-previewModal')
)

const record = computed(() => store.state.recordPreview.record)

const resourceId = computed(() => store.state.recordPreview.resourceId)

const resourceName = computed(() => store.state.recordPreview.resourceName)

const componentReady = computed(() => {
  if (!resourceReadyForPreview.value) {
    return false
  }

  return isResourceUpdateReady.value && Object.keys(record.value).length > 0
})

const {
  isReady: isResourceUpdateReady,
  form: recordForm,
  fields,
  hasCollapsableFields,
  totalCollapsableFields,
  update,
  // init,
  boot,
} = useResourceUpdate(resourceName)

const { singularName: resourceSingular } = useResource(resourceName)

// via resource
const {
  record: viaResourceRecord,
  setRecord,
  updateResourceRecordHasManyRelationship,
  // removeResourceRecordHasManyRelationship,
} = useRecordStore()

const isPreviewingSameResourceAsViaResource = computed(() => {
  if (!props.viaResource) {
    return false
  }

  return (
    resourceName.value == props.viaResource &&
    viaResourceRecord.value.id === resourceId.value
  )
})

const previewKey = computed(() =>
  String(String(resourceId.value) + String(resourceName.value))
)

function hasRecordForPreview() {
  return Boolean(resourceName.value) && Boolean(resourceId.value)
}

/**
 * Reset the preview store
 */
function resetPreviewStore() {
  store.commit('recordPreview/RESET_RECORD')
  store.commit('recordPreview/RESET_PREVIEW')
}

/**
 * Set the preview modal description
 */
function setDescription(description) {
  titleDescription.value = description
}

/**
 * Set the record for preview in store
 */
function setRecordInStore(record) {
  store.commit('recordPreview/SET_VIA_RESOURCE', props.viaResource)
  store.commit('recordPreview/SET_RECORD', record)
}

/**
 * Get the URL hash for the preview
 */
function locationHashPreview() {
  // Make sure it's valid preview e.q. contacts-12
  let regex = /([a-z]*)-([0-9]*)$/i

  return window.location.hash.match(regex)
}

/**
 * Handle the modal pop state to support
 * back and forward actions for the previewed records
 */
function handlePopState() {
  if (!window.location.hash) {
    hideModal()
    return
  }

  let matches = locationHashPreview()

  if (matches) {
    const [hash, resourceName, resourceId] = matches
    viaHistory.value = true

    store.commit('recordPreview/SET_PREVIEW_RESOURCE', {
      resourceName: resourceName,
      resourceId: Number(resourceId),
    })

    nextTick(() => {
      bootPreview()
      viaHistory.value = false
    })
  }
}

/**
 * Handle the on window load event
 * The preview modal won't work when refreshing the page
 * In this case, if the page is refreshed, do not load the preview resource
 * which was viewed before refresh the page
 *
 * In order to achieve this we need to clear the hash
 */
function handleWindowLoad() {
  clearPreviewHash(route.fullPath)
}

/**
 * Clear the preview hash
 */
function clearPreviewHash(path) {
  windowState.clearHash(path.split('#')[0])
}

/**
 * Hide the preview modal
 */
function hideModal() {
  isModalVisible.value = false
}

/**
 * Fetch the record and set in store
 */
function fetchRecordAndSetInStore() {
  return new Promise((resolve, reject) => {
    Innoclapps.request()
      .get(`${resourceName.value}/${resourceId.value}`)
      .then(({ data }) => {
        setRecordInStore(data)
        resolve(data)
      })
      .catch(error => reject(error))
  })
}

/**
 * Boot the resource preview
 */
function bootPreview() {
  if (!hasRecordForPreview()) {
    return
  }

  removeRecordUpdatedEvent()

  // Don't push to state if it's via history
  // as it will produce more states, keep only the previews
  // the users clicked on
  if (!viaHistory.value) {
    windowState.push('#' + resourceName.value + '-' + resourceId.value)
  }

  isModalVisible.value = true

  addRecordUpdatedEvent()

  // The fields and the form must be resetted each time a new preview record is initialized
  recordForm.clear()
  fields.value.set([])

  fetchRecordAndSetInStore().then(record => {
    resourceReadyForPreview.value = true

    boot(record, {
      id: resourceId.value,
      onAfterUpdate: record => {
        setRecordInStore(record)

        Innoclapps.success(t('core::resource.updated'))
      },
    })
  })
}

/**
 * Helper method to navigate to the actual record full view/update
 * The method uses the current already fetched record from database and passes as meta
 * This helps not making the same request again to the server
 *
 * @return {Void}
 */
function view() {
  router[resourceSingular.value] = record.value

  router.replace({
    name: 'view-' + resourceSingular.value,
    hash: '',
    params: {
      id: record.value.id,
    },
  })

  // Hide the modal in case trying to view the same
  // resource type via resource e.q. child company so it can show
  // the new company directly
  hideModal()
}

function handleRecordUpdated(record) {
  if (!props.viaResource) {
    return
  }

  // Update the actual resource main record
  if (isPreviewingSameResourceAsViaResource.value) {
    emit('updated', record)

    setRecord(record)
  } else {
    updateResourceRecordHasManyRelationship(record, resourceName.value)
  }
}

/**
 * Reset the preview store so it can catch changes on next clicks
 */
function resetPreview() {
  fields.value.set([])
  resourceReadyForPreview.value = false
  removeRecordUpdatedEvent()
  windowState.clearHash()
  resetPreviewStore()
  titleDescription.value = null
}

/**
 * Add the global record updated event
 */
function addRecordUpdatedEvent() {
  Innoclapps.$on(`${resourceName.value}-record-updated`, handleRecordUpdated)
}

/**
 * Remove the global record updated event
 */
function removeRecordUpdatedEvent() {
  Innoclapps.$off(`${resourceName.value}-record-updated`, handleRecordUpdated)
}

window.addEventListener('popstate', handlePopState)
window.addEventListener('load', handleWindowLoad)

unregisterRouterGuard = router.beforeEach((to, from, next) => {
  // When navigating to the same resource different ID via the preview modal,
  // we will hide the modal so the new resource record is visible
  if (to.matched[0].path === from.matched[0].path && !to.hash) {
    hideModal()
  }

  // Check to.hash, if there is no hash, it's not modal history matching
  if (isModalVisible.value && !to.hash) {
    // Make sure that there is no state/hash on back when user is navigated outside of the modal routes
    // This ensures after navigation, when clicking back, it goes
    // to the original preview page without the hashes
    clearPreviewHash(from.fullPath)
  }

  next()
})

onMounted(() => {
  watch(
    previewKey,
    () => {
      resourceReadyForPreview.value = false

      if (!viaHistory.value) {
        nextTick(bootPreview)
      }
    },
    {
      immediate: true,
    }
  )
})

onBeforeUnmount(() => {
  window.removeEventListener('popstate', handlePopState)
  window.removeEventListener('load', handleWindowLoad)
  unregisterRouterGuard()
  resetPreview()
})

provide('setDescription', setDescription)
provide('refreshPreviewRecord', fetchRecordAndSetInStore)

// function actionExecuted(action) {
//   // Reload the record data on any action executed except delete
//   // If we try to reload on delete will throw 404 error
//   if (!action.destroyable) {
//     init(handleRecordUpdated)
//   } else if (!props.viaResource) {
//     // When no viaResource is passed, just hide the modal
//     // and leave the parent company to handle any updates
//     hideModal()
//   } else {
//     // Is previewing the same resource passed viaResource prop,
//     // In this case, redirect to the resource index named route
//     if (isPreviewingSameResourceAsViaResource.value) {
//       router.push({
//         name: resourceSingular.value + '-index',
//       })
//     } else {
//       // Deleted a record which was previewed
//       hideModal()
//       // In case viaResource is paseed, remove the resource
//       // relation too, this should be always true (if(props.viaResource))
//       if (props.viaResource) {
//        removeResourceRecordHasManyRelationship(
//           resourceId.value,
//           resourceName.value
//         )
//       }
//     }
//   }
// }
</script>
