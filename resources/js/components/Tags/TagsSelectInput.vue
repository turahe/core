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
  <ICustomSelect
    :model-value="modelValue"
    @update:modelValue="emitUpdateModelValue"
    :multiple="true"
    :options="options"
    :reduce="tag => tag.name"
    :simple="simple"
    :searchable="!showForm"
    :placeholder="simple ? $t('core::tags.search') : undefined"
    :toggle-icon="
      simple && modelValue && modelValue.length === 0 ? 'Tag' : 'ChevronDown'
    "
    :list-wrapper-class="simple ? 'min-w-[340px]' : undefined"
    label="name"
    :list-class="showForm ? 'hidden' : undefined"
    :reorderable="$gate.isSuperAdmin()"
    @update:draggable="handleTagsReordered"
  >
    <template #option-actions="{ index }">
      <a
        v-if="$gate.isSuperAdmin()"
        href="#"
        class="text-neutral-600 dark:text-neutral-200"
        @click.prevent="prepareEdit(options[index])"
      >
        <Icon icon="Pencil" class="h-4 w-4" />
      </a>
    </template>

    <template v-if="showForm" #header>
      <div
        class="border-b border-neutral-200 bg-neutral-50 px-5 py-3 text-sm font-medium text-neutral-800 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100"
      >
        {{
          tagBeingCreated ? $t('core::tags.new_tag') : $t('core::tags.edit_tag')
        }}
      </div>

      <div class="px-5 py-4">
        <IFormGroup
          label-for="tag_name"
          :label="$t('core::tags.tag_name')"
          required
        >
          <IFormInput id="tag_name" name="tag_name" v-model="tagForm.name" />
          <IFormError v-text="tagForm.getError('name')" />
        </IFormGroup>

        <IFormLabel :label="$t('core::tags.color')" class="mb-1" required />

        <IColorSwatches
          :allow-remove="false"
          :swatches="swatches"
          v-model="tagForm.swatch_color"
        />

        <IFormError v-text="tagForm.getError('swatch_color')" />
      </div>

      <div
        class="border-t border-neutral-200 bg-neutral-50 px-5 py-2 dark:border-neutral-600 dark:bg-neutral-800"
      >
        <div class="flex">
          <IButton
            v-if="!tagBeingCreated && $gate.isSuperAdmin()"
            variant="danger"
            :icon="deletitionConfirmed === null ? 'Trash' : null"
            :text="
              deletitionConfirmed === null ? undefined : $t('core::app.confirm')
            "
            size="sm"
            @click="deleteTag(tagBeingEdited)"
          />
          <div class="ml-auto space-x-2">
            <IButton
              variant="white"
              :text="$t('core::app.cancel')"
              size="sm"
              @click="hideForm"
            />
            <IButton
              variant="primary"
              :text="$t('core::app.save')"
              @click="tagBeingCreated ? createTag() : updateTag()"
              :loading="tagForm.busy"
              :disabled="tagForm.busy"
              size="sm"
            />
          </div>
        </div>
      </div>
    </template>

    <template #footer v-if="$gate.isSuperAdmin()">
      <a
        href="#"
        v-show="!showForm"
        @click.prevent="tagBeingCreated = true"
        class="link block border-t border-neutral-200 px-4 py-2 text-sm hover:bg-neutral-50 dark:border-neutral-600 dark:hover:bg-neutral-700"
      >
        + {{ $t('core::tags.add_new') }}
      </a>
    </template>
  </ICustomSelect>
</template>
<script setup>
import { ref, computed } from 'vue'
import { useForm } from '../../composables/useForm'
import { useTags } from './useTags'
import cloneDeep from 'lodash/cloneDeep'

const props = defineProps({
  modelValue: Array,
  type: String,
  simple: Boolean,
})

const emit = defineEmits(['update:modelValue'])

const {
  tagsByDisplayOrder,
  findTagsByType,
  removeTag,
  setTag,
  findTagById,
  setTags,
  addTag,
} = useTags()

const options = computed(() => {
  if (props.type) {
    return findTagsByType(props.type)
  }

  return tagsByDisplayOrder.value
})

const swatches = Innoclapps.config('favourite_colors').slice(0, -2)

const { form: tagForm } = useForm(
  {
    name: '',
    swatch_color: swatches[1],
  },
  {
    resetOnSuccess: true,
  }
)

const deletitionConfirmed = ref(null)
const tagBeingCreated = ref(false)
const tagBeingEdited = ref(null)

const showForm = computed(
  () => tagBeingCreated.value || Boolean(tagBeingEdited.value)
)

function prepareEdit(tag) {
  tagBeingEdited.value = tag.id
  tagForm.fill('name', tag.name)
  tagForm.fill('swatch_color', tag.swatch_color)
}

function handleTagsReordered(tags) {
  setTags(
    tags.map((tag, idx) => ({
      ...tag,
      ...{ display_order: idx + 1 },
    }))
  )

  Innoclapps.request().post(
    '/tags/order',
    tags.map((tag, index) => ({
      id: tag.id,
      display_order: index + 1,
    }))
  )
}

function hideForm() {
  tagBeingCreated.value = false
  tagBeingEdited.value = null
  deletitionConfirmed.value = null
  tagForm.reset()
}

function createTag() {
  tagForm.post(`/tags${props.type ? `/${props.type}` : ''}`).then(tag => {
    if (findTagById(tag.id)) {
      setTag(tag.id, tag)
    } else {
      addTag(tag)
    }
    hideForm()
  })
}

function updateTag() {
  const oldTagName = findTagById(tagBeingEdited.value).name

  tagForm.put(`/tags/${tagBeingEdited.value}`).then(tag => {
    setTag(tag.id, tag)

    let tagInValueIndex = props.modelValue.findIndex(
      t => (t?.name || t) === oldTagName
    )

    if (tagInValueIndex !== -1) {
      let oldTagValue = cloneDeep(props.modelValue[tagInValueIndex])

      if (typeof oldTagValue === 'string') {
        oldTagValue = tag.name
      } else {
        oldTagValue.name = tag.name
      }
      let newModelValue = cloneDeep(props.modelValue)
      newModelValue[tagInValueIndex] = oldTagValue
      emitUpdateModelValue(newModelValue)
    }
    hideForm()
  })
}

function emitUpdateModelValue(data) {
  emit(
    'update:modelValue',
    data.map(tag => {
      if (typeof tag === 'string') {
        return tag
      }
      return tag.name
    })
  )
}
async function deleteTag(id) {
  if (deletitionConfirmed.value === null) {
    deletitionConfirmed.value = false
    return
  }

  let tag = findTagById(id)
  await Innoclapps.request().delete(`/tags/${id}`)

  deletitionConfirmed.value = null

  let tagInValueIndex = props.modelValue.findIndex(
    t => (t?.name || t) === tag.name
  )

  if (tagInValueIndex !== -1) {
    emitUpdateModelValue(
      props.modelValue.filter(t => (t?.name || t) !== tag.name)
    )
  }
  removeTag(id)
  hideForm()
}
</script>
