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
    <div class="mx-auto max-w-7xl">
      <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
        <aside class="lg:col-span-3">
          <SettingsMenu></SettingsMenu>
        </aside>
        <div ref="settingsViewRef" class="sm:hidden"></div>
        <div class="lg:col-span-9">
          <router-view></router-view>
        </div>
      </div>
    </div>
  </ILayout>
</template>
<script setup>
import { ref } from 'vue'
import SettingsMenu from './SettingsMenu.vue'
import { onBeforeRouteUpdate } from 'vue-router'

const settingsViewRef = ref(null)

onBeforeRouteUpdate((to, from, next) => {
  // Scroll on top when some child route from the settings is loaded
  // Helps the user to see the top if navigated too down as well on mobile as
  // the menu is on the top of the page
  if (to.matched && to.matched[0] && to.matched[0].name === 'settings') {
    document.getElementById('main').scrollTo({
      top: settingsViewRef.value.getBoundingClientRect().top,
      behavior: 'smooth',
    })
  }
  next()
})
</script>
