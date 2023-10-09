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
  <div>
    <Lightbox v-model="activeIndex" :urls="imagesUrls" />
    <div ref="htmlWrapperRef" v-html="html" @click="handleWrapperClickEvent" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import Lightbox from './Lightbox.vue'

const props = defineProps({ html: String })

const activeIndex = ref(null)

const htmlWrapperRef = ref(null)

const imagesUrls = ref([])

function handleWrapperClickEvent(e) {
  if (
    e.target.tagName === 'IMG' &&
    e.target.dataset !== undefined &&
    e.target.dataset.lightboxIndex >= 0 &&
    e.target.parents('a')[0]?.tagName !== 'A'
  ) {
    activeIndex.value = parseInt(e.target.dataset.lightboxIndex)
  }
}

function parseAvailableImages() {
  imagesUrls.value = []

  htmlWrapperRef.value.getElementsByTagName('img').forEach(img => {
    if (
      img.src &&
      imagesUrls.value.indexOf(img.src) === -1 &&
      img.parents('a')[0]?.tagName !== 'A' // no lightbox for images wrapped in links
    ) {
      imagesUrls.value.push(img.src)
      img.classList.add('cursor-pointer')
      img.classList.add('hover:opacity-90')
      img.dataset.lightboxIndex = imagesUrls.value.length - 1
    }
  })
}

watch(() => props.html, parseAvailableImages, {
  flush: 'post', // after dom updated
})

onMounted(parseAvailableImages)
</script>
