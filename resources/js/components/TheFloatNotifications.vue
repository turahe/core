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
  <NotificationGroup group="app">
    <div
      class="notifications pointer-events-none fixed inset-0 flex items-start justify-end p-6 px-4 py-6"
    >
      <div class="w-full max-w-sm">
        <Notification
          v-slot="{ notifications, close }"
          enter="ease-out duration-300 transition"
          enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
          enter-to="translate-y-0 opacity-100 sm:translate-x-0"
          leave="transition ease-in duration-100"
          leave-from="opacity-100"
          leave-to="opacity-0"
          move="transition duration-500"
          move-delay="delay-300"
        >
          <div
            v-for="(notification, index) in notifications"
            :key="index"
            class="notification pointer-events-auto relative mb-2 w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-neutral-800"
            :class="{
              'border-2 border-danger-300': notification.type === 'error',
            }"
          >
            <div class="p-4">
              <div class="flex items-center">
                <div class="shrink-0">
                  <icon
                    v-if="notification.type === 'success'"
                    icon="CheckCircle"
                    class="h-5 w-5 text-success-400"
                  />
                  <icon
                    v-if="notification.type === 'info'"
                    icon="InformationCircle"
                    class="h-5 w-5 text-info-400"
                  />
                  <icon
                    v-if="notification.type === 'error'"
                    icon="XCircle"
                    class="h-5 w-5 text-danger-400"
                  />
                </div>
                <div class="ml-3 flex w-0 flex-1 justify-between">
                  <p
                    class="w-0 flex-1 text-sm font-medium text-neutral-800 dark:text-white"
                    v-text="notification.text"
                  />
                  <button
                    type="button"
                    v-if="notification.action"
                    @click="notification.action.onClick"
                    class="ml-3 shrink-0 rounded-md bg-white text-sm font-medium text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 hover:text-primary-500"
                    v-text="notification.action.text"
                  />
                </div>
                <div class="ml-4 flex shrink-0">
                  <button
                    type="button"
                    @click="close(notification.id)"
                    class="inline-flex rounded-md bg-white text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 hover:text-neutral-500 dark:bg-neutral-900"
                  >
                    <icon icon="X" class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Notification>
      </div>
    </div>
  </NotificationGroup>
</template>
