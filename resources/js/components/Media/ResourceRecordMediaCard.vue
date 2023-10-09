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
  <div
    :class="{
      'rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900':
        card,
    }"
  >
    <div :class="{ 'px-4 py-5 sm:px-6': card }">
      <slot name="heading">
        <h2 class="font-medium text-neutral-800 dark:text-white">
          {{ $t('core::app.attachments') }}
          <span
            v-if="total > 0"
            class="text-sm font-normal text-neutral-400"
            v-text="'(' + total + ')'"
          />
        </h2>
      </slot>
      <div :class="wrapperClass" v-if="show">
        <MediaItemsList
          :items="localMedia"
          :authorize-delete="authorizeDelete"
          @delete-requested="destroy"
        />
        <p
          v-show="!hasMedia"
          class="text-sm text-neutral-500 dark:text-neutral-300"
          v-t="'core::app.no_attachments'"
        />
        <div class="mt-3">
          <MediaUpload
            @file-uploaded="uploadedEventHandler"
            :input-id="
              'media-' +
              resourceName +
              '-' +
              resourceId +
              (isFloating ? '-floating' : '')
            "
            :action-url="`${$store.state.apiURL}/${resourceName}/${resourceId}/media`"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import MediaUpload from './MediaUpload.vue'
import MediaItemsList from './MediaItemsList.vue'
import orderBy from 'lodash/orderBy'

const emit = defineEmits(['deleted', 'uploaded'])

const props = defineProps({
  show: { default: true, type: Boolean },
  resourceName: { type: String, required: true },
  resourceId: { type: Number, required: true },
  media: { type: Array, required: true },
  authorizeDelete: { required: true, type: Boolean },
  isFloating: { type: Boolean, required: false },
  automaticUpload: { default: true, type: Boolean },
  card: { default: true, type: Boolean },
  wrapperClass: [String, Array, Object],
})

const localMedia = computed(() => {
  return orderBy(props.media, media => new Date(media.created_at), ['desc'])
})

const total = computed(() => {
  return props.media.length
})

const hasMedia = computed(() => total.value > 0)

function uploadedEventHandler(media) {
  emit('uploaded', media)
}

async function destroy(media) {
  await Innoclapps.dialog().confirm()
  await Innoclapps.request().delete(
    `/${props.resourceName}/${props.resourceId}/media/${media.id}`
  )

  emit('deleted', media)
}
</script>
