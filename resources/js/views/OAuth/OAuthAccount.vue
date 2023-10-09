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
  <div
    class="rounded-md border border-neutral-200 px-6 py-4 shadow-sm dark:border-neutral-700"
  >
    <div class="flex flex-col sm:flex-row sm:items-center">
      <div class="flex items-center">
        <div class="self-start">
          <span
            class="inline-flex h-8 w-8 items-center justify-center rounded-full"
            :class="[
              account.requires_auth ? 'bg-neutral-400' : 'bg-success-400',
            ]"
          >
            <Icon icon="CheckCircle" class="h-6 w-6 text-white" />
          </span>
        </div>
        <div class="ml-1 sm:ml-3">
          <span
            class="text-sm font-medium text-neutral-700 dark:text-neutral-200"
            v-text="account.email"
          />
          <br />
          <div v-if="account.requires_auth">
            <p
              class="text-sm text-warning-500 dark:text-warning-400"
              v-t="'core::oauth.requires_authentication'"
            />
          </div>
          <slot name="after-name"></slot>
        </div>
      </div>
      <div class="ml-0 mt-2 shrink-0 grow sm:ml-auto sm:mt-0 sm:grow-0">
        <div class="flex items-center sm:justify-center">
          <router-link
            class="link text-sm"
            v-show="showReconnectLink"
            :to="{ name: 'oauth-accounts', query: { reconnect: account.id } }"
            v-t="'core::oauth.re_authenticate'"
          />
          <slot></slot>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  account: { type: Object },
  withReconnectLink: { type: Boolean, default: true },
})

const route = useRoute()

const showReconnectLink = computed(
  () => route.name !== 'oauth-accounts' && props.withReconnectLink
)
</script>
