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
    <a
      :href="href"
      @click.prevent="navigate"
      :class="[
        isActive
          ? 'active bg-neutral-200/50 text-primary-600 dark:bg-neutral-700 dark:text-primary-300'
          : 'text-neutral-700 hover:bg-neutral-200/50 hover:text-primary-600 dark:text-neutral-100 dark:hover:dark:bg-neutral-700 dark:hover:text-primary-300',
        'group flex items-center rounded-md px-3 py-2 text-sm font-semibold focus:outline-none',
        linkClass,
      ]"
    >
      <slot name="icon">
        <Icon
          v-if="icon"
          :class="[
            isActive
              ? 'text-primary-600 dark:text-primary-300'
              : 'text-neutral-400 group-hover:text-primary-600 dark:text-neutral-400 dark:group-hover:text-primary-300',
            '-ml-1 mr-3 h-6 w-6 shrink-0',
            iconClass,
          ]"
          :icon="icon"
        />
      </slot>
      <slot name="title" :title="title">
        <span class="truncate">
          {{ title }}
        </span>
      </slot>

      <Icon
        v-if="!fixed && hasChildrens"
        icon="ChevronDown"
        :class="[
          isActive
            ? 'text-neutral-500'
            : 'text-neutral-400 group-hover:text-neutral-500',
          'ml-auto mr-1 h-6 w-6 shrink-0',
        ]"
      />
    </a>

    <div
      :id="itemId"
      ref="childrenRef"
      v-show="collapseVisible"
      class="ml-5 mt-1"
    >
      <slot></slot>
    </div>
  </div>
</template>
<script>
export default {
  name: 'IVerticalNavigationItem',
}
</script>
<script setup>
import { ref, watch, nextTick, useSlots, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { randomString } from '@/utils'
import startsWith from 'lodash/startsWith'

const props = defineProps({
  to: [String, Object],
  href: { type: String, default: '#' },
  fixed: { type: Boolean, default: false },
  linkClass: [Array, Object, String],
  title: String,
  icon: String,
  iconClass: [Array, Object, String],
})

const itemId = ref(randomString())

const childrenRef = ref(null)

/**
 * We will update the collaseVisible data in created lifecycle to prevent
 * blinking the collapsible when the item has no child items
 */
const collapseVisible = ref(props.fixed === true)

const router = useRouter()
const route = useRoute()
const slots = useSlots()

watch(
  () => route.fullPath,
  () => {
    nextTick(() => {
      if (!hasActiveChildren() && !props.fixed) {
        collapseVisible.value = false
      }
    })
  }
)

const childItems = computed(() => (!slots.default ? [] : slots.default()))

const hasChildrens = computed(
  () =>
    childItems.value &&
    childItems.value.length > 0 &&
    // We will check if the props prop is set on the first child
    // If yes, then it's real item, otherwise probably is in for loop
    childItems.value[0].props
)

const resolvedRoute = computed(() => router.resolve(props.to))

const isActive = computed(() => {
  if (!props.to) {
    return collapseVisible.value && !props.fixed
  }

  return (
    route.path == resolvedRoute.value.path ||
    startsWith(route.path, resolvedRoute.value.path)
  )
})

function hasActiveChildren() {
  if (!childrenRef.value) {
    return false
  }

  return childrenRef.value.querySelectorAll('.active').length > 0
}

function navigate() {
  if (props.to) {
    if (route.path != resolvedRoute.value.path) {
      router.push(props.to)
    }
    return
  }

  if (!props.fixed) {
    collapseVisible.value = !collapseVisible.value
  }
}

onMounted(() => {
  // Set the initial collapseVisible in case of direct access
  if (hasActiveChildren()) {
    collapseVisible.value = true
  }
})
</script>
