<script setup lang="ts">
  withDefaults(
    defineProps<{
      grid?: boolean
    }>(),
    {
      grid: false,
    }
  )
</script>

<template>
  <div
    :class="{
      'grid lg:grid-cols-2 gap-3 md:gap-6 items-start': grid,
      'flex flex-col gap-3 md:gap-6': !grid,
    }"
    class="group-wrapper"
  >
    <slot />
  </div>
</template>

<style scoped lang="postcss">
  .group-wrapper {
    & > div {
      @apply h-full;
    }

    & > input {
      @apply bg-yellow-200 md:absolute;
    }
  }
</style>
