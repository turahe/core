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
    <div v-show="!edit" class="flex items-center space-x-4">
      <div v-if="hasImage">
        <slot name="image" :src="activeImageSrc">
          <IAvatar size="lg" class="mb-2" :src="activeImageSrc" />
        </slot>
      </div>
      <!-- NOTE, drop is set to false as it's causing memory leaks -->
      <!-- https://github.com/lian-yue/vue-upload-component/issues/294 -->
      <div class="flex space-x-2">
        <FileUpload
          extensions="jpg,jpeg,png"
          accept="image/png,image/jpeg"
          :name="name"
          :input-id="name"
          :headers="{
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRFToken,
          }"
          class="!flex cursor-pointer items-center rounded-full bg-primary-50 px-5 py-2 text-sm font-medium text-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50 hover:bg-primary-100"
          :post-action="uploadUrl"
          :drop="false"
          v-model="tmpFile"
          @input-filter="inputFilter"
          @input-file="inputFile"
          ref="uploadRef"
        >
          <Icon
            v-if="!(uploadRef && uploadRef.active)"
            icon="CursorClick"
            class="mr-2 h-4 w-4 text-current"
          />
          <ISpinner
            v-if="uploadRef && uploadRef.active"
            class="-mt-1 mr-2 inline-flex h-4 w-4 text-current"
          />
          {{ chooseTextLocal }}
        </FileUpload>
        <button
          v-if="hasImage && showDelete"
          type="button"
          @click="remove"
          class="inline-flex items-center rounded-full bg-danger-50 px-5 py-2 text-sm font-medium text-danger-800 focus:outline-none focus:ring-2 focus:ring-danger-600 focus:ring-offset-2 focus:ring-offset-danger-50 hover:bg-danger-100"
          v-t="'core::app.remove'"
        />
      </div>
    </div>
    <div v-show="hasTemporaryFile && edit">
      <div class="flex space-x-1">
        <IButton
          type="submit"
          variant="secondary"
          @click="editSave"
          :text="saveTextLocal"
        />
        <IButton
          variant="white"
          @click="() => uploadRef.clear()"
          :text="cancelTextLocal"
        />
      </div>
      <div
        v-if="hasTemporaryFile"
        class="mt-2 w-full overflow-hidden rounded-md"
      >
        <img ref="editedFileRef" :src="tmpFile[0].url" />
      </div>
    </div>
  </div>
</template>
<script setup>
import {
  ref,
  computed,
  watch,
  nextTick,
  onBeforeUnmount,
  defineAsyncComponent,
} from 'vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import { useI18n } from 'vue-i18n'
const FileUpload = defineAsyncComponent(() => import('vue-upload-component'))

const emit = defineEmits(['success', 'cleared'])
const props = defineProps({
  showDelete: { type: Boolean, default: true },
  chooseText: String,
  saveText: String,
  cancelText: String,
  image: String,
  uploadUrl: { type: String, required: true },
  name: { type: String, default: 'image' },
  cropperOptions: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

let cropper = null

const defaultCropperOptions = {
  aspectRatio: 1 / 1,
  viewMode: 1,
}

const CSRFToken = Innoclapps.csrfToken()

const edit = ref(false)
const tmpFile = ref([])

const uploadRef = ref(null)
const editedFileRef = ref(null)

watch(edit, newVal => {
  if (newVal) {
    nextTick(function () {
      if (!editedFileRef.value) {
        return
      }

      cropper = new Cropper(
        editedFileRef.value,
        Object.assign({}, defaultCropperOptions, props.cropperOptions)
      )
    })
  } else if (cropper) {
    cropper.destroy()
    cropper = null
  }
})

const chooseTextLocal = computed(
  () => props.chooseText || t('core::app.choose_image')
)

const saveTextLocal = computed(() => props.saveText || t('core::app.upload'))

const cancelTextLocal = computed(
  () => props.cancelText || t('core::app.cancel')
)

const hasImage = computed(() => hasTemporaryFile.value || props.image)

const activeImageSrc = computed(() =>
  hasTemporaryFile.value ? tmpFile.value[0].url : props.image
)

const hasTemporaryFile = computed(() => tmpFile.value.length > 0)

function remove() {
  edit.value = false
  tmpFile.value = []
  emit('cleared')
}

function editSave() {
  edit.value = false
  let oldFile = tmpFile.value[0]
  let binStr = atob(
    cropper.getCroppedCanvas().toDataURL(oldFile.type).split(',')[1]
  )
  let arr = new Uint8Array(binStr.length)
  for (let i = 0; i < binStr.length; i++) {
    arr[i] = binStr.charCodeAt(i)
  }
  let file = new File([arr], oldFile.name, {
    type: oldFile.type,
  })
  uploadRef.value.update(oldFile.id, {
    file,
    type: file.type,
    size: file.size,
    active: true,
  })
}

function inputFile(newFile, oldFile, prevent) {
  if (newFile && !oldFile) {
    nextTick(function () {
      edit.value = true
    })
  }

  if (!newFile && oldFile) {
    edit.value = false
  }

  if (newFile && oldFile) {
    // Uploaded
    if (newFile.success !== oldFile.success) {
      emit('success', newFile.response)
    }

    // Error
    if (newFile.error !== oldFile.error) {
      // Nginx 413 Request Entity Too Large
      if (newFile.xhr.status === 413) {
        Innoclapps.error(t('core::app.file_too_large'))
        tmpFile.value = []

        return
      }

      let response = JSON.parse(newFile.xhr.response)
      Innoclapps.error(response.message)
    }
  }
}

function inputFilter(newFile, oldFile, prevent) {
  if (newFile && !oldFile) {
    if (!/\.(jpeg|png|jpg|gif|svg)$/i.test(newFile.name)) {
      Innoclapps.error(
        t('validation.image', {
          attribute: newFile.name,
        })
      )

      return prevent()
    }
  }
  if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
    newFile.url = ''
    let URL = window.URL || window.webkitURL
    if (URL && URL.createObjectURL) {
      newFile.url = URL.createObjectURL(newFile.file)
    }
  }
}

onBeforeUnmount(() => {
  cropper && cropper.destroy()
})
</script>
