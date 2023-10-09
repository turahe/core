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
    v-bind="$attrs"
    :class="{ 'resize-y': isResizeable }"
    :style="{
      height: fieldsHeight ? `${fieldsHeight}px` : null,
    }"
    ref="wrapperRef"
  >
    <div class="grid grid-cols-12 gap-x-4">
      <div
        v-for="field in iterableFields"
        :key="field.attribute"
        :class="[
          field.colClass !== false ? field.colClass || 'col-span-12' : '',
          field.displayNone || (collapsed && field.collapsed) ? 'hidden' : '',
        ]"
      >
        <component
          :field="field"
          :view="view"
          :via-resource="viaResource"
          :via-resource-id="viaResourceId"
          :is-floating="isFloating"
          :form-id="formId"
          :is="field.component"
        />
        <slot :name="`after-${field.attribute}-field`" :field="field"></slot>
      </div>
    </div>
  </div>
  <slot :fields="iterableFields" name="after"></slot>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'
import castArray from 'lodash/castArray'
import debounce from 'lodash/debounce'
import elementResizeEvent from 'element-resize-event'
import { unbind as unbindElementResizeEvent } from 'element-resize-event'
import { useGate } from '~/Core/resources/js/composables/useGate'
import { useResource } from '~/Core/resources/js/composables/useResource'
import Fields from '~/Core/resources/js/components/Fields/Fields'

const props = defineProps({
  fields: { required: true, type: [Array, Fields], default: () => [] },
  formId: { required: true, type: String },
  collapsed: Boolean,

  resizeable: Boolean,
  // Required when resizeable
  resourceName: String,

  viaResource: String,
  viaResourceId: Number,
  except: [Array, String],
  only: [Array, String],
  view: { required: true, type: String },
  isFloating: { default: false, type: Boolean },
  focusFirst: { type: Boolean, default: false },
})

const { gate } = useGate()

let timeoutClear = null
const wrapperRef = ref(null)
const fieldsHeight = ref(null)

const { singularName: resourceSingular } = useResource(props.resourceName)

const isResizeable = computed(() => props.resizeable && gate.isSuperAdmin())

const onlyFields = computed(() => (props.only ? castArray(props.only) : []))

const exceptFields = computed(() =>
  props.except ? castArray(props.except) : []
)

const iterableFields = computed(() => {
  if (!props.fields) {
    return []
  }

  let fields =
    props.fields instanceof Fields ? props.fields.all() : props.fields

  if (props.only) {
    return fields.filter(
      field => onlyFields.value.indexOf(field.attribute) > -1
    )
  } else if (props.except) {
    return fields.filter(
      field => exceptFields.value.indexOf(field.attribute) === -1
    )
  }

  return fields
})

const updateResourceFieldsHeight = debounce(function () {
  Innoclapps.request()
    .post('/settings', {
      [resourceSingular.value + '_fields_height']:
        wrapperRef.value.offsetHeight,
    })
    .then(() => (fieldsHeight.value = wrapperRef.value.offsetHeight))
}, 500)

function createResizableEvent() {
  elementResizeEvent(wrapperRef.value, updateResourceFieldsHeight)
}

function destroyResizableEvent() {
  if (isResizeable.value) {
    unbindElementResizeEvent(wrapperRef.value)
  }
}

function prepareComponent() {
  if (isResizeable.value) {
    nextTick(createResizableEvent)
  }
}

function focusToFirstFocusableElement() {
  const focusAbleInputs = [
    'date',
    'datetime-local',
    'email',
    'file',
    'month',
    'number',
    'password',
    'range',
    'search',
    'tel',
    'text',
    'time',
    'url',
    'week',
  ]

  const input = wrapperRef.value.querySelector('div:first-child input')
  const textarea = wrapperRef.value.querySelector('div:first-child textarea')

  if (input && focusAbleInputs.indexOf(input.getAttribute('type')) > -1) {
    input.focus()
  } else if (textarea) {
    textarea.focus()
  }
}

if (isResizeable.value) {
  fieldsHeight.value = Innoclapps.config(
    `${resourceSingular.value}_fields_height`
  )
}

onMounted(prepareComponent)

onBeforeUnmount(destroyResizableEvent)

onMounted(() => {
  if (props.focusFirst) {
    timeoutClear = setTimeout(focusToFirstFocusableElement, 600)
  }
})

onBeforeUnmount(() => {
  timeoutClear && clearTimeout(timeoutClear)
})
</script>
