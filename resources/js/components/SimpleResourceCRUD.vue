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
    <IModal
      size="sm"
      static-backdrop
      :ok-title="$t('core::app.create')"
      :ok-disabled="form.busy"
      id="createRecord"
      form
      @submit="create"
      @keydown="form.onKeydown($event)"
      @hidden="handleModalHiddenEvent"
      :title="$t('core::resource.create', { resource: singularLabel })"
    >
      <FieldsGenerator
        focus-first
        :fields="fields"
        :form-id="form.formId"
        view="create"
        :is-floating="true"
      />
    </IModal>
    <IModal
      size="sm"
      static-backdrop
      id="updateRecord"
      :ok-title="$t('core::app.save')"
      @hidden="handleModalHiddenEvent"
      :ok-disabled="form.busy"
      form
      @submit="update"
      @keydown="form.onKeydown($event)"
      :title="$t('core::resource.edit', { resource: singularLabel })"
    >
      <FieldsGenerator
        :fields="fields"
        :form-id="form.formId"
        view="update"
        :is-floating="true"
      />
    </IModal>
    <ICard no-body>
      <template #header>
        <IButtonMinimal
          v-show="withCancel"
          variant="info"
          :text="$t('core::app.go_back')"
          @click="requestCancel"
        />

        <slot name="header"></slot>
      </template>
      <template #actions>
        <IButton
          @click="prepareCreate"
          icon="plus"
          size="sm"
          :text="$t('core::resource.create', { resource: singularLabel })"
        />
      </template>
      <TableSimple
        :table-props="{ shadow: false, ...tableProps }"
        :table-id="resourceName"
        :request-uri="resourceName"
        ref="tableRef"
        sort-by="name"
        :fields="columns"
      >
        <template #name="{ row }">
          <div class="flex justify-between">
            <a
              href="#"
              class="link"
              @click.prevent="prepareEdit(row.id)"
              v-text="row.name"
            />
            <IMinimalDropdown>
              <IDropdownItem
                @click="prepareEdit(row.id)"
                :text="$t('core::app.edit')"
                icon="PencilAlt"
              />

              <span
                v-i-tooltip="
                  row.is_primary
                    ? $t('core::resource.primary_record_delete_info', {
                        resource: singularLabel,
                      })
                    : null
                "
              >
                <IDropdownItem
                  :disabled="row.is_primary"
                  @click="destroy(row.id)"
                  icon="Trash"
                  :text="$t('core::app.delete')"
                />
              </span>
            </IMinimalDropdown>
          </div>
        </template>
      </TableSimple>
    </ICard>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import TableSimple from '~/Core/resources/js/components/Table/Simple/TableSimple.vue'
import { useFieldsForm } from '~/Core/resources/js/components/Fields/useFieldsForm'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useResourceFields } from '~/Core/resources/js/composables/useResourceFields'
import { useI18n } from 'vue-i18n'

const emit = defineEmits(['cancel', 'updated', 'created', 'deleted'])

const props = defineProps({
  resourceName: { required: true, type: String },
  withCancel: { type: Boolean, default: true },
  tableProps: {
    type: Object,
    default() {
      return {}
    },
  },
})

const { t } = useI18n()
const { resetStoreState } = useApp()

const { fields, getCreateFields, getUpdateFields } = useResourceFields()

const { form } = useFieldsForm(fields)

const columns = ref([
  {
    key: 'id',
    label: t('core::app.id'),
    sortable: true,
  },
  {
    key: 'name',
    label: t('core::fields.label'),
    sortable: true,
  },
])

const tableRef = ref(null)

const singularLabel = Innoclapps.config(
  `resources.${props.resourceName}.singularLabel`
)

function handleModalHiddenEvent() {
  form.reset()
  fields.value.set([])
}

/**
 * Request cancel edit
 */
function requestCancel() {
  emit('cancel')
}

/**
 * Prepare resource record create
 */
async function prepareCreate() {
  let createFields = await getCreateFields(props.resourceName)
  columns.value[1].key = createFields[0].attribute

  fields.value.set(createFields)

  Innoclapps.modal().show('createRecord')
}

/**
 * Prepare the resource record edit
 */
async function prepareEdit(id) {
  let updateFields = await getUpdateFields(props.resourceName, id)
  let { data } = await Innoclapps.request().get(`${props.resourceName}/${id}`)

  columns.value[1].key = updateFields[0].attribute
  form.fill('id', id)

  fields.value.set(updateFields).populate(data)

  Innoclapps.modal().show('updateRecord')
}

/**
 * Store resource record in storage
 */
function create() {
  form
    .hydrate()
    .post(props.resourceName)
    .then(record => {
      actionExecuted('created')
      Innoclapps.modal().hide('createRecord')
    })
}

/**
 * Update resource record in storage
 */
function update() {
  form
    .hydrate()
    .put(`${props.resourceName}/${form.id}`)
    .then(record => {
      actionExecuted('updated')
      Innoclapps.modal().hide('updateRecord')
    })
}

/**
 * Remove resource record from storage
 */
async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await Innoclapps.request().delete(`${props.resourceName}/${id}`)

  actionExecuted('deleted')
}

/**
 * Handle action executed
 */
function actionExecuted(action) {
  Innoclapps.success(t('core::resource.' + action))
  tableRef.value.reload()
  resetStoreState()
  emit(action)
}
</script>
<style scoped>
::v-deep(table thead th:first-child) {
  width: 7%;
}

::v-deep(table thead th:first-child a) {
  justify-content: center;
}

::v-deep(table tbody td:first-child) {
  width: 7%;
  text-align: center;
}
</style>
