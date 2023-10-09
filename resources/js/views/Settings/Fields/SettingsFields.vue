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
      id="newCustomField"
      size="sm"
      @shown="handleCustomFieldCreateModalShown"
      @hidden="handleCustomFieldModalHidden"
      :ok-title="$t('core::app.save')"
      :cancel-title="$t('core::app.cancel')"
      :title="$t('core::fields.custom.create')"
      form
      @keydown="form.onKeydown($event)"
      @submit="createCustomField"
      static-backdrop
    >
      <CustomFieldFormFields :form="form" />
    </IModal>
    <IModal
      size="sm"
      id="editCustomField"
      form
      @keydown="form.onKeydown($event)"
      @submit="updateCustomField"
      @hidden="handleCustomFieldModalHidden"
      :ok-title="$t('core::app.save')"
      :cancel-title="$t('core::app.cancel')"
      :title="$t('core::fields.custom.update')"
      static-backdrop
    >
      <CustomFieldFormFields :form="form" :edit="true" />
    </IModal>
    <div class="mb-3 flex items-center justify-between">
      <h4
        class="whitespace-nowrap text-lg/6 font-medium text-neutral-800 dark:text-white"
        v-text="title"
      />
      <IButton
        variant="primary"
        icon="Plus"
        size="sm"
        v-i-modal="'newCustomField'"
        class="ml-3"
        :text="$t('core::fields.add')"
      />
    </div>
    <div v-show="!editingRelatedFieldResource">
      <CustomizeFields
        v-if="resourceName !== 'products'"
        class="mb-3"
        ref="detailRef"
        :group="resourceName"
        :view="detailView"
        :heading="$t('core::fields.settings.detail')"
        :sub-heading="$t('core::fields.settings.detail_info')"
        @delete-requested="deleteCustomField"
        @update-requested="handleUpdateRequestedEvent"
        @saved="refreshFieldsData"
      />
      <CustomizeFields
        class="mb-3"
        ref="createRef"
        :group="resourceName"
        :view="createView"
        :heading="$t('core::fields.settings.create')"
        :sub-heading="$t('core::fields.settings.create_info')"
        :collapse-option="false"
        @delete-requested="deleteCustomField"
        @update-requested="handleUpdateRequestedEvent"
        @saved="refreshFieldsData"
      />
      <CustomizeFields
        class="mb-3"
        ref="updateRef"
        :group="resourceName"
        :view="updateView"
        :heading="$t('core::fields.settings.update')"
        :sub-heading="$t('core::fields.settings.update_info')"
        @delete-requested="deleteCustomField"
        @update-requested="handleUpdateRequestedEvent"
        @saved="refreshFieldsData"

      />
      <ICard class="mh-96" no-body>
        <template #header>
          <ICardHeading class="text-base" v-t="'core::fields.settings.list'" />
        </template>

        <i18n-t
          scope="global"
          tag="p"
          class="inline-block px-4 py-4 text-sm text-neutral-600 dark:text-white sm:px-6"
          keypath="core::fields.settings.list_info"
        >
          <template #icon>
            <Icon icon="DotsHorizontal" class="mx-1 inline h-5 w-5" />
          </template>
          <template #resourceName>
            {{ resource.label }}
          </template>
        </i18n-t>
      </ICard>
    </div>
    <RelatedFieldResource
      v-if="editingRelatedFieldResource"
      :resource-name="editingRelatedFieldResource"
      @created="refreshFieldsData"
      @updated="refreshFieldsData"
      @deleted="refreshFieldsData"
      @cancel="editingRelatedFieldResource = null"
    />
  </div>
</template>
<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import CustomizeFields from './SettingsCustomizeFields.vue'
import CustomFieldFormFields from './CustomFieldFormFields.vue'
import RelatedFieldResource from '~/Core/resources/js/components/SimpleResourceCRUD.vue'
import cloneDeep from 'lodash/cloneDeep'
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useForm } from '~/Core/resources/js/composables/useForm'

const { t } = useI18n()
const store = useStore()
const route = useRoute()
const { setPageTitle } = useApp()

const createView = Innoclapps.config('fields.views.create')
const updateView = Innoclapps.config('fields.views.update')
const detailView = Innoclapps.config('fields.views.detail')

const detailRef = ref(null)
const createRef = ref(null)
const updateRef = ref(null)

const editingRelatedFieldResource = ref(null)

const { form } = useForm(
  {
    label: null,
    field_type: 'Text',
    field_id: null,
    resource_name: null,
    options: [],
    is_unique: null,
  },
  { resetOnSuccess: true }
)

const resourceName = computed(() => route.params.resourceName)

const resource = computed(() =>
  Innoclapps.config(`resources.${resourceName.value}`)
)

const title = computed(() => {
  if (!resource.value) {
    return null
  }

  return t('core::resource.settings.fields', {
    resourceName: resource.value.singularLabel,
  })
})

watch(resourceName, () => {
  // When navigating out of the setting route and the resource in the URL is removed
  // this watcher is triggered and in this case, the resource and the titles are null, no need
  // to set any title
  if (title.value) {
    setPageTitle(title)
  }

  // In case the user is editing some field data
  // to show the fields again, we need to set the editingRelatedFieldResource to null
  editingRelatedFieldResource.value = null
})

function handleUpdateRequestedEvent(field) {
  if (!field.customField) {
    editingRelatedFieldResource.value = field.optionsViaResource
    return
  }

  form.fill('id', field.customField.id)
  form.fill('label', field.customField.label)
  form.fill('field_type', field.customField.field_type)
  form.fill('field_id', field.customField.field_id)
  form.fill('is_unique', field.customField.is_unique)
  form.fill('resource_name', field.customField.resource_name)
  // Clone options deep, when removing an option and not saving
  // the custom field, will remove the option from the field.customField.options array
  // too, in this case, we need the option to the original field in case
  // user access edit again to be shown on the form
  form.fill('options', cloneDeep(field.customField.options) || [])

  Innoclapps.modal().show('editCustomField')
}

function updateCustomField() {
  form.put(`/custom-fields/${form.id}`).then(field => {
    Innoclapps.modal().hide('editCustomField')

    refreshFieldsData()
    Innoclapps.success(t('core::fields.custom.updated'))
  })
}

async function deleteCustomField(id) {
  await Innoclapps.dialog().confirm()

  Innoclapps.request()
    .delete(`/custom-fields/${id}`)
    .then(() => {
      refreshFieldsData()
      Innoclapps.success(t('core::fields.custom.deleted'))
    })
}

function refreshFieldsData() {
  store.commit('table/RESET_SETTINGS')

  const fieldsRefs = [createRef, updateRef, detailRef].filter(
    ref => ref.value !== null
  )

  Promise.all(fieldsRefs.map(ref => ref.value.fetch())).then(() => {
    nextTick(() => fieldsRefs.forEach(ref => ref.value.submit()))
  })
}

function createCustomField() {
  form.post('/custom-fields').then(field => {
    Innoclapps.modal().hide('newCustomField')

    refreshFieldsData()
    Innoclapps.success(t('core::fields.custom.created'))
  })
}

function handleCustomFieldCreateModalShown() {
  form.fill('resource_name', resourceName.value)
}

function handleCustomFieldModalHidden() {
  form.reset()
  form.errors.clear()
}
</script>
