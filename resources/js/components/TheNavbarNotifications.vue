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
  <IDropdown
    placement="bottom-end"
    items-class="max-w-xs sm:max-w-sm"
    :full="false"
    ref="dropdownRef"
  >
    <template #toggle="{ toggle }">
      <IButton
        variant="white"
        :rounded="false"
        :size="false"
        class="relative rounded-full p-1"
        @click="toggle(), markAllRead()"
      >
        <Icon icon="Bell" class="h-6 w-6" />
        <IBadge
          v-if="hasUnread"
          variant="primary"
          size="circle"
          wrapper-class="absolute -top-2 -right-2"
          :text="totalUnread"
        />
      </IButton>
    </template>

    <div
      :class="[
        'flex items-center px-4 py-3 sm:p-4',
        { 'border-b border-neutral-200 dark:border-neutral-700': total > 0 },
      ]"
    >
      <div
        :class="[
          'grow text-neutral-800 dark:text-white',
          { 'font-medium': total > 0, 'sm:text-sm': total === 0 },
        ]"
        v-t="
          total > 0
            ? 'core::notifications.notifications'
            : 'core::notifications.no_notifications'
        "
      />
      <router-link
        :to="{ name: 'profile', hash: '#notifications' }"
        @click="() => $refs.dropdownRef.hide()"
        v-i-tooltip="$t('core::settings.settings')"
        class="link ml-2"
      >
        <Icon icon="Cog" class="h-5 w-5" />
      </router-link>
    </div>

    <div
      class="max-h-96 divide-y divide-neutral-200 overflow-y-auto dark:divide-neutral-700"
    >
      <IDropdownItem
        v-for="notification in notifications"
        :key="notification.id"
        :title="localize(notification)"
        :to="notification.data.path"
      >
        <p
          class="truncate text-neutral-800 dark:text-neutral-100"
          v-text="localize(notification)"
        />
        <span
          class="text-xs text-neutral-500 dark:text-neutral-300"
          v-text="localizedDateTime(notification.created_at)"
        />
      </IDropdownItem>
    </div>
    <div
      v-show="total > 0"
      class="flex items-center justify-end border-t border-neutral-200 bg-neutral-50 px-4 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-700"
    >
      <router-link
        :to="{ name: 'notifications' }"
        @click="() => $refs.dropdownRef.hide()"
        class="link"
        v-t="'core::app.see_all'"
      />
    </div>
  </IDropdown>
</template>
<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useDates } from '~/Core/resources/js/composables/useDates'
import { useGlobalEventListener } from '~/Core/resources/js/composables/useGlobalEventListener'

const { currentUser } = useApp()
const { localizedDateTime } = useDates()

const store = useStore()

const total = computed(() => store.getters['users/totalNotifications'])

const hasUnread = computed(() => store.getters['users/hasUnreadNotifications'])

const localize = store.getters['users/localizeNotification']

const notifications = computed(() => currentUser.value.notifications.latest)

const totalUnread = computed(() => currentUser.value.notifications.unread_count)

function markAllRead() {
  store.dispatch('users/markAllNotificationsAsRead')
}
</script>
