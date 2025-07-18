<script setup lang="ts">
  import { computed } from 'vue'

  const props = withDefaults(
    defineProps<{
      legend?: string
      query?: string
      columns?: 1 | 2
    }>(),
    {
      legend: '',
      query: '',
      columns: 1,
    }
  )

  const fieldClasses = computed(() => {
    const temp = ['gap-4']

    if (props.columns > 1) {
      temp.push('grid')
      temp.push(`grid-cols-${props.columns}`)
    } else {
      temp.push('flex')
      temp.push('flex-col')
    }

    return temp.join(' ')
  })
</script>

<template>
  <fieldset class="max-w-4xl mx-auto space-y-2 p-4 pb-12 md:pb-24">
    <legend v-if="legend" class="sr-only">{{ legend }}</legend>
    <h3 v-if="query" class="text-base font-medium text-gray-900">{{ query }}</h3>
    <div :class="fieldClasses">
      <slot />
    </div>
  </fieldset>
</template>

<style scoped></style>
