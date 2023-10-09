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
  <div class="inline-block">
    <Compose
      v-if="isComposing"
      :visible="isComposing"
      ref="composeRef"
      :to="to"
    />
    <IDropdown no-caret @show="handleDropdownShownEvent" ref="dropdownRef">
      <template #toggle="{ toggle }">
        <a
          class="link"
          @click.prevent="toggle"
          :href="'mailto:' + row[column.attribute]"
          v-text="row[column.attribute]"
        />
      </template>
      <span
        v-i-tooltip="
          hasAccountsConfigured
            ? ''
            : $t('mailclient::mail.account.integration_not_configured')
        "
      >
        <IDropdownItem
          href="#"
          :disabled="!hasAccountsConfigured"
          @click="compose(true)"
          :text="$t('mailclient::mail.create')"
        />
      </span>
      <IButtonCopy
        :success-message="$t('core::fields.email_copied')"
        :text="row[column.attribute]"
        tag="IDropdownItem"
      >
        {{ $t('core::app.copy') }}
      </IButtonCopy>
      <IDropdownItem
        :href="'mailto:' + row[column.attribute]"
        :text="$t('core::app.open_in_app')"
      />
    </IDropdown>
  </div>
</template>
<script setup>
import { ref, computed, nextTick } from 'vue'
import Compose from '~/MailClient/resources/js/views/Emails/ComposeMessage.vue'
import { useStore } from 'vuex'

import propsDefinition from './props'
const props = defineProps(propsDefinition)

const store = useStore()
const isComposing = ref(false)

const composeRef = ref(null)
const dropdownRef = ref(null)

const hasAccountsConfigured = computed(
  () => store.getters['emailAccounts/hasConfigured']
)

/**
 * Get the predefined TO property
 */
const to = computed(() => [
  {
    address: props.row[props.column.attribute],
    name: props.row.display_name,
    resourceName: props.resourceName,
    id: props.row.id,
  },
])

/**
 * Compose new email
 */
function compose(state = true) {
  isComposing.value = state
  dropdownRef.value.hide()
  nextTick(() => {
    composeRef.value.subjectRef.focus()
  })
}

function handleDropdownShownEvent() {
  // Load the placeholders when the first time dropdown
  // is shown, helps when first time is clicked on the dropdown -> Create Email the
  // placeholders are not loaded as the editor is initialized before the request is finished
  store.dispatch('fields/fetchPlaceholders')
  // We will check if the accounts are not fetched, if not
  // we will dispatch the store fetch function to retrieve the
  // accounts before the dropdown is shown so the Compose.vue won't need to
  // As if we use the ComposeMessage.vue, every row in the table will retireve the accounts
  // and there will be 20+ requests when the table loads
  if (!store.state.emailAccounts.dataFetched) {
    store.dispatch('emailAccounts/fetch')
  }
}
</script>
