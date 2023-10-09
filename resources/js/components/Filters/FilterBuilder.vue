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
  <IModal
    :visible="rulesAreVisible"
    size="lg"
    static-backdrop
    :title="
      !activeFilter
        ? $t('core::filters.create')
        : $t('core::filters.edit_filter')
    "
    :ok-title="
      saving ? $t('core::filters.save_and_apply') : $t('core::filters.apply')
    "
    :ok-disabled="!filtersCanBeApplied || form.busy"
    @ok="submit"
    :cancel-title="$t('core::app.hide')"
    :hide-footer="isReadonly"
    @shown="handleModalShown"
    @hidden="rulesAreVisible = false"
  >
    <IAlert class="mb-3 border border-info-200" v-if="isReadonly">
      {{ $t('core::filters.is_readonly') }}
    </IAlert>

    <div class="mb-3 flex items-center justify-end space-x-2">
      <a
        v-if="!isCurrentFilterDefault && isReadonly"
        href="#"
        class="rounded-md px-2 py-1.5 text-sm font-medium text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50 hover:bg-primary-50"
        @click.prevent="markAsDefault"
        v-t="'core::filters.mark_as_default'"
      />

      <a
        v-if="isCurrentFilterDefault && isReadonly"
        href="#"
        class="rounded-md px-2 py-1.5 text-sm font-medium text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50 hover:bg-primary-50"
        @click.prevent="unmarkAsDefault"
        v-t="'core::filters.unmark_as_default'"
      />

      <a
        v-show="!isReadonly && localHasRulesApplied"
        class="rounded-md px-2 py-1.5 text-sm font-medium text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50 hover:bg-primary-50"
        href="#"
        @click.prevent="clearRules"
        v-t="'core::filters.clear_rules'"
      />

      <span
        v-if="activeFilter && canDelete"
        v-i-tooltip="
          isSystemDefault
            ? $t('core::filters.system_default_delete_info')
            : null
        "
      >
        <IButtonMinimal
          variant="danger"
          :disabled="isSystemDefault || isReadonly"
          :text="$t('core::app.delete')"
          @click="destroy"
        />
      </span>
    </div>

    <QueryBuilder
      v-bind="$attrs"
      ref="queryBuilder"
      :rules="availableRules"
      :read-only="isReadonly"
      :identifier="identifier"
      :view="view"
    />

    <div v-show="saving && !isReadonly" class="mt-5">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <IFormGroup
            :label="$t('core::filters.name')"
            label-for="filter_name"
            required
          >
            <IFormInput
              v-model="form.name"
              size="sm"
              :placeholder="$t('core::filters.name')"
              id="filter_name"
              name="name"
              type="text"
            />
            <IFormError v-text="form.getError('name')" />
          </IFormGroup>
        </div>
        <div>
          <IFormGroup required :label="$t('core::filters.share.with')">
            <IDropdown
              adaptive-width
              size="sm"
              :text="
                form.is_shared
                  ? $t('core::filters.share.everyone')
                  : $t('core::filters.share.private')
              "
            >
              <IDropdownItem @click="form.is_shared = false">
                <div class="flex flex-col">
                  <p
                    class="text-neutral-900 dark:text-white"
                    v-t="'core::filters.share.private'"
                  />
                  <p
                    class="text-neutral-500 dark:text-neutral-300"
                    v-t="'core::filters.share.private_info'"
                  />
                </div>
              </IDropdownItem>
              <IDropdownItem
                v-if="!hasRulesAppliedWithAuthorization"
                @click="form.is_shared = true"
              >
                <div class="flex flex-col">
                  <p
                    class="text-neutral-900 dark:text-white"
                    v-t="'core::filters.share.everyone'"
                  />
                  <p
                    class="text-neutral-500 dark:text-neutral-300"
                    v-t="'core::filters.share.everyone_info'"
                  />
                </div>
              </IDropdownItem>
            </IDropdown>
            <IFormError v-text="form.getError('is_shared')" />
          </IFormGroup>
        </div>
      </div>
      <IAlert v-if="hasRulesAppliedWithAuthorization" show class="mb-3">
        {{
          $t('core::filters.cannot_be_shared', {
            rules: rulesLabelsWithAuthorization,
          })
        }}
      </IAlert>
    </div>

    <IFormGroup v-show="saving && !isReadonly">
      <IFormCheckbox
        v-model:checked="defaulting"
        :label="$t('core::filters.is_default')"
      />
    </IFormGroup>
    <template #modal-cancel="{ cancel, title }">
      <div class="flex space-x-4">
        <IFormToggle
          v-show="!editing && !activeFilter && localHasRulesApplied"
          label-class="font-semibold"
          v-model="saving"
          :label="$t('core::filters.save_as_new')"
        />
        <IButton
          variant="white"
          class="hidden sm:inline-flex"
          :disabled="form.busy"
          :text="title"
          @click="cancel"
        />
      </div>
    </template>
  </IModal>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import QueryBuilder from '~/Core/resources/js/components/QueryBuilder'
import { useQueryBuilder } from '~/Core/resources/js/components/QueryBuilder/useQueryBuilder'
import { useFilterable } from './useFilterable'
import each from 'lodash/each'
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'
import { useForm } from '~/Core/resources/js/composables/useForm'
import { isValueEmpty } from '@/utils'
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useGate } from '~/Core/resources/js/composables/useGate'
import { useGlobalEventListener } from '~/Core/resources/js/composables/useGlobalEventListener'
import {
  isNullableOperator,
  isBetweenOperator,
} from '~/Core/resources/js/components/QueryBuilder/utils'

const emit = defineEmits(['apply'])

const props = defineProps({
  view: { required: true, type: String },
  identifier: { required: true, type: String },
  activeFilterId: Number,
  initialApply: { default: true, type: Boolean },
})

const store = useStore()
const { currentUser } = useApp()
const { gate } = useGate()

const { form } = useForm()
const editing = ref(false)
const saving = ref(false)
const defaulting = ref(false)

const {
  queryBuilderRules,
  rulesAreValid,
  hasRulesApplied,
  findRule,
  rulesAreVisible,
  resetQueryBuilderRules,
} = useQueryBuilder(props.identifier, props.view)

const {
  rules: availableRules,
  activeFilter,
  userDefaultFilter,
  findFilter,
} = useFilterable(props.identifier, props.view)

/**
 * Get the rule labels with authorization
 */
const rulesLabelsWithAuthorization = computed(() => {
  return rulesWithAuthorization.value.map(rule => rule.label).join(', ')
})

/**
 * Get all the rules in the query builder which are having authorization
 */
const rulesWithAuthorization = computed(() =>
  availableRules.value.filter(rule => rule.has_authorization)
)

/**
 * Indicates whether there are rules in the query builder which are with authorization
 */
const hasRulesAppliedWithAuthorization = computed(() =>
  rulesWithAuthorization.value.some(rule => findRule(rule.id))
)

/**
 * Indicates whether the curent active filter is sytem default
 */
const isSystemDefault = computed(
  () => activeFilter.value && activeFilter.value.is_system_default
)

/**
 * Indicates whether currently applied filter is default
 */
const isCurrentFilterDefault = computed(
  () =>
    activeFilter.value &&
    userDefaultFilter.value &&
    activeFilter.value.id == userDefaultFilter.value.id
)

/**
 * Indicates whether the filters can be applied
 */
const filtersCanBeApplied = computed(
  () => !(!localRulesAreValid.value || totalValidRules.value === 0)
)

/**
 * Indicates whether the filter is read only
 */
const isReadonly = computed(
  () =>
    (activeFilter.value && activeFilter.value.is_readonly) ||
    activeFilterIsSharedFromAnotherUser.value
)

/**
 * The applied rules values
 */
const rulesValidationValues = computed(() =>
  getValuesForValidation(queryBuilderRules.value)
)

/**
 * Total number of rules in the query builder
 * The function checks based on the values that exists
 */
const totalValidRules = computed(() => rulesValidationValues.value.length)

/**
 * Indicates whether the applied rules are valid
 */
const localRulesAreValid = computed(() => {
  if (!localHasRulesApplied.value) {
    return true
  }

  let totalValid = 0

  rulesValidationValues.value.forEach(value => {
    if (!isValueEmpty(value)) {
      totalValid++
    }
  })

  // If all rules has values, the filters are valid
  return totalValidRules.value === totalValid
})

/**
 * Indicates if there are filters applied for the resource
 */
const localHasRulesApplied = computed(() => {
  // If there is values, this means that there is at least
  // one rule added in the filter
  return totalValidRules.value > 0
})

/**
 * Indicates whether the active filter is shared and created from another user
 */
const activeFilterIsSharedFromAnotherUser = computed(() => {
  if (!activeFilter.value || activeFilter.value.is_system_default) {
    return false
  }

  return (
    activeFilter.value.is_shared &&
    activeFilter.value.user_id != currentUser.value.id
  )
})

const canUpdate = computed(() => gate.allows('update', activeFilter.value))
const canDelete = computed(() => gate.allows('delete', activeFilter.value))
const PUSH_FILTER = (...params) => store.commit('filters/PUSH', ...params)
const UPDATE_FILTER = (...params) => store.commit('filters/UPDATE', ...params)
const UNMARK_AS_DEFAULT = (...params) =>
  store.commit('filters/UNMARK_AS_DEFAULT', ...params)

/**
 * Reset the filters state
 */
function resetState() {
  form.clear().set({
    name: null,
    rules: [],
    is_shared: false,
  })
  saving.value = false
  editing.value = false
  defaulting.value = false
}

/**
 * Handle the modal shown event
 */
function handleModalShown() {
  resetState()

  if (activeFilter.value && canUpdate.value) {
    setUpdateData()
  }
}

/**
 * Store new filter
 */
async function create() {
  form.fill('identifier', props.identifier)
  let filter = await form.post(`/filters`)

  PUSH_FILTER({
    filter: filter,
    identifier: props.identifier,
  })

  setActive(filter.id)

  return filter
}

/**
 * Update the currently active filter
 */
async function update() {
  const filter = await form.put(`/filters/${activeFilter.value.id}`)
  handleUpdatedLifeCycle(filter)

  return filter
}

/**
 * Submit the filters form
 */
async function submit() {
  if (!saving.value) {
    apply()
    rulesAreVisible.value = false
    return
  }

  form.fill('rules', queryBuilderRules.value)

  await (editing.value ? update() : create())
  await nextTick()

  if (!editing.value) {
    defaulting.value && markAsDefault()
  } else {
    if (isCurrentFilterDefault.value && !defaulting.value) {
      unmarkAsDefault()
    } else if (isCurrentFilterDefault.value && defaulting.value) {
      markAsDefault()
    }
  }

  apply()
  rulesAreVisible.value = false
}

/**
 * Set update data so the submit method can use
 */
function setUpdateData() {
  form.is_shared = activeFilter.value.is_shared
  form.name = activeFilter.value.name
  defaulting.value = isCurrentFilterDefault.value
  editing.value = true
  saving.value = true
}

/**
 * Delete filter
 */
async function destroy() {
  await Innoclapps.dialog().confirm()

  store
    .dispatch('filters/destroy', {
      identifier: props.identifier,
      view: props.view,
      id: activeFilter.value.id,
    })
    .then(() => {
      rulesAreVisible.value = false
      clearRules()
    })
}

/**
 * Make the active filter as default
 */
function markAsDefault() {
  Innoclapps.request()
    .put(`filters/${activeFilter.value.id}/${props.view}/default`)
    .then(({ data }) => {
      // We need to remove the previous default filter data
      if (userDefaultFilter.value && userDefaultFilter.value.id != data.id) {
        UNMARK_AS_DEFAULT({
          id: userDefaultFilter.value.id,
          identifier: props.identifier,
          view: props.view,
          userId: currentUser.value.id,
        })
      }

      handleUpdatedLifeCycle(data)
    })
}

/**
 * Unmark the active filter as default
 *
 * @return {Void}
 */
function unmarkAsDefault() {
  Innoclapps.request()
    .delete(`/filters/${activeFilter.value.id}/${props.view}/default`)
    .then(({ data }) => {
      handleUpdatedLifeCycle(data)
    })
}

/**
 * Update the filter in Vuex
 */
function handleUpdatedLifeCycle(filter) {
  UPDATE_FILTER({
    identifier: props.identifier,
    filter: filter,
  })
}

/**
 * Apply filters event
 */
function apply() {
  emit('apply', queryBuilderRules.value)
}

/**
 * Set filter as active
 */
function setActive(id, emit = true) {
  let filter = findFilter(id)

  queryBuilderRules.value = cloneDeep(filter.rules)
  activeFilter.value = filter.id

  // And if needed emit apply event
  if (emit) {
    apply()
  }
}

/**
 * Clear the applied query builder rules
 */
function clearRules() {
  resetQueryBuilderRules()

  apply()
}

/**
 * Get the applied rules values
 */
function getValuesForValidation(query) {
  let vals = []

  each(query.children, (rule, key) => {
    if (rule.query.children) {
      vals = vals.concat(getValuesForValidation(rule.query))
    } else {
      let filter = find(availableRules.value, ['id', rule.query.rule])
      if (filter && filter.isStatic) {
        // Push true so it can trigger true rule
        // static rules are always valid as they do not receive any values
        vals.push(true)
      } else if (isNullableOperator(rule.query.operator)) {
        // Push only true so we can validate as valid rule
        vals.push(true)
      } else if (isBetweenOperator(rule.query.operator)) {
        // Validate between, from and to must be selected
        if (
          rule.query.value &&
          !isValueEmpty(rule.query.value[0]) &&
          !isValueEmpty(rule.query.value[1])
        ) {
          vals.push(cloneDeep(rule.query.value))
        } else {
          // Push null so it can trigger false rule
          vals.push(null)
        }
      } else {
        vals.push(cloneDeep(rule.query.value))
      }
    }
  })

  return vals
}
/**
 *  Update the store on rules are valid change
 */
watch(
  localRulesAreValid,
  newVal => {
    rulesAreValid.value = newVal
  },
  { immediate: true }
)

/**
 * Update the store on rules applied change
 */
watch(
  localHasRulesApplied,
  newVal => {
    hasRulesApplied.value = newVal
  },
  { immediate: true }
)

useGlobalEventListener(
  `${props.identifier}-${props.view}-filter-selected`,
  setActive
)

// The setActive method will trigger the refresh too
if (props.activeFilterId) {
  setActive(props.activeFilterId, props.initialApply)
} else if (activeFilter.value) {
  // In case the active filter was edited but the rules are invalid
  // in this case, reset the rule and use the original rules from the filter
  if (!rulesAreValid.value) {
    queryBuilderRules.value = activeFilter.value.rules
  }
  // We will check if there is an active filter already applied in store
  // helps keeping the previous filter applied when navigating from the page
  // where the filters are mounted and then going back
  props.initialApply && apply()
} else if (userDefaultFilter.value) {
  setActive(userDefaultFilter.value.id, props.initialApply)
} else {
  props.initialApply && apply()
}
</script>
