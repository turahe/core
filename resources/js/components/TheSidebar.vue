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
  <!-- Sidebar for mobile -->
  <TransitionRoot as="template" :show="isSidebarOpen">
    <Dialog
      as="div"
      static
      class="fixed inset-0 z-50 flex md:hidden"
      @close="setSidebarOpenState(false)"
      :open="isSidebarOpen"
    >
      <TransitionChild
        as="template"
        enter="transition-opacity ease-linear duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="transition-opacity ease-linear duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <DialogOverlay class="fixed inset-0 bg-neutral-600 bg-opacity-75" />
      </TransitionChild>
      <TransitionChild
        as="template"
        enter="transition ease-in-out duration-300 transform"
        enter-from="-translate-x-full"
        enter-to="translate-x-0"
        leave="transition ease-in-out duration-300 transform"
        leave-from="translate-x-0"
        leave-to="-translate-x-full"
      >
        <div
          class="relative flex w-56 max-w-xs flex-col bg-neutral-800 pb-4 pt-5 dark:bg-neutral-900"
        >
          <TransitionChild
            as="template"
            enter="ease-in-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in-out duration-300"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="absolute right-0 top-0 -mr-12 pt-2">
              <button
                type="button"
                class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                @click="setSidebarOpenState(false)"
              >
                <span class="sr-only">Close sidebar</span>
                <Icon icon="X" class="h-6 w-6 text-white" />
              </button>
            </div>
          </TransitionChild>
          <div class="flex shrink-0 items-center px-4">
            <router-link class="whitespace-normal" :to="{ name: 'dashboard' }">
              <span v-if="!logo" class="font-bold text-white">
                {{ companyName }}
              </span>
              <img v-else :src="logo" class="h-10 max-h-14 w-auto" />
            </router-link>
          </div>
          <div class="mt-5 h-0 flex-1 overflow-y-auto">
            <nav class="space-y-1 px-2">
              <router-link
                v-for="item in sidebarNavigation"
                :key="item.id"
                :to="item.route"
                custom
                v-slot="{ href, navigate, isActive }"
              >
                <a
                  :href="href"
                  @click="navigate"
                  :class="[
                    isActive
                      ? 'bg-neutral-700 text-white'
                      : 'text-neutral-50 hover:bg-neutral-600',
                    'group relative flex items-center rounded-md px-2 py-2 focus:outline-none',
                  ]"
                  :aria-current="isActive ? 'page' : undefined"
                >
                  <Icon
                    v-if="item.icon"
                    :icon="item.icon"
                    class="mr-4 h-6 w-6 shrink-0 text-neutral-300"
                  />

                  {{ item.name }}

                  <IBadge
                    v-if="item.badge"
                    :variant="item.badgeVariant"
                    size="circle"
                    wrapper-class="absolute -left-px -top-px"
                    :text="item.badge"
                  />

                  <router-link
                    v-if="item.inQuickCreate"
                    v-show="$route.path !== item.quickCreateRoute"
                    :to="item.quickCreateRoute"
                    :class="[
                      'ml-auto rounded-md',
                      isActive
                        ? 'hover:bg-neutral-800'
                        : 'hover:bg-neutral-700',
                    ]"
                  >
                    <Icon icon="Plus" class="h-5 w-5"></Icon>
                  </router-link>
                </a>
              </router-link>
            </nav>
          </div>
          <SidebarHighlights />
        </div>
      </TransitionChild>
      <div class="w-14 shrink-0" aria-hidden="true">
        <!-- Dummy element to force sidebar to shrink to fit close icon -->
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Static sidebar for desktop -->
  <div
    class="hidden bg-neutral-800 dark:bg-neutral-900 md:flex md:shrink-0"
    v-show="['404', '403', 'not-found'].indexOf($route.name) === -1"
  >
    <div class="flex w-56 flex-col">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col overflow-y-auto pb-4 pt-5">
        <div class="flex shrink-0 items-center px-4">
          <router-link class="whitespace-normal" :to="{ name: 'dashboard' }">
            <span v-if="!logo" class="font-bold text-white">
              {{ companyName }}
            </span>
            <img v-else :src="logo" class="h-10 max-h-14 w-auto" />
          </router-link>
        </div>

        <!-- Profile dropdown -->
        <div class="relative mt-4 inline-block px-3 text-left">
          <IDropdown :full="false" items-class="max-w-[200px]">
            <template #toggle="{ toggle }">
              <button
                type="button"
                @click="toggle"
                class="group mt-3 w-full rounded-md bg-neutral-200 px-3.5 py-2 text-left text-sm font-medium text-neutral-700 hover:bg-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 focus:ring-offset-neutral-100 dark:border dark:border-neutral-600 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:focus:ring-offset-neutral-300"
              >
                <span class="flex w-full items-center justify-between">
                  <span
                    class="flex min-w-0 items-center justify-between space-x-3"
                  >
                    <IAvatar
                      :src="currentUser.avatar_url"
                      :title="currentUser.name"
                    />
                    <span class="flex min-w-0 flex-1 flex-col">
                      <span
                        class="truncate text-sm font-medium text-neutral-800 dark:text-white"
                      >
                        {{ currentUser.name }}
                      </span>
                      <span
                        class="truncate text-sm text-neutral-600 dark:text-neutral-300"
                      >
                        {{ currentUser.email }}
                      </span>
                    </span>
                  </span>
                  <Icon
                    icon="Selector"
                    class="h-5 w-5 shrink-0 text-neutral-500 group-hover:text-neutral-600 dark:text-neutral-400 dark:group-hover:text-neutral-300"
                  />
                </span>
              </button>
            </template>
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
              <div v-if="currentUser.teams.length > 0" class="px-4 py-3">
                <p
                  class="inline-flex items-center text-sm text-neutral-800 dark:text-neutral-100"
                >
                  <Icon
                    icon="UserGroup"
                    class="mr-1 h-5 w-5 text-neutral-600 dark:text-neutral-400"
                  />
                  <span
                    v-text="
                      $t('users::team.your_teams', currentUser.teams.length)
                    "
                  />
                </p>
                <p
                  v-for="team in currentUser.teams"
                  :key="team.id"
                  class="flex text-sm font-medium text-neutral-900 dark:text-neutral-300"
                >
                  <span
                    :class="[
                      'truncate',
                      team.user_id === currentUser.id
                        ? 'text-primary-600 dark:text-primary-400'
                        : '',
                    ]"
                    v-text="team.name"
                  />
                </p>
              </div>
              <div class="py-1">
                <IDropdownItem
                  :to="{ name: 'profile' }"
                  :text="$t('users::profile.profile')"
                />

                <IDropdownItem
                  :to="{ name: 'calendar-sync' }"
                  :text="$t('activities::calendar.calendar_sync')"
                />

                <IDropdownItem
                  :to="{ name: 'oauth-accounts' }"
                  :text="$t('core::oauth.connected_accounts')"
                />

                <IDropdownItem
                  v-if="$gate.userCan('access-api')"
                  :to="{ name: 'personal-access-tokens' }"
                  :text="$t('core::api.personal_access_tokens')"
                />
              </div>
              <div class="py-1">
                <IDropdownItem
                  href="#"
                  @click="$store.dispatch('logout')"
                  :text="$t('auth.logout')"
                />
              </div>
            </div>
          </IDropdown>
        </div>

        <!-- Sidebar links -->
        <div class="mt-6 flex h-0 flex-1 flex-col overflow-y-auto">
          <nav class="flex-1 space-y-1 px-2">
            <router-link
              v-for="item in sidebarNavigation"
              :key="item.id"
              :to="item.route"
              custom
              v-slot="{ href, navigate, isActive }"
            >
              <a
                :href="href"
                @click="navigate"
                :class="[
                  isActive
                    ? 'bg-neutral-700 text-white'
                    : 'text-neutral-50 hover:bg-neutral-600',
                  'group relative flex items-center rounded-md px-2 py-2 text-sm focus:outline-none',
                ]"
                :aria-current="isActive ? 'page' : undefined"
              >
                <Icon
                  v-if="item.icon"
                  :icon="item.icon"
                  class="mr-3 h-6 w-6 shrink-0 text-neutral-300"
                />

                {{ item.name }}

                <IBadge
                  v-if="item.badge"
                  :variant="item.badgeVariant"
                  size="circle"
                  wrapper-class="absolute -left-px -top-px"
                  :text="item.badge"
                />

                <router-link
                  v-if="item.inQuickCreate"
                  v-show="$route.path !== item.quickCreateRoute"
                  :to="item.quickCreateRoute"
                  :class="[
                    'ml-auto hidden rounded-md group-hover:block',
                    isActive ? 'hover:bg-neutral-800' : 'hover:bg-neutral-700',
                  ]"
                >
                  <Icon icon="Plus" class="h-5 w-5"></Icon>
                </router-link>
              </a>
            </router-link>
          </nav>
        </div>
        <SidebarHighlights />
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'
import SidebarHighlights from './TheSidebarHighlights.vue'
import {
  Dialog,
  DialogOverlay,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'

const { currentUser, setting } = useApp()
const store = useStore()

const sidebarNavigation = computed(() => store.state.menu)
const isSidebarOpen = computed(() => store.state.sidebarOpen)
const companyName = computed(() => setting('company_name'))
const logo = Innoclapps.config('options.logo_light')

function setSidebarOpenState(value) {
  store.commit('SET_SIDEBAR_OPEN', value)
}
</script>
