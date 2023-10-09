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
  <Lightbox v-model="activeLightboxImageIndex" :urls="imagesUrlsForLightbox">
    <template #toolbar>
      <a
        v-for="type in ['preview_url', 'download_url']"
        :key="type"
        :href="activeLightboxMedia[type]"
        :target="type === 'preview_url' ? '_blank' : undefined"
        tabindex="-1"
        rel="noopener noreferrer"
        class="toolbar-btn"
      >
        <Icon
          :icon="type == 'preview_url' ? 'Eye' : 'Download'"
          class="h-5 w-5"
        />
      </a>
    </template>
  </Lightbox>

  <ul
    class="divide-y divide-neutral-200 dark:divide-neutral-700"
    v-bind="$attrs"
  >
    <li
      v-for="media in items"
      :key="media.id"
      class="group flex items-center space-x-3 py-4"
    >
      <div class="shrink-0">
        <span
          :class="[
            media.was_recently_created
              ? 'bg-success-500 text-white'
              : 'bg-neutral-200 text-neutral-400 dark:bg-neutral-700 dark:text-neutral-300',
          ]"
          class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm"
        >
          <Icon
            v-if="media.was_recently_created"
            icon="Check"
            class="h-5 w-5"
          />
          <span v-else v-text="media.extension"></span>
        </span>
      </div>

      <div class="min-w-0 flex-1 truncate">
        <a
          v-if="media.aggregate_type !== 'image'"
          :href="media.view_url"
          class="text-sm font-medium text-neutral-800 focus:outline-none hover:text-neutral-500 dark:text-white dark:hover:text-neutral-300"
          target="_blank"
          rel="noopener noreferrer"
          tabindex="0"
          v-text="media.file_name"
        />
        <a
          v-else
          :href="media.view_url"
          @click.prevent="
            activeLightboxImageIndex = findIndexForLightbox(media.preview_url)
          "
          class="text-sm font-medium text-neutral-800 focus:outline-none hover:text-neutral-500 dark:text-white dark:hover:text-neutral-300"
          target="_blank"
          rel="noopener noreferrer"
          tabindex="0"
          v-text="media.file_name"
        />
        <span
          class="ml-2 text-sm text-neutral-500 dark:text-neutral-300"
          v-text="formatBytes(media.size)"
        />
        <p
          class="text-sm text-neutral-500 dark:text-neutral-300"
          v-text="localizedDateTime(media.created_at)"
        />
      </div>
      <div class="block shrink-0 md:hidden md:group-hover:block">
        <div class="flex items-center space-x-2">
          <a
            :href="media.download_url"
            class="text-neutral-500 focus:outline-none hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
          >
            <Icon icon="Download" class="h-5 w-5" />
          </a>
          <div v-if="authorizeDelete">
            <IButtonIcon icon="X" @click="deleteRequested(media)" />
          </div>
        </div>
      </div>
    </li>
  </ul>
</template>
<script setup>
import { ref, computed } from 'vue'
import { formatBytes } from '@/utils'
import { useDates } from '~/Core/resources/js/composables/useDates'
import Lightbox from '../Lightbox/Lightbox.vue'

const emit = defineEmits(['delete-requested'])

const props = defineProps({
  items: Array,
  authorizeDelete: { type: Boolean, default: false },
})

const activeLightboxImageIndex = ref(null)

const { localizedDateTime } = useDates()

const mediaImages = computed(() =>
  props.items.filter(media => media.aggregate_type === 'image')
)

const imagesUrlsForLightbox = computed(() =>
  mediaImages.value.map(media => media.preview_url)
)

const activeLightboxMedia = computed(() => {
  return mediaImages.value[activeLightboxImageIndex.value]
})

function findIndexForLightbox(previewUrl) {
  return props.items.findIndex(media => media.preview_url === previewUrl)
}

function deleteRequested(media) {
  emit('delete-requested', media)
}
</script>
