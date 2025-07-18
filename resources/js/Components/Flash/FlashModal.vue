<script setup lang="ts">
  import { computed, onMounted, onUnmounted, watch } from 'vue'
  import { FlashType, FlashWidth } from './types'

  const props = withDefaults(
    defineProps<{
      show?: boolean
      maxWidth?: FlashWidth
      type?: FlashType
      closeable?: boolean
    }>(),
    {
      show: false,
      maxWidth: '2xl',
      type: 'message',
      closeable: true,
    }
  )

  const emit = defineEmits(['close'])

  watch(
    () => props.show,
    () => {
      if (props.show) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = 'visible'
      }
    }
  )

  const close = () => {
    if (props.closeable) {
      emit('close')
    }
  }

  const closeOnEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) {
      close()
    }
  }

  onMounted(() => document.addEventListener('keydown', closeOnEscape))

  onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape)
    document.body.style.overflow = 'visible'
  })

  const maxWidthClass = computed(() => {
    return {
      sm: 'sm:max-w-sm',
      md: 'sm:max-w-md',
      lg: 'sm:max-w-lg',
      xl: 'sm:max-w-xl',
      '2xl': 'sm:max-w-2xl',
    }[props.maxWidth]
  })
</script>

<template>
  <div
    v-show="show"
    class="my-3 p-3 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto flex items-start justify-between gap-2"
    :class="maxWidthClass"
  >
    <div>
      <slot v-if="show" />
    </div>

    <button
      v-if="closeable"
      type="button"
      class="rounded-md focus:outline-none focus:shadow-outline-white"
      @click="close"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" height="1em" viewBox="0 0 384 512">
        <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
        <path
          d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"
        />
      </svg>
    </button>
  </div>
</template>

<style scoped lang="postcss"></style>
