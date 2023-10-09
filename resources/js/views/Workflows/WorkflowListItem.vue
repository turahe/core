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
  <WorkflowForm
    v-if="editOrCreate"
    :workflow="workflow"
    :triggers="triggers"
    @hide="editOrCreate = false"
    @update:workflow="$emit('update:workflow', $event)"
    @delete-requested="$emit('delete-requested', $event)"
  />

  <div :class="{ 'opacity-70': !workflow.is_active, hidden: editOrCreate }">
    <div class="flex items-center px-4 py-4 sm:px-6">
      <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
        <div class="truncate">
          <div class="flex">
            <a
              href="#"
              class="link flex items-center truncate text-sm font-medium"
              @click.prevent="editOrCreate = true"
            >
              <span class="mr-1">
                {{ workflow.title }}
              </span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M13 7l5 5m0 0l-5 5m5-5H6"
                />
              </svg>
            </a>
          </div>
          <div class="mt-2 flex">
            <div class="flex items-center space-x-4 text-sm text-neutral-500">
              <p class="text-neutral-800 dark:text-neutral-300">
                {{
                  $t('core::workflow.total_executions', {
                    total: workflow.total_executions,
                  })
                }}
              </p>
              <p
                v-if="workflow.created_at"
                class="text-sm text-neutral-800 dark:text-white"
              >
                {{ $t('core::app.created_at') }}:
                {{ localizedDateTime(workflow.created_at) }}
              </p>
            </div>
          </div>
        </div>
        <div class="mt-4 shrink-0 sm:ml-5 sm:mt-0">
          <IFormToggle
            :label="$t('core::app.active')"
            @change="handleWorkflowActiveChangeEvent"
            :modelValue="workflow.is_active"
          />
        </div>
      </div>
      <div class="ml-5 shrink-0">
        <IMinimalDropdown>
          <IDropdownItem
            @click="editOrCreate = true"
            :text="$t('core::app.edit')"
          />

          <IDropdownItem
            @click="requestDelete"
            :text="$t('core::app.delete')"
          />
        </IMinimalDropdown>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { useDates } from '~/Core/resources/js/composables/useDates'
import WorkflowForm from './WorkflowForm.vue'
import { useI18n } from 'vue-i18n'

const emit = defineEmits(['update:workflow', 'delete-requested'])

const props = defineProps({
  index: { required: true, type: Number },
  triggers: { required: true, type: Array },
  workflow: { required: true, type: Object },
})

const { t } = useI18n()
const { localizedDateTime } = useDates()

const editOrCreate = ref(false)

function handleWorkflowActiveChangeEvent(value) {
  Innoclapps.request()
    .put(`/workflows/${props.workflow.id}`, {
      ...{ ...props.workflow, ...{ data: {} } },
      ...props.workflow.data,
      ...{ is_active: value },
    })
    .then(({ data }) => {
      emit('update:workflow', data)
      Innoclapps.success(t('core::workflow.updated'))
    })
}

function requestDelete() {
  emit('delete-requested', props.workflow)
}

if (!props.workflow.id) {
  editOrCreate.value = true
}
</script>
