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
    ref="dropdownRef"
    placement="bottom-end"
    :full="false"
    :offset="15"
    @show="visible = true"
    @hide="visible = false"
  >
    <template #toggle="{ toggle }">
      <IButton
        @click="toggle"
        variant="secondary"
        :rounded="false"
        :size="false"
        icon="Plus"
        icon-class="w-6 h-6"
        class="rounded-full p-1"
      />
    </template>
    <div class="w-56">
      <div
        class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 text-sm dark:border-neutral-700"
      >
        <p
          class="font-medium text-neutral-600 dark:text-neutral-100"
          v-t="'core::app.quick_create'"
        />
        <span
          class="rounded-md bg-neutral-700 px-1.5 text-base text-neutral-100 dark:bg-neutral-600 dark:text-neutral-200"
        >
          <span class="-mt-0.5 block">+</span>
        </span>
      </div>
      <div class="space-y-0.5">
        <IDropdownItem
          v-for="(item, index) in quickCreateMenuItems"
          :key="index"
          v-show="$route.path !== item.quickCreateRoute"
          :icon="item.icon"
          :to="item.quickCreateRoute"
        >
          <span class="inline-flex w-full items-center justify-between">
            <span>{{ item.quickCreateName }}</span>
            <span
              class="rounded-md bg-neutral-100 px-1.5 uppercase text-neutral-500 dark:bg-neutral-700 dark:text-neutral-300"
              v-text="item.keyboardShortcutChar"
            />
          </span>
        </IDropdownItem>
      </div>
    </div>
  </IDropdown>
</template>
<script setup>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import IButton from '~/Core/resources/js/components/UI/Buttons/IButton.vue'

const store = useStore()
const router = useRouter()
const visible = ref(false)
const sidebarNavigation = computed(() => store.state.menu)

const dropdownRef = ref(null)
const quickCreateMenuItems = computed(() =>
  sidebarNavigation.value.filter(item => item.inQuickCreate)
)

const itemsWithKeyboardShortcut = computed(() =>
  quickCreateMenuItems.value.filter(item => item.keyboardShortcutChar !== null)
)

registerKeyboardShortcuts()

/**
 * Register the quick create keyboard shortcuts
 * NOTE: They don't need to be unbinded as this is a global component
 */
function registerKeyboardShortcuts() {
  itemsWithKeyboardShortcut.value.forEach(item => {
    Innoclapps.addShortcut(
      '+ ' + item.keyboardShortcutChar.toLowerCase(),
      () => {
        // If the dropdown is open and the user uses keyboard shortcut
        // it won't be closed as the popper component is expecting click in order to close the component
        if (visible.value) {
          dropdownRef.value.hide()
        }

        router.push(item.quickCreateRoute)
      }
    )
  })
}
</script>
