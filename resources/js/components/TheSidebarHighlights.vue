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
  <div class="group mt-8 px-2">
    <h3
      v-once
      class="hidden px-3 text-xs font-medium uppercase tracking-wider text-neutral-50 group-hover:inline-flex group-hover:items-center"
      id="highlights-headline"
      v-i-tooltip.right="
        $t('core::app.highlights.refresh_interval', { interval: 10 })
      "
    >
      <Icon icon="QuestionMarkCircle" class="mr-2 h-5 w-5 text-current"></Icon>
      {{ $t('core::app.highlights.highlights') }}
    </h3>
    <div
      class="mt-1 space-y-1"
      role="group"
      aria-labelledby="highlights-headline"
    >
      <router-link
        v-for="highlight in highlights"
        :key="highlight.name"
        v-memo="[highlight.count]"
        class="group flex items-center rounded-md px-3 py-2 text-sm font-medium text-neutral-50 hover:bg-neutral-600"
        :to="highlight.route"
      >
        <span
          :class="[
            highlight.count > 0 ? highlight.bgColorClass : 'bg-success-500',
            'mr-4 h-2.5 w-2.5 rounded-full',
          ]"
          aria-hidden="true"
        />
        <span class="mr-1 truncate" v-text="highlight.name"></span>
        ({{ highlight.count }})
      </router-link>
    </div>
  </div>
</template>
<script setup>
import { onMounted, onBeforeUnmount, shallowRef } from 'vue'

const highlights = shallowRef(Innoclapps.config('highlights'))

let intervalId = null

function fetch() {
  Innoclapps.request()
    .get('/highlights')
    .then(({ data }) => {
      highlights.value = data
    })
}

onMounted(() => {
  intervalId = setInterval(fetch, 1000 * 60 * 10)
})

onBeforeUnmount(() => {
  clearInterval(intervalId)
})
</script>
