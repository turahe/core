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
  <form @submit.prevent="saveGeneralSettings" @keydown="form.onKeydown($event)">
    <!-- General settings -->
    <ICard
      :header="$t('core::settings.general')"
      class="mb-3"
      :overlay="!componentReady"
    >
      <p
        class="mb-3 text-sm font-medium text-neutral-700 dark:text-neutral-200"
        v-t="'core::app.logo.dark'"
      />
      <CropsAndUploadsImage
        name="logo_dark"
        :upload-url="$store.state.apiURL + '/logo/dark'"
        :image="currentDarkLogo"
        :show-delete="Boolean(form.logo_dark)"
        :cropper-options="{ aspectRatio: null }"
        :choose-text="
          !currentDarkLogo
            ? $t('core::settings.choose_logo')
            : $t('core::app.change')
        "
        @cleared="deleteLogo('dark')"
        @success="refreshPage"
      >
        <template #image="{ src }">
          <img :src="src" class="h-8 w-auto" />
        </template>
      </CropsAndUploadsImage>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />
      <p
        class="mb-3 text-sm font-medium text-neutral-700 dark:text-neutral-200"
        v-t="'core::app.logo.light'"
      />
      <CropsAndUploadsImage
        name="logo_light"
        :show-delete="Boolean(form.logo_light)"
        :upload-url="$store.state.apiURL + '/logo/light'"
        :image="currentLightLogo"
        :cropper-options="{ aspectRatio: null }"
        :choose-text="
          !currentLightLogo
            ? $t('core::settings.choose_logo')
            : $t('core::app.change')
        "
        @cleared="deleteLogo('light')"
        @success="refreshPage"
      >
        <template #image="{ src }">
          <img :src="src" class="h-8 w-auto" />
        </template>
      </CropsAndUploadsImage>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <IFormGroup
        :label="$t('core::app.currency')"
        label-for="currency"
        class="w-auto xl:w-1/3"
      >
        <ICustomSelect
          input-id="currency"
          v-model="form.currency"
          :clearable="false"
          :options="currencies"
        >
        </ICustomSelect>
        <IFormError v-text="form.getError('currency')" />
      </IFormGroup>
      <IFormGroup
        :label="$t('core::settings.system_email')"
        label-for="system_email_account_id"
      >
        <div class="w-auto xl:w-1/3">
          <ICustomSelect
            input-id="system_email_account_id"
            :placeholder="
              !systemEmailAccountIsVisibleToCurrentUser &&
              systemEmailAccountIsConfiguredFromOtherUser
                ? $t('core::settings.system_email_configured')
                : ''
            "
            :modelValue="systemEmailAccount"
            :disabled="
              !systemEmailAccountIsVisibleToCurrentUser &&
              systemEmailAccountIsConfiguredFromOtherUser
            "
            :options="emailAccounts"
            label="email"
            @option:selected="form.system_email_account_id = $event.id"
            @cleared="form.system_email_account_id = null"
          />
        </div>
        <IFormText
          v-text="$t('core::settings.system_email_info')"
          class="mt-2 max-w-3xl"
        />
        <IFormError v-text="form.getError('system_email_account_id')" />
      </IFormGroup>

      <IFormGroup
        :label="$t('core::app.allowed_extensions')"
        :description="$t('core::app.allowed_extensions_info')"
      >
        <IFormTextarea
          rows="2"
          v-model="form.allowed_extensions"
          id="allowed_extensions"
        />
        <IFormError v-text="form.getError('allowed_extensions')" />
      </IFormGroup>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
        <li class="py-4">
          <div
            class="space-x-0 space-y-3 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
          >
            <div>
              <h5
                class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
                v-t="'core::settings.phones.require_calling_prefix'"
              />
              <p
                class="break-words text-sm text-neutral-600 dark:text-neutral-300"
                v-t="'core::settings.phones.require_calling_prefix_info'"
              />
            </div>
            <div>
              <IFormToggle
                :value="true"
                :unchecked-value="false"
                v-model="form.require_calling_prefix_on_phones"
              />
            </div>
          </div>
        </li>
      </ul>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <div class="my-4 block">
        <IAlert class="mb-5">
          {{ $t('core::settings.update_user_account_info') }}
        </IAlert>
        <LocalizationFields
          class="w-auto xl:w-1/3"
          :exclude="['timezone', 'locale']"
          @update:firstDayOfWeek="form.first_day_of_week = $event"
          @update:timeFormat="form.time_format = $event"
          @update:dateFormat="form.date_format = $event"
          :form="form"
        />
      </div>
      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>

    <!-- Company information -->
    <ICard
      :header="$t('core::settings.company_information')"
      class="mb-3"
      :overlay="!componentReady"
    >
      <IFormGroup
        class="w-auto xl:w-1/3"
        :label="$t('core::app.company.name')"
        label-for="company_name"
      >
        <IFormInput v-model="form.company_name" id="company_name" />
      </IFormGroup>

      <IFormGroup
        class="w-auto xl:w-1/3"
        :label="$t('core::app.company.country')"
        label-for="company_country_id"
      >
        <ICustomSelect
          v-model="country"
          :options="countries"
          label="name"
          @option:selected="form.company_country_id = $event.id"
          @cleared="form.company_country_id = null"
          input-id="company_country_id"
        />
      </IFormGroup>

      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>
    <ICard :header="$t('core::app.privacy_policy')" :overlay="!componentReady">
      <Editor v-model="form.privacy_policy" />
      <IFormText
        tabindex="-1"
        class="mt-2"
        v-t="{
          path: 'core::settings.privacy_policy_info',
          args: { url: privacyPolicyUrl },
        }"
      />
      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>
  </form>
</template>
<script setup>
import { shallowRef, computed } from 'vue'
import CropsAndUploadsImage from '~/Core/resources/js/components/CropsAndUploadsImage.vue'
import LocalizationFields from './LocalizationFields.vue'
import { useSettings } from './useSettings'
import find from 'lodash/find'
import map from 'lodash/map'
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'

const store = useStore()
const { setting, resetStoreState } = useApp()

const {
  form,
  submit,
  isReady: componentReady,
  originalSettings,
} = useSettings()

const privacyPolicyUrl = Innoclapps.config('privacyPolicyUrl')
const currencies = shallowRef([])
const countries = shallowRef([])
const country = shallowRef(null)

const emailAccounts = computed(() => store.getters['emailAccounts/accounts'])

const currentLightLogo = computed(() => setting('logo_light'))

const currentDarkLogo = computed(() => setting('logo_dark'))

const systemEmailAccount = computed(() =>
  find(emailAccounts.value, ['id', Number(form.system_email_account_id)])
)

const originalSystemEmailAccount = computed(() =>
  find(emailAccounts.value, [
    'id',
    Number(originalSettings.value.system_email_account_id),
  ])
)

const systemEmailAccountIsVisibleToCurrentUser = computed(
  () =>
    originalSettings.value.system_email_account_id &&
    originalSystemEmailAccount.value
)

const systemEmailAccountIsConfiguredFromOtherUser = computed(() => {
  // If the account cannot be found in the accounts list, this means the account is not visible
  // to the current logged-in user
  return (
    originalSettings.value.system_email_account_id &&
    !originalSystemEmailAccount.value
  )
})

function saveGeneralSettings() {
  submit(() => {
    if (
      form.require_calling_prefix_on_phones !==
      originalSettings.value.require_calling_prefix_on_phones
    ) {
      resetStoreState()
    }

    if (form.currency !== originalSettings.value.currency) {
      // Reload the page as the original currency is stored is in Innoclapps.config object
      refreshPage()
    }
  })
}

function refreshPage() {
  window.location.reload()
}

function deleteLogo(type) {
  const optionName = 'logo_' + type

  if (form[optionName]) {
    Innoclapps.request().delete(`/logo/${type}`).then(refreshPage)
  }
}

function fetchAndSetCurrencies() {
  Innoclapps.request()
    .get('currencies')
    .then(({ data }) => {
      currencies.value = map(data, (val, code) => code)
    })
}

function fetchAndSetCountries() {
  Innoclapps.request()
    .get('countries')
    .then(({ data }) => {
      countries.value = data

      if (form.company_country_id) {
        country.value = find(countries.value, [
          'id',
          Number(form.company_country_id),
        ])
      }
    })
}

store.dispatch('emailAccounts/fetch')
fetchAndSetCurrencies()
fetchAndSetCountries()
</script>
