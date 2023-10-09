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
  <ICard :overlay="!componentReady">
    <template #header>
      <div class="flex items-center">
        <Icon
          :icon="
            numbers.length === 0 ||
            selectedNumberHasNoVoiceCapabilities ||
            !isSecure
              ? 'XCircleSolid'
              : 'CheckCircleSolid'
          "
          :class="[
            'mr-1 h-5 w-5',
            numbers.length === 0 ||
            selectedNumberHasNoVoiceCapabilities ||
            !isSecure
              ? 'text-danger-500'
              : 'text-success-600',
          ]"
          v-if="
            isConfigured && componentReady && !numbersRetrievalRequestInProgress
          "
        />

        <ICardHeading>Twilio</ICardHeading>
        <IStepsCircle class="pointer-events-none ml-4">
          <IStepCircle :status="!showNumberConfig ? 'current' : 'complete'" />
          <IStepCircle :status="form.twilio_number ? 'complete' : ''" />
          <IStepCircle
            :status="isConfigured && form.twilio_number ? 'complete' : ''"
            is-last
          />
        </IStepsCircle>
      </div>
    </template>
    <template #actions>
      <IButton
        v-show="isConfigured"
        variant="danger"
        size="sm"
        :text="$t('core::settings.integrations.twilio.disconnect')"
        @click="disconnect"
      />
    </template>
    <div class="lg:flex lg:space-y-4">
      <div class="w-full">
        <IAlert class="mb-10" v-show="showAppUrlWarning" variant="warning">
          Your Twilio application URL does match your installation URL.
          <div class="mt-4">
            <div class="-mx-2 -my-1.5 flex">
              <button
                type="button"
                @click="updateTwiMLAppURL"
                class="rounded-md bg-warning-50 px-2 py-1.5 text-sm font-medium text-warning-800 focus:outline-none focus:ring-2 focus:ring-warning-600 focus:ring-offset-2 focus:ring-offset-warning-50 hover:bg-warning-100"
              >
                Update URL
              </button>
            </div>
          </div>
        </IAlert>

        <IAlert class="mb-10" :show="!isSecure" variant="warning">
          Application must be served over HTTPS URL in order to use the Twilio
          integration.
        </IAlert>

        <div class="grid grid-cols-12 gap-2 lg:gap-4">
          <div class="col-span-12 lg:col-span-6">
            <IFormGroup>
              <template #label>
                <div class="flex">
                  <div class="grow">
                    <IFormLabel for="twilio_account_sid">
                      Account SID
                    </IFormLabel>
                  </div>
                  <div>
                    <small>
                      <a
                        href="https://www.twilio.com/console"
                        class="link mb-1 inline-flex items-center"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        https://www.twilio.com/console
                        <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
                      </a>
                    </small>
                  </div>
                </div>
              </template>
              <IFormInput
                v-model="form.twilio_account_sid"
                id="twilio_account_sid"
                autocomplete="off"
              />
            </IFormGroup>
          </div>
          <div class="col-span-12 lg:col-span-6">
            <IFormGroup>
              <template #label>
                <div class="flex">
                  <div class="grow">
                    <IFormLabel for="twilio_auth_token">Auth Token</IFormLabel>
                  </div>
                  <div>
                    <small>
                      <a
                        href="https://www.twilio.com/console"
                        class="link mb-1 inline-flex items-center"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        https://www.twilio.com/console
                        <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
                      </a>
                    </small>
                  </div>
                </div>
              </template>
              <IFormInput
                type="password"
                v-model="form.twilio_auth_token"
                id="twilio_auth_token"
                autocomplete="off"
              />
            </IFormGroup>
          </div>
        </div>

        <div
          class="mt-2 border-t border-neutral-200 pt-5 dark:border-neutral-600"
          :class="{
            'pointer-events-none opacity-50 blur-sm': !showNumberConfig,
          }"
        >
          <IFormLabel
            :label="$t('core::settings.integrations.twilio.number')"
          />

          <IAlert
            :show="selectedNumberHasNoVoiceCapabilities"
            class="my-3"
            variant="danger"
          >
            This phone number does not have enabled voice capabilities.
          </IAlert>
          <div class="mt-1 flex rounded-md shadow-sm">
            <div class="relative flex grow items-stretch focus-within:z-10">
              <div
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
              >
                <Icon icon="Phone" class="h-5 w-5 text-neutral-400" />
              </div>
              <IFormSelect
                :rounded="false"
                :disabled="!numbers.length"
                class="rounded-l-md pl-10"
                v-model="form.twilio_number"
              >
                <option value=""></option>
                <option
                  v-for="number in numbers"
                  :key="number.phoneNumber"
                  :value="number.phoneNumber"
                >
                  {{ number.phoneNumber }}
                </option>
              </IFormSelect>
            </div>
            <IButton
              class="relative -ml-px rounded-r-md"
              variant="white"
              :rounded="false"
              :loading="numbersRetrievalRequestInProgress"
              :disabled="numbersRetrievalRequestInProgress"
              @click="retrieveNumbers"
            >
              Retrieve Numbers
            </IButton>
          </div>
        </div>

        <div
          class="mt-5 border-t border-neutral-200 pt-5 dark:border-neutral-600"
          :class="{
            'pointer-events-none opacity-50 blur-sm': !showAppConfig,
          }"
        >
          <IFormLabel :label="$t('core::settings.integrations.twilio.app')" />
          <div class="mt-1 flex rounded-md shadow-sm">
            <div class="relative flex grow items-stretch focus-within:z-10">
              <IFormInput
                v-model="form.twilio_app_sid"
                :disabled="true"
                :rounded="false"
                class="rounded-l-md"
              />
            </div>
            <IButton
              :rounded="false"
              :class="['relative', { 'rounded-r-md': !hasAppSid }]"
              :disabled="
                appIsBeingCreated ||
                hasAppSid ||
                selectedNumberHasNoVoiceCapabilities
              "
              :text="$t('core::settings.integrations.twilio.create_app')"
              @click="createTwiMLApp"
            />
            <IButton
              v-if="hasAppSid"
              variant="danger"
              :rounded="false"
              class="relative rounded-r-md"
              icon="Trash"
              @click="deleteTwiMLApp"
            />
          </div>
        </div>
      </div>
    </div>

    <template #footer v-if="isConfigured">
      <IButton
        class="mb-2"
        @click="save"
        :disabled="selectedNumberHasNoVoiceCapabilities"
        :text="$t('core::app.save')"
      />
    </template>
  </ICard>
</template>
<script setup>
import { ref, computed } from 'vue'
import find from 'lodash/find'
import { isValueEmpty } from '@/utils'
import { useI18n } from 'vue-i18n'
import { useForm } from '~/Core/resources/js/composables/useForm'

const { t } = useI18n()

const numbers = ref([])
const componentReady = ref(false)
const appIsBeingCreated = ref(false)
const numbersRetrievalRequestInProgress = ref(false)
const showAppUrlWarning = ref(false)
const { form } = useForm()
const isSecure = Innoclapps.config('is_secure')

const hasAuthToken = computed(() => !isValueEmpty(form.twilio_auth_token))

const hasAccountSid = computed(() => !isValueEmpty(form.twilio_account_sid))

const hasAppSid = computed(() => !isValueEmpty(form.twilio_app_sid))

const showNumberConfig = computed(
  () => hasAuthToken.value && hasAccountSid.value
)

const showAppConfig = computed(() => !isValueEmpty(form.twilio_number))

const isConfigured = computed(
  () => hasAuthToken.value && hasAccountSid.value && hasAppSid.value
)

const selectedNumber = computed(() =>
  find(numbers.value, ['phoneNumber', form.twilio_number])
)

const selectedNumberHasNoVoiceCapabilities = computed(() => {
  if (!selectedNumber.value) {
    return false
  }
  return selectedNumber.value.capabilities.voice === false
})

function save() {
  form.post('settings').then(() => {
    Innoclapps.success(t('core::settings.updated'))
    window.location.reload()
  })
}

function disconnect() {
  Innoclapps.request()
    .delete('/twilio')
    .then(() => {
      window.location.reload()
    })
}

function updateTwiMLAppURL() {
  Innoclapps.request()
    .put(`/twilio/app/${form.twilio_app_sid}`, {
      voiceUrl: Innoclapps.config('voip.endpoints.call'),
    })
    .then(() => {
      window.location.reload()
    })
}

function retrieveNumbers() {
  numbersRetrievalRequestInProgress.value = true

  Innoclapps.request()
    .get('/twilio/numbers', {
      params: {
        account_sid: form.twilio_account_sid,
        auth_token: form.twilio_auth_token,
      },
    })
    .then(({ data }) => {
      numbers.value = data
    })
    .finally(() => (numbersRetrievalRequestInProgress.value = false))
}

/**
 * Get the TwiML app associated with the integration
 *
 * @return {Object}
 */
async function getTwiMLApp() {
  let { data } = await Innoclapps.request().get(
    `/twilio/app/${form.twilio_app_sid}`
  )

  return data
}

function createTwiMLApp() {
  appIsBeingCreated.value = true
  Innoclapps.request()
    .post('/twilio/app', {
      number: form.twilio_number,
      account_sid: form.twilio_account_sid,
      auth_token: form.twilio_auth_token,
      voiceMethod: 'POST',
      voiceUrl: Innoclapps.config('voip.endpoints.call'),
      friendlyName: 'Concord CRM',
    })
    .then(({ data }) => {
      form.twilio_app_sid = data.app_sid
    })
    .finally(() => (appIsBeingCreated.value = false))
}

function deleteTwiMLApp() {
  Innoclapps.dialog().confirm().then(deleteTwiMLAppWithoutConfirmation)
}

function deleteTwiMLAppWithoutConfirmation() {
  Innoclapps.request()
    .delete(`/twilio/app/${form.twilio_app_sid}`, {
      params: {
        account_sid: form.twilio_account_sid,
        auth_token: form.twilio_auth_token,
      },
    })
    .then(() => {
      form.twilio_app_sid = null
    })
}

function prepareComponent() {
  Innoclapps.request()
    .get('/settings')
    .then(({ data }) => {
      form.set({
        twilio_account_sid: data.twilio_account_sid,
        twilio_auth_token: data.twilio_auth_token,
        twilio_app_sid: data.twilio_app_sid,
        twilio_number: data.twilio_number,
      })

      componentReady.value = true

      if (hasAuthToken.value && hasAccountSid.value) {
        retrieveNumbers()

        if (hasAppSid.value) {
          getTwiMLApp()
            .then(app => {
              if (app.voiceUrl !== Innoclapps.config('voip.endpoints.call')) {
                showAppUrlWarning.value = true
              }
            })
            .catch(error => {
              // If we get 404 error when retrieving the app, this means that the app
              // does not exists in Twilio, in this case, we will delete the app from
              // the installation to forget the apps sid, see the TwilioAppController destroy method
              if (error.response.data.message.indexOf('[HTTP 404]') > -1) {
                deleteTwiMLAppWithoutConfirmation()
              }
            })
        }
      }
    })
}

prepareComponent()
</script>
