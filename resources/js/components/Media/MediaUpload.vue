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
    <IAlert
      v-for="(error, index) in errors"
      :key="index"
      show
      variant="danger"
      class="mb-4"
    >
      {{ error }}
    </IAlert>

    <slot></slot>

    <MediaUploadOutputList
      v-if="showOutput"
      :files="files"
      @remove-requested="remove"
    />

    <div :class="[wrapperClasses, 'relative']">
      <slot name="drop-placeholder" :upload="$refs.uploadRef">
        <div
          v-show="$refs.uploadRef && $refs.uploadRef.dropActive"
          class="absolute inset-0 z-10 flex items-center justify-center rounded-md bg-neutral-200"
          v-t="'core::app.drop_files'"
        />
      </slot>

      <FileUpload
        ref="uploadRef"
        :headers="{
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': CSRFToken,
        }"
        :class="styleClasses"
        :disabled="$refs.uploadRef && $refs.uploadRef.active"
        :name="name"
        :multiple="multiple"
        :extensions="extensions"
        :accept="accept"
        :data="requestData"
        v-model="files"
        :drop="drop"
        :post-action="actionUrl"
        :input-id="inputId"
        @update:modelValue="$emit('update:modelValue', $event)"
        @input-file="inputFile"
        @input-filter="inputFilter"
      >
        <Icon
          v-if="icon && !($refs.uploadRef && $refs.uploadRef.active)"
          :icon="icon"
          class="mr-2 h-4 w-4 text-current"
        />

        <ISpinner
          v-if="$refs.uploadRef && $refs.uploadRef.active"
          class="-mt-1 mr-2 inline-flex h-4 w-4 text-current"
        />
        <slot name="upload-text">
          {{ selectButtonUploadText }}
        </slot>
      </FileUpload>
      <div class="ml-2 flex items-center space-x-2">
        <slot name="upload-button" :upload="$refs.uploadRef">
          <button
            v-if="showUploadButton && !automaticUpload"
            type="button"
            @click="$refs.uploadRef.active = true"
            :disabled="files.length === 0"
            class="inline-flex cursor-pointer items-center rounded-full bg-primary-50 px-5 py-2 text-sm font-medium text-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50 disabled:pointer-events-none disabled:opacity-60 hover:bg-primary-100"
          >
            <Icon icon="CloudArrowUp" class="mr-2 h-4 w-4 text-current" />
            {{ uploadButtonText }}
          </button>
        </slot>
        <button
          v-show="allowCancel && $refs.uploadRef && $refs.uploadRef.active"
          type="button"
          @click="$refs.uploadRef.active = false"
          class="inline-flex items-center rounded-full bg-danger-50 px-5 py-2 text-sm font-medium text-danger-800 focus:outline-none focus:ring-2 focus:ring-danger-600 focus:ring-offset-2 focus:ring-offset-danger-50 hover:bg-danger-100"
          v-t="'core::app.cancel'"
        />
        <button
          v-show="
            files.length > 0 && (!$refs.uploadRef || !$refs.uploadRef.active)
          "
          type="button"
          @click="clear"
          class="inline-flex items-center rounded-full bg-neutral-50 px-5 py-2 text-sm font-medium text-neutral-800 focus:outline-none focus:ring-2 focus:ring-neutral-600 focus:ring-offset-2 focus:ring-offset-neutral-50 hover:bg-neutral-100"
          v-t="'core::app.clear'"
        />
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, nextTick, defineAsyncComponent } from 'vue'
const FileUpload = defineAsyncComponent(() => import('vue-upload-component'))
import findIndex from 'lodash/findIndex'
import MediaUploadOutputList from './MediaUploadOutputList.vue'
import { useI18n } from 'vue-i18n'

const emit = defineEmits([
  'update:modelValue',
  'file-accepted',
  'file-uploaded',
  'clear',
])

const props = defineProps({
  modelValue: {},
  icon: { type: [String, Boolean], default: 'CursorClick' },
  inputId: { default: 'media', type: String },
  actionUrl: String,
  // NOTE, drop is set to false as it's causing memory leaks
  // https://github.com/lian-yue/vue-upload-component/issues/294
  drop: { type: Boolean, default: false },
  wrapperClasses: {
    type: [Object, Array, String],
    default: 'flex items-center',
  },
  name: { default: 'file', type: String },
  extensions: [Array, String],
  accept: { type: String, default: undefined },
  uploadText: String,
  selectFileText: String,
  allowCancel: { type: Boolean, default: true },
  showUploadButton: { type: Boolean, default: true },
  showOutput: { default: true, type: Boolean },
  automaticUpload: { default: true, type: Boolean },
  multiple: { default: true, type: Boolean },
  requestData: { type: Object, default: () => ({}) },
  styleClasses: {
    type: [Object, Array, String],
    default:
      '!flex items-center rounded-full px-5 py-2 text-sm font-medium bg-primary-50 text-primary-800 hover:bg-primary-100 focus:ring-offset-primary-50 focus:ring-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer',
  },
})

const { t } = useI18n()
const uploadRef = ref(null)
const CSRFToken = Innoclapps.csrfToken()
const files = ref([])
const errors = ref([])

const uploadButtonText = computed(
  () => props.uploadText || t('core::app.upload')
)

const selectButtonUploadText = computed(
  () => props.selectFileText || t('core::app.select_file')
)

function handleResponse(xhr) {
  // Nginx 413 Request Entity Too Large
  if (xhr.status === 413) {
    Innoclapps.error(t('core::app.file_too_large'))

    return
  }

  let response = JSON.parse(xhr.response)
  let isSuccess = xhr.status < 400

  if (response.message) {
    if (isSuccess) {
      Innoclapps.success(response.message)
    } else {
      Innoclapps.error(response.message)
    }
  }

  if (xhr.status === 422) {
    errors.value = response.errors
  }

  return response
}

/**
 * Remove file from the queue
 */
function remove(index) {
  files.value.splice(index, 1)
  emit('update:modelValue', files.value)
}

function clear() {
  uploadRef.value.clear()
  errors.value = []
  emit('clear')
}

function validateExtensions(file) {
  if (!props.extensions) {
    return true
  }

  let validateExtensions = props.extensions

  if (typeof validateExtensions == 'array') {
    validateExtensions = validateExtensions.join('|')
  } else if (typeof validateExtensions == 'string') {
    validateExtensions = validateExtensions.replace(',', '|')
  }

  var regex = RegExp('.(' + validateExtensions + ')', 'i')

  if (!regex.test(file.name)) {
    Innoclapps.error(
      t('validation.mimes', {
        attribute: t('core::app.file').toLowerCase(),
        values: [validateExtensions],
      })
    )

    return false
  }

  return true
}

function isNewFile(newFile, oldFile) {
  return newFile && !oldFile
}

function isUpdatedFile(newFile, oldFile) {
  return newFile && oldFile
}

function shouldStartUpload(newFile, oldFile) {
  return newFile.active !== oldFile.active
}

/**
 * A file change detected
 */
function inputFile(newFile, oldFile) {
  if (isNewFile(newFile, oldFile)) {
    // console.log('add file')
    // Add file
  }

  if (isUpdatedFile(newFile, oldFile)) {
    // Update file
    // console.log('update file')
    // Start upload
    if (shouldStartUpload(newFile, oldFile)) {
      // console.log('Start upload', newFile.active, newFile)
    }

    // Upload progress
    if (newFile.progress !== oldFile.progress) {
      // console.log('progress', newFile.progress, newFile)
    }

    // Upload error
    if (newFile.error !== oldFile.error) {
      if (newFile.xhr.response /* perhaps canceled */) {
        handleResponse(newFile.xhr)
      }
    }

    // Uploaded successfully
    if (newFile.success !== oldFile.success) {
      // console.log('success', newFile.success, newFile)
      emit('file-uploaded', handleResponse(newFile.xhr))
      remove(findIndex(files.value, ['name', newFile.name]))
    }
  }

  if (!props.automaticUpload) {
    return
  }

  if (
    Boolean(newFile) !== Boolean(oldFile) ||
    oldFile.error !== newFile.error
  ) {
    if (!uploadRef.value.active && newFile && !newFile.xhr) {
      // console.log('Automatic upload')
      uploadRef.value.active = true
    }
  }
}

const inputFilter = function (newFile, oldFile, prevent) {
  if (newFile && !oldFile) {
    // Extentesion validator
    if (!validateExtensions(newFile)) {
      return prevent()
    }

    // File size validator
    if (
      newFile.size >= 0 &&
      newFile.size > Innoclapps.config('max_upload_size')
    ) {
      Innoclapps.error('File too big')
      return prevent()
    }

    newFile.blob = ''
    let URL = window.URL || window.webkitURL
    if (URL && URL.createObjectURL) {
      newFile.blob = URL.createObjectURL(newFile.file)
    }

    // this.file = newFile
    nextTick(() => emit('file-accepted', newFile))
  }
}
</script>
