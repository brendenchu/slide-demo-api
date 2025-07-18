<script setup lang="ts">
  import { computed } from 'vue'
  import { ProjectStep } from '@/types'

  const props = defineProps<{
    step?: ProjectStep
    page?: number
  }>()

  // this progress bar increments by sections
  const progress = computed(() => {
    if (!props.step) return 0

    switch (props.step.id) {
      case 'intro':
        return 0
      case 'section-a':
        return 25
      case 'section-b':
        return 50
      case 'section-c':
        return 75
      case 'complete':
        return 100
      default:
        return 0
    }
  })
</script>

<template>
  <progress :value="progress" max="100" class="w-full h-2 progress-secondary" />
</template>
