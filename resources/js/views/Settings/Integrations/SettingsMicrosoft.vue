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
  <form
    @submit.prevent="submitMicrosoftIntegrationSettings"
    @input="form.errors.clear($event.target.name)"
  >
    <ICard header="Microsoft" :overlay="!componentReady">
      <template #header>
        <div class="flex items-center">
          <Icon
            :icon="
              maybeClientSecretIsExpired ? 'XCircleSolid' : 'CheckCircleSolid'
            "
            :class="[
              'mr-1 h-5 w-5',
              {
                'text-success-600':
                  !maybeClientSecretIsExpired && isConfigured && componentReady,
                'text-danger-500':
                  maybeClientSecretIsExpired && isConfigured && componentReady,
              },
            ]"
            v-if="isConfigured && componentReady"
          />
          <ICardHeading>Microsoft</ICardHeading>
        </div>
      </template>
      <template #actions>
        <a
          href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade"
          class="link inline-flex items-center text-sm"
          target="_blank"
          rel="noopener noreferrer"
        >
          App Registrations <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
        </a>
      </template>
      <div
        class="mb-6 flex justify-between rounded-md border border-neutral-200 bg-neutral-50 px-3 py-2 dark:border-neutral-600 dark:bg-neutral-800"
      >
        <div class="text-sm">
          <span class="mr-2 font-medium text-neutral-700 dark:text-neutral-200">
            Redirect Url:
          </span>
          <span
            class="select-all text-neutral-600 dark:text-white"
            v-text="redirectUri"
          />
        </div>
        <IButtonCopy
          class="ml-3"
          :text="redirectUri"
          :success-message="$t('core::app.copied')"
          v-i-tooltip="$t('core::app.copy')"
        />
      </div>
      <!-- <div
        class="mb-6 flex justify-between rounded-md border border-neutral-200 bg-neutral-50 px-3 py-2 dark:border-neutral-600 dark:bg-neutral-800"
      >
        <div class="text-sm">
          <span class="mr-2 font-medium text-neutral-700 dark:text-neutral-200">Logout Url:</span>
          <span class="select-all text-neutral-600 dark:text-white" v-text="logoutUrl"></span>
        </div>
        <IButtonCopy class="ml-3" :text="logoutUrl" :success-message="$t('core::app.copied')" v-i-tooltip="$t('core::app.copy')" />
      </div> -->
      <div class="sm:flex sm:space-x-4">
        <IFormGroup
          class="w-full"
          label="App (client) ID"
          label-for="msgraph_client_id"
        >
          <IFormInput
            autocomplete="off"
            v-model="form.msgraph_client_id"
            id="msgraph_client_id"
            name="msgraph_client_id"
          />
        </IFormGroup>
        <IFormGroup
          class="w-full"
          label="Client Secret"
          label-for="msgraph_client_secret"
        >
          <IFormInput
            autocomplete="off"
            type="password"
            v-model="form.msgraph_client_secret"
            id="msgraph_client_secret"
            name="msgraph_client_secret"
          />
        </IFormGroup>
      </div>

      <IAlert
        class="mt-4"
        v-if="
          originalSettings.msgraph_client_secret &&
          originalSettings.msgraph_client_secret_configured_at &&
          !maybeClientSecretIsExpired
        "
        variant="info"
      >
        The client secret was configured at
        {{
          localizedDate(originalSettings.msgraph_client_secret_configured_at)
        }}. If you followed the documentation and configured the client secret
        to expire in 24 months,
        <span class="font-bold">
          you must re-generate a new client secret at:
          {{ getClientSecretExpiresMoment().format('YYYY-MM-DD') }}
        </span>
        in order the integration to continue working.
        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <IButtonMinimal
              variant="info"
              target="_blank"
              rel="noopener noreferrer"
              tag="a"
              :href="
                'https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/Credentials/appId/' +
                form.msgraph_client_id +
                '/isMSAApp/true'
              "
            >
              Re-Generate
            </IButtonMinimal>
          </div>
        </div>
      </IAlert>
      <IAlert class="mt-4" :show="maybeClientSecretIsExpired" variant="danger">
        The client secret is probably expired, click the button below to
        re-generate new secret if it's needed, don't forget to update the secret
        here in the integration as well.
        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <IButtonMinimal
              variant="danger"
              tag="a"
              target="_blank"
              rel="noopener noreferrer"
              :href="
                'https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/Credentials/appId/' +
                form.msgraph_client_id +
                '/isMSAApp/true'
              "
            >
              Re-Generate
            </IButtonMinimal>
          </div>
        </div>
      </IAlert>
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
import { computed, nextTick } from 'vue'
import { useSettings } from './../useSettings'
import { useDates } from '~/Core/resources/js/composables/useDates'

const { localizedDate, appMoment, appDate } = useDates()

const {
  form,
  submit,
  isReady: componentReady,
  originalSettings,
} = useSettings()

const redirectUri = Innoclapps.config('url') + '/microsoft/callback'
// const logoutUrl = Innoclapps.config('url') + '/microsoft/logout'

const isConfigured = computed(
  () =>
    originalSettings.value.msgraph_client_secret &&
    originalSettings.value.msgraph_client_id
)
const maybeClientSecretIsExpired = computed(() => {
  if (
    !originalSettings.value.msgraph_client_secret ||
    !originalSettings.value.msgraph_client_secret_configured_at
  ) {
    return false
  }

  return getClientSecretExpiresMoment().isBefore(appMoment())
})

/**
 * We can only fetch the secret expires date using the servicePrincipal endpoint
 * however, this endpoint required work account and as we cannot force all users
 * to configure work account, we will assume that they follow the docs and add the
 * token to expire in 24 months, based on the configuration date, we will track the expiration of the token
 * @see https://docs.microsoft.com/en-us/graph/api/serviceprincipal-list?view=graph-rest-1.0&tabs=http#permissions
 */
function getClientSecretExpiresMoment() {
  return (
    appMoment(originalSettings.value.msgraph_client_secret_configured_at)
      .add(24, 'months')
      // Subtract 1 day to avoid integration interruptions when the secret must be renewed at the same day
      .subtract(1, 'day')
  )
}

function submitMicrosoftIntegrationSettings() {
  if (
    form.msgraph_client_secret &&
    originalSettings.value.msgraph_client_secret != form.msgraph_client_secret
  ) {
    form.fill('msgraph_client_secret_configured_at', appDate())
  } else if (!form.msgraph_client_secret) {
    form.fill('msgraph_client_secret_configured_at', null)
  }

  nextTick(() => submit(form => window.location.reload()))
}
</script>
