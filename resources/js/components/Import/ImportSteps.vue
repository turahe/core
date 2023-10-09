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
    class="-mt-px lg:border-b lg:border-t lg:border-neutral-200 lg:dark:border-neutral-700"
  >
    <nav class="mx-auto max-w-7xl lg:px-8" aria-label="Progress">
      <ol
        role="list"
        class="overflow-hidden lg:flex lg:border-l lg:border-r lg:border-neutral-200 lg:dark:border-neutral-700"
      >
        <li
          v-for="(step, stepIdx) in steps"
          :key="step.id"
          class="relative overflow-hidden lg:flex-1"
        >
          <div
            :class="[
              stepIdx === 0 ? 'border-b-0' : '',
              stepIdx === steps.length - 1 ? 'border-t-0' : '',
              'overflow-hidden border-y border-neutral-200 dark:border-neutral-700 lg:border-0',
            ]"
          >
            <div v-if="step.status === 'complete'">
              <span
                class="absolute left-0 top-0 h-full w-1 bg-transparent lg:bottom-0 lg:top-auto lg:h-1 lg:w-full"
                aria-hidden="true"
              />
              <span
                :class="[
                  stepIdx !== 0 ? 'lg:pl-9' : '',
                  'flex items-start px-6 py-5 text-sm font-medium',
                ]"
              >
                <span class="shrink-0">
                  <span
                    :class="
                      stepIdx !== steps.length - 1
                        ? 'bg-primary-600'
                        : 'bg-success-500'
                    "
                    class="flex h-10 w-10 items-center justify-center rounded-full"
                  >
                    <Icon icon="Check" class="h-6 w-6 text-white" />
                  </span>
                </span>
                <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                  <span
                    class="text-xs font-semibold uppercase tracking-wide dark:text-primary-300"
                  >
                    {{ step.name }}
                  </span>
                  <span
                    class="truncate text-sm font-medium text-neutral-500 dark:text-neutral-200"
                  >
                    {{ step.description }}
                  </span>
                </span>
              </span>
            </div>
            <div v-else-if="step.status === 'current'" aria-current="step">
              <span
                class="absolute left-0 top-0 h-full w-1 bg-primary-600 dark:bg-primary-300 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full"
                aria-hidden="true"
              />
              <span
                :class="[
                  stepIdx !== 0 ? 'lg:pl-9' : '',
                  'flex items-start px-6 py-5 text-sm font-medium',
                ]"
              >
                <span class="shrink-0">
                  <span
                    class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary-600 dark:border-primary-300"
                  >
                    <span class="text-primary-600 dark:text-primary-300">
                      {{ step.id }}
                    </span>
                  </span>
                </span>
                <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                  <span
                    class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-300"
                  >
                    {{ step.name }}
                  </span>
                  <span
                    class="truncate text-sm font-medium text-neutral-500 dark:text-neutral-200"
                  >
                    {{ step.description }}
                  </span>
                </span>
              </span>
            </div>
            <div v-else>
              <span
                class="absolute left-0 top-0 h-full w-1 bg-transparent lg:bottom-0 lg:top-auto lg:h-1 lg:w-full"
                aria-hidden="true"
              />
              <span
                :class="[
                  stepIdx !== 0 ? 'lg:pl-9' : '',
                  'flex items-start px-6 py-5 text-sm font-medium',
                ]"
              >
                <span class="shrink-0">
                  <span
                    class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-neutral-300 dark:border-neutral-400"
                  >
                    <span class="text-neutral-500 dark:text-neutral-300">
                      {{ step.id }}
                    </span>
                  </span>
                </span>
                <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                  <span
                    class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-100"
                  >
                    {{ step.name }}
                  </span>
                  <span
                    class="truncate text-sm font-medium text-neutral-500 dark:text-neutral-200"
                  >
                    {{ step.description }}
                  </span>
                </span>
              </span>
            </div>
            <template v-if="stepIdx !== 0">
              <!-- Separator -->
              <div
                class="absolute inset-0 left-0 top-0 hidden w-3 lg:block"
                aria-hidden="true"
              >
                <svg
                  class="h-full w-full text-neutral-300 dark:text-neutral-600"
                  viewBox="0 0 12 82"
                  fill="none"
                  preserveAspectRatio="none"
                >
                  <path
                    d="M0.5 0V31L10.5 41L0.5 51V82"
                    stroke="currentcolor"
                    vector-effect="non-scaling-stroke"
                  />
                </svg>
              </div>
            </template>
          </div>
        </li>
      </ol>
    </nav>
  </div>
</template>
<script setup>
defineProps({ steps: { type: Object, required: true } })
</script>
