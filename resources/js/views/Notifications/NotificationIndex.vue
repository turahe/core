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
  <ILayout>
    <div class="mx-auto max-w-5xl">
      <ICard no-body :header="$t('core::notifications.notifications')">
        <template #actions>
          <IButton
            v-show="countNotifications > 0"
            variant="white"
            :loading="requestInProgress"
            size="sm"
            :disabled="!hasUnreadNotifications"
            :text="$t('core::notifications.mark_all_as_read')"
            @click="markAllRead"
          />
        </template>
        <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
          <li
            v-for="(notification, index) in notifications"
            :key="notification.id"
          >
            <a
              href="#"
              @click.prevent="$router.push(notification.data.path)"
              class="block focus:outline-none hover:bg-neutral-50 dark:hover:bg-neutral-700/60"
            >
              <div class="flex items-center px-4 py-4 sm:px-6">
                <div
                  class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
                >
                  <div class="truncate">
                    <p
                      class="truncate text-sm font-medium text-neutral-800 dark:text-neutral-100"
                      v-text="localize(notification)"
                    />

                    <p
                      class="mt-2 text-sm text-neutral-500 dark:text-neutral-300"
                      v-text="localizedDateTime(notification.created_at)"
                    />
                  </div>
                </div>
                <div class="ml-5 shrink-0">
                  <IButtonIcon icon="Trash" @click.stop="destroy(index)" />
                </div>
              </div>
            </a>
          </li>
        </ul>

        <InfinityLoader @handle="loadHandler" />

        <ICardBody v-show="countNotifications === 0" class="text-center">
          <Icon icon="EmojiSad" class="mx-auto h-12 w-12 text-neutral-400" />
          <h3
            class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
            v-t="'core::notifications.no_notifications'"
          />
        </ICardBody>
        <p
          v-show="noMoreResults"
          class="p-3 text-center text-neutral-600"
          v-t="'core::notifications.no_more_notifications'"
        />
      </ICard>
    </div>
  </ILayout>
</template>
<script setup>
import { ref, shallowReactive, computed, nextTick } from 'vue'
import InfinityLoader from '~/Core/resources/js/components/InfinityLoader.vue'
import findIndex from 'lodash/findIndex'
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useDates } from '~/Core/resources/js/composables/useDates'
import { useGlobalEventListener } from '~/Core/resources/js/composables/useGlobalEventListener'

const store = useStore()
const { currentUser } = useApp()
const { localizedDateTime } = useDates()

const notifications = shallowReactive([])
const noMoreResults = ref(false)
const nextPage = ref(2)
const requestInProgress = ref(false)

const hasUnreadNotifications = computed(
  () => store.getters['users/hasUnreadNotifications']
)

const localize = store.getters['users/localizeNotification']

const countNotifications = computed(() => notifications.length)

function markAllRead() {
  requestInProgress.value = true
  store
    .dispatch('users/markAllNotificationsAsRead')
    .finally(() => (requestInProgress.value = false))
}

function destroy(index) {
  store
    .dispatch('users/destroyNotification', notifications[index])
    .then(notification => notifications.splice(index, 1))
}

function addNotifications(notifications) {
  // We will check if the notification already exists
  // if not, then we will add to the array of notifications
  // In case of previously broadcasted notification, to prevent duplicate
  // as the last one will be duplicate
  notifications.forEach(notification => {
    if (findIndex(notifications, ['id', notification.id]) === -1) {
      notifications.push(notification)
    }
  })
}

async function loadHandler($state) {
  let { data } = await loadMore()

  addNotifications(data.data)

  nextTick(() => {
    if (data.total === countNotifications.value) {
      noMoreResults.value = true
      $state.complete()
    }
  })

  nextPage.value += 1
  $state.loaded()
}

function loadMore() {
  return Innoclapps.request().get(store.state.users.notificationsEndpoint, {
    params: {
      page: nextPage.value,
    },
  })
}

function handleNewNotificationBroadcasted(notification) {
  notifications.unshift(notification)
}

// Set the initial notifications from the current user, as it's the first page
currentUser.value.notifications.latest.forEach(notification =>
  notifications.push(notification)
)

// Push new notification when new notification is broadcasted/added to update this list too
// Useful when the user is at the all notifications route,
// will update all notifications and the dropdown notifications too
useGlobalEventListener(
  'new-notification-added',
  handleNewNotificationBroadcasted
)
</script>
