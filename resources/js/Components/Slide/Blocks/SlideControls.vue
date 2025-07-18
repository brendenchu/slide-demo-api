<script setup lang="ts">
  import { ActionButton, NavigationButton } from '../UI'
  import { computed, ref } from 'vue'
  import type { Action, SlideOptions } from '../types'

  const props = withDefaults(
    defineProps<{
      current?: number
      pages?: number
      actions?: SlideOptions<Action>
    }>(),
    {
      current: 1,
      pages: 5,
      actions: undefined,
    }
  )

  const current = ref<number>(props.current)
  const actions = ref<SlideOptions<Action>>(
    props.actions || {
      next: {
        callback: () => {
          current.value++
        },
      },
      previous: {
        callback: () => {
          current.value--
        },
      },
    }
  )

  const page = computed(() => {
    return props.current ?? current.value
  })

  const hideNext: () => boolean = () => {
    let result = false
    Object.keys(actions.value).forEach((key) => {
      if (key !== 'previous' && actions.value[key].forced) {
        result = true
        return false
      }
    })
    return result
  }
</script>

<template>
  <section class="bg-base-300 w-full sticky bottom-0">
    <div class="contained-sm">
      <div
        :class="{
          'justify-between': page > 1 && page <= pages,
          'justify-end': page === 1,
        }"
        class="grid grid-cols-3 gap-3 max-w-4xl mx-auto"
      >
        <div class="flex items-center justify-start">
          <NavigationButton
            v-show="actions?.previous.forced || page > 1"
            theme="neutral"
            outline
            @click="actions?.previous.callback()"
          >
            {{ actions?.previous?.label || '&laquo; Previous' }}
          </NavigationButton>
        </div>
        <div class="flex items-center justify-center">
          <slot name="display">
            <Transition
              enter-active-class="transition ease-out duration-300"
              enter-from-class="opacity-0"
              enter-to-class="opacity-100"
              leave-active-class="transition ease-in duration-300"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <span v-if="page > 0 && page <= pages" class="hidden sm:inline-block text-lg font-bold text-base-content">
                Page {{ page }} of {{ pages }}
              </span>
            </Transition>
          </slot>
        </div>
        <div class="flex items-center justify-end">
          <NavigationButton
            v-show="!hideNext() && (actions?.next.forced || page <= pages + 1)"
            :disabled="actions?.next.blocked"
            @click="actions?.next.callback()"
          >
            {{ actions?.next?.label || 'Next &raquo;' }}
          </NavigationButton>
          <ActionButton
            v-show="actions?.close?.forced || (page > pages + 1 && actions?.close)"
            @click.prevent="actions?.close?.callback()"
          >
            {{ actions?.close?.label || 'Close' }}
          </ActionButton>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
