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
  <ICard
    no-body
    :header="$t('core::workflow.workflows')"
    :overlay="!componentReady"
  >
    <template #actions>
      <IButton
        v-show="hasDefinedWorkflows"
        @click="add"
        size="sm"
        icon="plus"
        :disabled="isWorkflowBeingCreated"
        :text="$t('core::workflow.create')"
      />
    </template>
    <div v-if="componentReady">
      <TransitionGroup
        v-if="hasDefinedWorkflows"
        name="flip-list"
        tag="ul"
        class="divide-y divide-neutral-200 dark:divide-neutral-700"
      >
        <li
          v-for="(workflow, index) in listWorkflows"
          :key="workflow.id || workflow.key"
        >
          <WorkflowListItem
            :index="index"
            :triggers="availableTriggers"
            v-model:workflow="workflows[index]"
            @delete-requested="destroy"
          />
        </li>
      </TransitionGroup>

      <div v-else class="p-7">
        <button
          type="button"
          @click="add"
          class="relative flex w-full flex-col items-center rounded-lg border-2 border-dashed border-neutral-300 p-6 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 hover:border-neutral-400 dark:border-neutral-400 dark:hover:border-neutral-300 sm:p-12"
        >
          <Icon
            icon="RocketLaunch"
            class="mx-auto h-12 w-12 text-primary-500 dark:text-primary-400"
          />

          <span
            class="mt-2 block font-medium text-neutral-900 dark:text-neutral-100"
            v-t="'core::workflow.create'"
          />

          <p
            v-t="'core::workflow.info'"
            class="mt-2 block max-w-2xl text-sm text-neutral-600 dark:text-neutral-300"
          />
        </button>
      </div>
    </div>
  </ICard>
</template>
<script setup>
import { ref, computed } from 'vue'
import { randomString } from '@/utils'
import WorkflowListItem from './WorkflowListItem.vue'
import findIndex from 'lodash/findIndex'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const availableTriggers = ref([])
const workflows = ref([])
const componentReady = ref(false)

const listWorkflows = computed(() =>
  workflows.value.sort(
    (a, b) => +b.is_active - +a.is_active || a.title.localeCompare(b.title)
  )
)

const isWorkflowBeingCreated = computed(
  () => workflows.value.filter(workflow => workflow.key).length > 0
)

const hasDefinedWorkflows = computed(() => workflows.value.length > 0)

function add() {
  workflows.value.unshift({
    key: randomString(),
    title: '',
    description: null,
    is_active: false,
    trigger_type: null,
    action_type: null,
  })
}

function destroy(workflow) {
  if (!workflow.id) {
    workflows.value.splice(findIndex(workflows.value, ['key', workflow.key]), 1)
  } else {
    Innoclapps.dialog()
      .confirm()
      .then(() => {
        makeDestroyRequest(workflow.id)
      })
  }
}

function makeDestroyRequest(id) {
  Innoclapps.request()
    .delete(`/workflows/${id}`)
    .then(({ data }) => {
      workflows.value.splice(findIndex(workflows.value, ['id', Number(id)]), 1)

      Innoclapps.success(t('core::workflow.deleted'))
    })
}

Promise.all([
  Innoclapps.request().get('/workflows'),
  Innoclapps.request().get('/workflows/triggers'),
]).then(responses => {
  workflows.value.push(...responses[0].data)
  availableTriggers.value = responses[1].data
  componentReady.value = true
})
</script>
