<script setup lang="ts">
  import { Project, ProjectStep } from '@/types'
  import { Link } from '@inertiajs/vue3'
  import { computed } from 'vue'

  const props = defineProps<{
    project?: Project
    step?: ProjectStep
    token?: string
    page?: number
  }>()

  const steps = {
    intro: 'Introduction',
    'section-a': 'Section A',
    'section-b': 'Section B',
    'section-c': 'Section C',
    complete: 'Complete',
  }

  let atCurrentStep = false

  const stepClasses = computed(() => {
    let temp: Record<string, string> = {}
    Object.keys(steps).forEach((key: string) => {
      if (!atCurrentStep) {
        temp[key] = 'step step-primary'
      } else {
        temp[key] = 'step'
      }
      if (key === props.step?.id) {
        atCurrentStep = true
      }
    })
    return temp
  })
</script>

<template>
  <div>
    <ul class="steps">
      <li v-for="(s, index) in steps" :key="index" data-content="" :class="stepClasses[index]" class="hover:font-bold">
        <Link
          v-if="
            props.project &&
            props.token &&
            (index !== 'complete' || (index === 'complete' && props.project.status === 'Published'))
          "
          :href="
            route('story.form', {
              project: props.project?.id,
              step: index,
              token: props.token,
            })
          "
        >
          {{ s }}
        </Link>
        <span v-else>{{ s }}</span>
      </li>
    </ul>
  </div>
</template>

<style scoped lang="postcss">
  .steps {
    @apply text-sm;
  }
</style>
