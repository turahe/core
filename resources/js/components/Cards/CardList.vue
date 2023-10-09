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
  <div class="flex flex-wrap space-x-0 lg:flex-nowrap lg:space-x-4">
    <template v-if="!componentReady">
      <div class="w-full lg:w-1/2" v-for="p in totalPlaceholders" :key="p">
        <CardPlaceholder pulse class="mb-4" />
      </div>
    </template>

    <div v-for="card in cards" :key="card.uriKey" :class="card.width">
      <component :is="card.component" class="mb-4" :card="card" />
    </div>
  </div>
</template>
<script setup>
import { ref, shallowRef } from 'vue'
import CardPlaceholder from './CardPlaceholder.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  totalPlaceholders: { default: 2, type: Number },
})

const cards = shallowRef([])
const componentReady = ref(false)

/**
 * Fetch the resource cards
 *
 * @return {Void}
 */
function fetch() {
  Innoclapps.request()
    .get(`/${props.resourceName}/cards`)
    .then(({ data }) => {
      cards.value = data
      componentReady.value = true
    })
}

fetch()
</script>
