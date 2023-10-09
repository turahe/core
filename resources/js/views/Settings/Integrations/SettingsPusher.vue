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
    @submit.prevent="submitPusherIntegrationSettings"
    @input="form.errors.clear($event.target.name)"
  >
    <ICard :overlay="!componentReady">
      <template #header>
        <div class="flex items-center">
          <Icon
            icon="CheckCircleSolid"
            class="mr-1 h-5 w-5 text-success-600"
            v-if="isConfigured && componentReady"
          />
          <ICardHeading>Pusher</ICardHeading>
        </div>
      </template>
      <template #actions>
        <a
          href="https://dashboard.pusher.com"
          class="link inline-flex items-center text-sm"
          target="_blank"
          rel="noopener noreferrer"
        >
          Pusher.com <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
        </a>
      </template>

      <IAlert
        :show="!isConfigured && componentReady"
        variant="info"
        class="mb-6"
      >
        Receive notifications in real time without the need to manually refresh
        the page, after synchronization, automatically updates the calendar,
        total unread emails and new emails.
      </IAlert>

      <div class="sm:flex sm:space-x-4">
        <IFormGroup class="w-full" label="App ID" label-for="pusher_app_id">
          <IFormInput
            v-model="form.pusher_app_id"
            id="pusher_app_id"
          ></IFormInput>
        </IFormGroup>
        <IFormGroup class="w-full" label="App Key" label-for="pusher_app_key">
          <IFormInput
            v-model="form.pusher_app_key"
            id="pusher_app_key"
          ></IFormInput>
        </IFormGroup>
      </div>
      <div class="sm:flex sm:space-x-4">
        <IFormGroup
          class="w-full"
          label="App Secret"
          label-for="pusher_app_secret"
        >
          <IFormInput
            type="password"
            v-model="form.pusher_app_secret"
            id="pusher_app_secret"
          ></IFormInput>
        </IFormGroup>
        <IFormGroup class="w-full">
          <template #label>
            <div class="flex">
              <div class="grow">
                <IFormLabel for="pusher_app_cluster">App Cluster</IFormLabel>
              </div>
              <div>
                <small>
                  <a
                    href="https://pusher.com/docs/clusters"
                    class="link mb-1 inline-flex items-center"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    https://pusher.com/docs/clusters
                    <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
                  </a>
                </small>
              </div>
            </div>
          </template>
          <IFormInput
            v-model="form.pusher_app_cluster"
            id="pusher_app_cluster"
          ></IFormInput>
        </IFormGroup>
      </div>

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
import { computed } from 'vue'
import { useSettings } from './../useSettings'

const {
  form,
  submit,
  isReady: componentReady,
  originalSettings,
} = useSettings()

const isConfigured = computed(
  () =>
    originalSettings.value.pusher_app_id &&
    originalSettings.value.pusher_app_key &&
    originalSettings.value.pusher_app_secret
)

function submitPusherIntegrationSettings() {
  submit(form => window.location.reload())
}
</script>
