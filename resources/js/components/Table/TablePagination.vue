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
  <div class="flex items-center justify-between">
    <div class="flex flex-1 justify-between sm:hidden">
      <IButton
        variant="white"
        :size="size"
        @click="$emit('go-to-previous')"
        :disabled="!hasPreviousPage || loading"
        :text="$t('pagination.previous')"
      />

      <IButton
        variant="white"
        :size="size"
        @click="$emit('go-to-next')"
        :disabled="!hasNextPage || loading"
        :text="$t('pagination.next')"
      />
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p
          class="text-sm text-neutral-700 dark:text-neutral-200"
          v-t="{
            path: 'core::table.info',
            args: {
              from: from,
              to: to,
              total: total,
            },
          }"
        ></p>
      </div>
      <div>
        <nav
          class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm"
          aria-label="Pagination"
        >
          <template v-if="renderLinks">
            <a
              href="#"
              @click.prevent="$emit('go-to-previous')"
              :class="{
                'px-2 py-1': size === 'sm',
                'px-2 py-1.5': size === 'md',
                'pointer-events-none opacity-60': !hasPreviousPage || loading,
              }"
              class="relative inline-flex items-center rounded-l-md border border-neutral-300 bg-white text-sm font-medium text-neutral-500 focus:outline-none hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
            >
              <span class="sr-only">Previous</span>
              <Icon
                icon="ChevronLeft"
                :class="{
                  'h-4 w-4': size === 'sm',
                  'h-5 w-5': size === 'md',
                }"
              />
            </a>
            <!-- Current: "z-10 bg-primary-50 border-primary-500 text-primary-600", Default: "bg-white border-neutral-300 text-neutral-500 hover:bg-neutral-50" -->
            <template v-for="(page, index) in links" :key="index">
              <span
                v-if="page === '...'"
                :class="{
                  'px-3 py-1': size === 'sm',
                  'px-4 py-1.5': size === 'md',
                }"
                class="relative inline-flex items-center border border-neutral-300 bg-white text-sm font-medium text-neutral-700 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300"
              >
                ...
              </span>

              <a
                v-else
                href="#"
                aria-current="page"
                @click.prevent="$emit('go-to-page', page)"
                :class="[
                  'relative inline-flex items-center border text-sm font-medium focus:outline-none',
                  isCurrentPageCheck(page)
                    ? 'z-10 border-primary-500 bg-primary-50 text-primary-600 dark:border-primary-500 dark:bg-primary-500 dark:text-white'
                    : 'border-neutral-300 bg-white text-neutral-500 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700',
                  {
                    'px-3 py-1': size === 'sm',
                    'px-4 py-1.5': size === 'md',
                    'pointer-events-none opacity-60': loading,
                  },
                ]"
                v-text="page"
              />
            </template>
            <a
              href="#"
              @click.prevent="$emit('go-to-next')"
              :class="[
                'relative inline-flex items-center rounded-r-md border border-neutral-300 bg-white text-sm font-medium text-neutral-500 focus:outline-none hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700',
                {
                  'px-2 py-1': size === 'sm',
                  'px-2 py-1.5': size === 'md',
                  'pointer-events-none opacity-60': !hasNextPage || loading,
                },
              ]"
            >
              <span class="sr-only">Next</span>
              <Icon
                icon="ChevronRight"
                :class="{
                  'h-4 w-4': size === 'sm',
                  'h-5 w-5': size === 'md',
                }"
              />
            </a>
          </template>
        </nav>
      </div>
    </div>
  </div>
</template>
<script setup>
defineEmits(['go-to-previous', 'go-to-next', 'go-to-page'])

const props = defineProps({
  loading: { type: Boolean, required: false },
  isCurrentPageCheck: { type: Function, required: true },
  renderLinks: { type: Boolean, required: true },
  links: { type: Array },

  hasNextPage: { type: Boolean, required: true },
  hasPreviousPage: { type: Boolean, required: true },
  from: { type: Number, required: true },
  to: { type: Number, required: true },
  total: { type: Number, required: true },
  size: {
    type: String,
    default: 'md',
    validator(value) {
      return ['sm', 'md'].includes(value)
    },
  },
})
</script>
