<script lang="ts" setup>
  import { LogoutButton } from '@/Components/Story/Form/UI'

  defineProps<{
    title?: string
  }>()
</script>

<template>
  <div class="p-6 flex justify-between items-start gap-2 bg-base-200">
    <h2 class="text-3xl font-bold">{{ title || 'Slide Form Demo' }}</h2>
    <slot />
    <div class="flex justify-end items-start gap-2">
      <LogoutButton />
    </div>
  </div>
</template>

<style lang="postcss" scoped></style>
