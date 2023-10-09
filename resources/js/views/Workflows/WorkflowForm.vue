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
  <form @submit.prevent="submit">
    <div class="mx-auto max-w-2xl p-4">
      <h2
        class="mb-4 text-lg font-medium text-neutral-700 dark:text-neutral-200"
        v-text="workflow.title"
      />
      <IFormGroup
        label-for="title"
        :label="$t('core::workflow.title')"
        required
      >
        <IFormInput v-model="form.title" id="title" />
        <IFormError v-text="form.getError('title')" />
      </IFormGroup>
      <IFormGroup
        class="mb-4"
        label-for="description"
        :label="$t('core::workflow.description')"
        optional
      >
        <IFormTextarea v-model="form.description" rows="2" id="description" />
        <IFormError v-text="form.getError('description')" />
      </IFormGroup>
      <IFormGroup :label="$t('core::workflow.when')" required>
        <ICustomSelect
          input-id="trigger"
          :clearable="false"
          label="name"
          @option:selected="handleTriggerChange"
          v-model="trigger"
          :options="triggers"
        >
        </ICustomSelect>
        <IFormError v-text="form.getError('trigger_type')" />
      </IFormGroup>
      <IFormGroup
        v-if="hasChangeField"
        :label="$t('core::workflow.field_change_to')"
        required
      >
        <FieldsGenerator
          :form-id="form.formId"
          :fields="fields"
          :view="workflow.id ? 'update' : 'create'"
          :only="trigger.change_field.attribute"
        />
      </IFormGroup>
      <IFormGroup
        v-if="trigger"
        :class="{ 'mt--3': hasChangeField }"
        :label="$t('core::workflow.then')"
        required
      >
        <ICustomSelect
          v-if="trigger"
          input-id="action"
          :clearable="false"
          @option:selected="handleActionChange"
          v-model="action"
          label="name"
          :options="trigger.actions"
        >
        </ICustomSelect>
        <IFormError v-text="form.getError('action_type')" />

        <MailPlaceholders
          v-if="action"
          class="mt-3"
          :placeholders="action.placeholders"
        />
      </IFormGroup>
      <IFormGroup v-if="hasActionFields">
        <FieldsGenerator
          :form-id="form.formId"
          :view="workflow.id ? 'update' : 'create'"
          :fields="fields"
          :except="hasChangeField ? trigger.change_field.attribute : []"
        />
      </IFormGroup>
    </div>
    <div class="bg-neutral-50 px-4 py-3 dark:bg-neutral-900">
      <div class="flex items-center justify-end">
        <IFormToggle
          class="mr-4 border-r border-neutral-200 pr-4 dark:border-neutral-700"
          v-model="form.is_active"
          :label="$t('core::app.active')"
        />

        <IButton
          @click="cancel"
          variant="secondary"
          :text="$t('core::app.cancel')"
        />

        <IButton
          @click="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
          class="ml-2"
        />
      </div>
    </div>
  </form>
</template>
<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import MailPlaceholders from '~/Core/resources/js/components/MailPlaceholders.vue'
import {
  purgeCache,
  useFieldsForm,
} from '~/Core/resources/js/components/Fields/useFieldsForm'
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'
import { useResourceFields } from '~/Core/resources/js/composables/useResourceFields'
import { useI18n } from 'vue-i18n'

const emit = defineEmits(['update:workflow', 'delete-requested', 'hide'])

const props = defineProps({
  show: { type: Boolean, default: false },
  workflow: { required: true, type: Object },
  triggers: { required: true, type: Array },
})

const { t } = useI18n()
const { fields } = useResourceFields()
const { form } = useFieldsForm(fields)

// Selected trigger
const trigger = ref(null)

// Selected action
const action = ref(null)

/**
 * Watch the action change
 * We need to remove the old fields and add the new ones
 * in the same time keeps the CHANGEFIELD in the DOM to not loose
 * the FILL method
 */
watch(action, (newVal, oldVal) => {
  // Remove any previous fields
  if (oldVal && oldVal.fields) {
    oldVal.fields.forEach(field => {
      // We don't remove the change field as this field is trigger based
      if (
        !hasChangeField.value ||
        (hasChangeField.value &&
          field.attribute !== trigger.value.change_field.attribute)
      ) {
        fields.value.remove(field.attribute)
      }
    })
  }

  // Add any new fields
  if (newVal && newVal.fields) {
    newVal.fields.forEach(field => {
      // Check if exists, it may exists if added before
      if (!fields.value.find(field.attribute)) {
        fields.value.push(cloneDeep(field))
      }
    })
  }
})

const hasChangeField = computed(() => {
  if (!trigger.value) {
    return false
  }

  return Boolean(trigger.value.change_field)
})

const hasActionFields = computed(() => {
  if (!action.value) {
    return false
  }

  return action.value.fields.length > 0
})

function update() {
  form.put(`/workflows/${props.workflow.id}`).then(data => {
    emit('update:workflow', data)
    emit('hide')
    Innoclapps.success(t('core::workflow.updated'))
  })
}

function create() {
  form.post('/workflows').then(data => {
    emit('update:workflow', data)
    Innoclapps.success(t('core::workflow.created'))
  })
}

async function submit() {
  // Wait for the active switch to update
  await nextTick()

  form.hydrate()

  props.workflow.id ? update() : create()
}

function handleTriggerChange(trigger) {
  action.value = null
  form.errors.clear('trigger_type')

  setFormData({
    title: form.title,
    description: form.description,
    is_active: form.is_active,
  })

  fields.value.set(hasChangeField.value ? [trigger.change_field] : [])

  form.fill('trigger_type', trigger.identifier)
}

function handleActionChange(action) {
  form.errors.clear('action_type')

  form.fill('action_type', action.identifier || null)
}

function cancel() {
  if (props.workflow.id) {
    emit('hide')
    return
  }

  requestDelete()
}

function requestDelete() {
  emit('delete-requested', props.workflow)
}

function setFormData(data = {}) {
  form.clear().set({
    trigger_type: data.trigger || null,
    action_type: data.action || null,
    title: data.title || null,
    description: data.description || null,
    is_active: data.is_active || true,
  })
}

function setWorkflowForUpdate() {
  trigger.value = find(props.triggers, [
    'identifier',
    props.workflow.trigger_type,
  ])

  action.value = find(trigger.value.actions, [
    'identifier',
    props.workflow.action_type,
  ])

  // Set the fields for update
  let updateFields = hasActionFields.value ? cloneDeep(action.value.fields) : []

  if (hasChangeField.value) {
    updateFields.push(trigger.value.change_field)
  }

  // Avoid duplicate field id's as the fields
  // are inline for all workflows
  updateFields = updateFields.map(field => {
    field.id = field.attribute + '-' + props.index
    return field
  })

  if (fields.value.isEmpty()) {
    fields.value.set(updateFields).populate(props.workflow.data)
  } else {
    fields.value.setFormValues(props.workflow.data)
  }
}

onMounted(() => {
  setFormData({
    title: props.workflow.title,
    description: props.workflow.description,
    is_active: props.workflow.is_active,
    trigger: props.workflow.trigger_type,
    action: props.workflow.action_type,
  })

  if (props.workflow.id) {
    setWorkflowForUpdate()
  }
})

onUnmounted(purgeCache)
</script>
