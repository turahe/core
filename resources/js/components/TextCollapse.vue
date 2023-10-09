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
  <div class="relative">
    <div v-html="visibleText" v-if="!lightbox" v-bind="$attrs" />
    <HtmlableLightbox v-else :html="visibleText" v-bind="$attrs" />

    <div v-show="hasTextToCollapse">
      <slot name="action" :collapsed="localIsCollapsed" :toggle="toggle">
        <div
          v-show="localIsCollapsed"
          @click="toggle"
          class="absolute bottom-0 h-1/2 w-full cursor-pointer bg-gradient-to-t from-white to-transparent dark:from-neutral-900"
        />

        <a
          href="#"
          v-show="!localIsCollapsed"
          class="link mt-2 block text-sm !no-underline"
          @click.prevent="toggle"
          v-t="'core::app.show_less'"
        />
      </slot>
    </div>
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import truncate from 'truncate-html'
import { watch, ref, computed, nextTick } from 'vue'
import HtmlableLightbox from './Lightbox/HtmlableLightbox.vue'

const emit = defineEmits(['update:collapsed', 'hasTextToCollapse'])

const props = defineProps({
  text: { type: String, required: true },
  length: { default: 150, type: Number },
  lightbox: { type: Boolean, default: false },
  collapsed: { type: Boolean, default: true },
})

const localIsCollapsed = ref(props.collapsed)
const truncatedText = ref('')

const hasTextToCollapse = computed(() => props.text.length >= props.length)
const visibleText = computed(() =>
  localIsCollapsed.value ? truncatedText.value : props.text
)

watch(
  () => props.collapsed,
  newVal => (localIsCollapsed.value = newVal)
)

watch(
  () => props.text,
  newVal => {
    truncatedText.value = truncate(newVal, props.length)
    nextTick(() => emit('hasTextToCollapse', hasTextToCollapse.value))
  },
  { immediate: true }
)

function toggle() {
  localIsCollapsed.value = !localIsCollapsed.value
  emit('update:collapsed', localIsCollapsed.value)
}
</script>
