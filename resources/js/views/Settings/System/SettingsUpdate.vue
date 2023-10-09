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
  <ICard class="mb-3">
    <div class="my-1 flex flex-col sm:flex-row sm:items-center">
      <IFormLabel for="purchase-key" class="mb-1 shrink-0 sm:mb-0 sm:mr-4">
        {{ $t('core::app.purchase_key') }}
      </IFormLabel>
      <div class="flex grow">
        <div class="relative grow focus-within:z-10">
          <input
            type="text"
            id="purchase-key"
            :placeholder="$t('core::app.enter_purchase_key')"
            v-model="updateData.purchase_key"
            :class="[
              'form-input rounded-l-md border-neutral-300 dark:bg-neutral-800 dark:text-white',
              componentReady && !hasValidPurchaseKey
                ? 'text-danger-900 placeholder-danger-400 focus:border-danger-500 focus:ring-danger-500'
                : 'dark:border-neutral-500',
            ]"
          />
        </div>

        <IButton
          variant="white"
          :rounded="false"
          :text="$t('core::app.save')"
          @click="savePurchaseKey"
          class="relative -ml-px rounded-r-md"
        />
      </div>
    </div>
  </ICard>
  <IOverlay :show="!passesZipRequirement && componentReady">
    <template #overlay v-if="!passesZipRequirement">
      {{ $t('core::update.update_zip_is_required') }}
    </template>

    <ICard :header="$t('core::update.system')" class="mb-3">
      <IOverlay :show="!componentReady">
        <div v-if="updateData.is_new_version_available">
          <div
            class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0"
          >
            <div
              class="flex-1 rounded bg-warning-100 p-2 px-2 py-3 text-center text-warning-700"
            >
              <h4 class="font-medium" v-t="'core::update.installed_version'" />
              <h5 v-text="updateData.installed_version"></h5>
            </div>
            <div
              class="flex-1 rounded bg-success-100 p-2 px-2 py-3 text-center text-success-700"
            >
              <h4 class="font-medium" v-t="'core::update.latest_version'" />
              <h5 v-text="updateData.latest_available_version"></h5>
            </div>
          </div>
        </div>
        <div v-else>
          <h4
            class="text-center text-lg font-semibold text-neutral-800 dark:text-neutral-100"
            v-show="componentReady"
          >
            <Icon
              icon="EmojiHappy"
              class="m-auto mb-2 h-10 w-10 text-success-500"
            />
            {{ $t('core::update.not_available') }}
          </h4>
          <p
            v-show="componentReady"
            class="text-center text-sm text-neutral-600 dark:text-neutral-300"
            v-t="'core::update.using_latest_version'"
          />
        </div>
      </IOverlay>
      <template #footer>
        <div class="flex justify-end">
          <IButton
            @click="update"
            variant="success"
            :text="updateButtonText"
            :disabled="
              !canPerformUpdate ||
              updateInProgress ||
              patchBeingApplied !== false
            "
          />
        </div>
      </template>
    </ICard>
  </IOverlay>
  <IOverlay :show="!passesZipRequirement && componentReady">
    <template #overlay v-if="!passesZipRequirement">
      {{ $t('core::update.patch_zip_is_required') }}
    </template>

    <ICard :header="$t('core::update.patches')" no-body>
      <IOverlay :show="!componentReady">
        <ul
          v-if="hasPatches"
          class="divide-y divide-neutral-200 dark:divide-neutral-700"
        >
          <li
            v-for="(patch, index) in sortedPatches"
            :key="patch.token"
            class="px-4 py-4 sm:px-6"
          >
            <div class="flex items-center justify-between">
              <div>
                <p
                  v-html="patch.description"
                  class="text-sm font-medium text-neutral-800 dark:text-neutral-100"
                />
                <IBadge
                  v-if="patch.isApplied"
                  wrapper-class="mr-1"
                  variant="success"
                  :text="$t('core::update.patch_applied')"
                />
                <IBadge variant="neutral" :text="patch.token" />
                <small class="ml-2.5 text-neutral-500 dark:text-neutral-300">
                  {{ localizedDateTime(patch.date) }}
                </small>
                <br />
              </div>
              <div class="flex">
                <a
                  :href="
                    '/patches/' + patch.token + '/' + updateData.purchase_key
                  "
                  class="link mr-3 mt-2"
                  :class="{
                    'pointer-events-none opacity-70':
                      index > 0 ||
                      patchBeingApplied !== false ||
                      !hasValidPurchaseKey ||
                      !passesZipRequirement ||
                      updateInProgress ||
                      patch.isApplied,
                  }"
                >
                  <Icon icon="DocumentDownload" class="h-5 w-5" />
                </a>
                <span
                  class="inline-block"
                  tabindex="-1"
                  v-i-tooltip="
                    index === 0 || patch.isApplied
                      ? null
                      : $t('core::update.apply_oldest_first')
                  "
                >
                  <IButton
                    size="sm"
                    :disabled="
                      index > 0 ||
                      patchBeingApplied !== false ||
                      !hasValidPurchaseKey ||
                      !passesZipRequirement ||
                      updateInProgress ||
                      patch.isApplied
                    "
                    @click="applyPatch(patch.token, index)"
                  >
                    {{
                      patchBeingApplied === index
                        ? $t('core::update.update_in_progress')
                        : $t('core::app.apply')
                    }}
                  </IButton>
                </span>
              </div>
            </div>
          </li>
        </ul>

        <ICardBody v-else>
          <p
            class="text-center text-sm text-neutral-500 dark:text-neutral-300"
            v-show="componentReady"
            v-t="'core::update.no_patches'"
          />
        </ICardBody>
      </IOverlay>
    </ICard>
  </IOverlay>
</template>
<script setup>
import { ref, computed } from 'vue'
import { useSettings } from './../useSettings'
import orderBy from 'lodash/orderBy'
import { isPurchaseKey } from '@/utils'
import { useI18n } from 'vue-i18n'
import { useDates } from '~/Core/resources/js/composables/useDates'

const { t } = useI18n()
const { form, submit } = useSettings()
const { localizedDateTime } = useDates()

const passesZipRequirement = Innoclapps.config('requirements.zip')
const updateData = ref({})
const patches = ref([])
const updateInProgress = ref(false)
const patchBeingApplied = ref(false)
const componentReady = ref(false)

const sortedPatches = computed(() =>
  orderBy(
    patches.value.map(patch => {
      // For date sorting
      patch._date = new Date(patch.date)

      return patch
    }),
    ['isApplied', '_date'],
    ['asc', 'asc']
  )
)

const hasPatches = computed(() => patches.value.length > 0)

const updateButtonText = computed(() =>
  updateInProgress.value
    ? t('core::update.update_in_progress')
    : t('core::update.perform')
)

const hasValidPurchaseKey = computed(() =>
  isPurchaseKey(updateData.value.purchase_key)
)

const canPerformUpdate = computed(
  () =>
    updateData.value.is_new_version_available &&
    hasValidPurchaseKey.value &&
    passesZipRequirement
)

function savePurchaseKey() {
  form.purchase_key = updateData.value.purchase_key
  submit()
}

function handleUpdateErrorResponse(response) {
  if (response.data === 'Incorrect files permissions.') {
    window.location.href = '/errors/permissions'
  } else {
    Innoclapps.error(response.data)
  }
}

function update() {
  updateInProgress.value = true
  Innoclapps.request()
    .post(`/update/${updateData.value.purchase_key}`)
    .then(() => window.location.reload())
    .catch(({ response }) => handleUpdateErrorResponse(response))
    .finally(() => (updateInProgress.value = false))
}

function applyPatch(token, index) {
  patchBeingApplied.value = index

  Innoclapps.request()
    .post(`/patches/${token}/${updateData.value.purchase_key}`)
    .then(() => window.location.reload())
    .catch(({ response }) => handleUpdateErrorResponse(response))
    .finally(() => (patchBeingApplied.value = false))
}

function prepareComponent() {
  Promise.all([
    Innoclapps.request().get('/update'),
    Innoclapps.request().get('/patches'),
  ])
    .then(values => {
      updateData.value = values[0].data
      patches.value = values[1].data
    })
    .catch(({ response }) => handleUpdateErrorResponse(response))
    .finally(() => (componentReady.value = true))
}

prepareComponent()
</script>
