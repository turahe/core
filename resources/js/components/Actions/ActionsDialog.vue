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
  <div class="sm:flex sm:items-start">
    <div
      v-if="!hasFields"
      class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-danger-100 sm:mx-0 sm:h-10 sm:w-10"
    >
      <Icon icon="ExclamationTriangle" class="h-6 w-6 text-danger-600" />
    </div>
    <div
      :class="[
        { 'text-center sm:ml-4': !hasFields },
        'mt-3 w-full sm:mt-0 sm:text-left',
      ]"
    >
      <DialogTitle
        as="h3"
        :class="{ 'mt-2': !showMessage }"
        class="text-lg/6 font-medium text-neutral-600 dark:text-white"
      >
        {{ dialog.title }}
      </DialogTitle>

      <div v-if="showMessage" class="mt-2">
        <p class="text-sm text-neutral-500 dark:text-neutral-300">
          {{ dialog.message }}
        </p>
      </div>

      <FieldsGenerator
        v-if="hasFields"
        :form-id="form.formId"
        class="mt-4"
        :is-floating="true"
        view="internal"
        :fields="dialog.fields"
      />
    </div>
  </div>

  <div class="mt-5 sm:mt-4 sm:flex" :class="{ 'sm:ml-10 sm:pl-4': !hasFields }">
    <IButton
      :variant="dialog.action.destroyable ? 'danger' : 'secondary'"
      :disabled="executing"
      :loading="executing"
      :text="$t('core::app.confirm')"
      class="w-full sm:w-auto"
      @click="runAction"
    />
    <IButton
      variant="white"
      class="mt-3 w-full sm:ml-3 sm:mt-0 sm:w-auto"
      :text="$t('core::app.cancel')"
      @click="cancel"
    />
  </div>
</template>
<script setup>
import { ref, computed } from 'vue'
import { useFieldsForm } from '~/Core/resources/js/components/Fields/useFieldsForm'
import { DialogTitle } from '@headlessui/vue'

const props = defineProps({
  close: Function,
  cancel: Function,
  dialog: { type: Object, required: true },
})

const executing = ref(false)

const { form } = useFieldsForm({ ids: [] })

const hasFields = computed(
  () => props.dialog.fields && props.dialog.fields.isNotEmpty()
)

const showMessage = computed(() => !hasFields.value && props.dialog.message)

function runAction() {
  hasFields.value && props.dialog.fields.fill(form)
  form.fill('ids', props.dialog.ids)
  executing.value = true

  form
    .post(`${props.dialog.endpoint}`, {
      params: props.dialog.queryString,
    })
    .then(data => {
      props.dialog.resolve({
        form: form,
        response: data,
      })
      props.close()
    })
    .finally(() => (executing.value = false))
}
</script>
