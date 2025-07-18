<script setup lang="ts">
  import type { Direction } from '../types'

  const props = withDefaults(
    defineProps<{
      direction?: Direction
    }>(),
    {
      direction: 'next',
    }
  )
</script>

<template>
  <Transition :name="`slide-${props.direction}`">
    <div v-if="true" class="absolute w-full h-full">
      <div class="contained w-full h-full">
        <slot />
      </div>
    </div>
  </Transition>
</template>

<style scoped lang="postcss">
  .slide-next-enter-active,
  .slide-next-leave-active,
  .slide-previous-enter-active,
  .slide-previous-leave-active {
    @apply transition-all duration-500 ease-in-out;
  }

  .slide-next-enter-from,
  .slide-previous-leave-to {
    transform: translateX(100%);
  }

  .slide-next-leave-to,
  .slide-previous-enter-from {
    transform: translateX(-100%);
  }

  .slide-next-enter-to,
  .slide-next-leave-from,
  .slide-previous-enter-to,
  .slide-previous-leave-from {
    transform: translateX(0);
  }
</style>
