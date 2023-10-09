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
  <VueEasyLightbox
    :visible="visibleRef"
    :imgs="imgsRef"
    :index="indexRef"
    @hide="onHide"
    @on-index-change="
      (oldIndex, newIndex) => $emit('update:modelValue', newIndex)
    "
  >
    <template #toolbar="{ toolbarMethods }">
      <div class="vel-toolbar">
        <div
          role="button"
          @click="toolbarMethods.zoomIn"
          aria-label="zoom in button"
          class="toolbar-btn toolbar-btn__zoomin"
        >
          <svg class="vel-icon icon" aria-hidden="true">
            <use xlink:href="#icon-zoomin"></use>
          </svg>
        </div>
        <div
          role="button"
          @click="toolbarMethods.zoomOut"
          aria-label="zoom out button"
          class="toolbar-btn toolbar-btn__zoomout"
        >
          <svg class="vel-icon icon" aria-hidden="true">
            <use xlink:href="#icon-zoomout"></use>
          </svg>
        </div>
        <!--         <div
          role="button"
          @click="toolbarMethods.resize"
          aria-label="resize image button"
          class="toolbar-btn toolbar-btn__resize"
        >
          <svg class="vel-icon icon" aria-hidden="true">
            <use xlink:href="#icon-resize"></use>
          </svg>
        </div> -->
        <div
          role="button"
          @click="toolbarMethods.rotateLeft"
          aria-label="image rotate left button"
          class="toolbar-btn toolbar-btn__rotate"
        >
          <svg class="vel-icon icon" aria-hidden="true">
            <use xlink:href="#icon-rotate-left"></use>
          </svg>
        </div>
        <div
          role="button"
          @click="toolbarMethods.rotateRight"
          aria-label="image rotate right button"
          class="toolbar-btn toolbar-btn__rotate"
        >
          <svg class="vel-icon icon" aria-hidden="true">
            <use xlink:href="#icon-rotate-right"></use>
          </svg>
        </div>
        <slot name="toolbar"></slot>
      </div>
    </template>
  </VueEasyLightbox>
</template>
<script setup>
import { computed, watch } from 'vue'
import VueEasyLightbox, {
  useEasyLightbox,
} from 'vue-easy-lightbox/dist/external-css/vue-easy-lightbox.esm.min.js'
import 'vue-easy-lightbox/dist/external-css/vue-easy-lightbox.css'

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: {
    validator: prop =>
      typeof prop === 'number' || typeof prop === 'string' || prop === null,
  }, // active index
  urls: Array,
})

const {
  show,
  visibleRef,
  indexRef, // this is the initIndex
  imgsRef,
} = useEasyLightbox({
  imgs: props.urls,
  // initial index (not used)
  initIndex: 0,
})

const totalImages = computed(() => props.urls.length)

const onHide = () => {
  visibleRef.value = false

  if (props.modelValue !== null) {
    emit('update:modelValue', null)
  }
}

watch(
  () => props.modelValue,
  newVal => {
    if (newVal >= 0 && newVal !== null) {
      show(newVal)
    } else {
      visibleRef.value = false
    }
  },
  { immediate: true }
)

watch(totalImages, () => (imgsRef.value = props.urls))
</script>
