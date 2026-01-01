<script setup lang="ts">
  import { Link } from '@inertiajs/vue3'
  import { Project } from '@/types'

  defineProps<{
    project: Project
    token: string
  }>()
</script>

<template>
  <!-- To the Dashboard button -->
  <Link
    :href="
      route('story.dashboard', {
        project: project.id,
        token,
      })
    "
    class="btn btn-outline btn-primary btn-xs md:btn-sm rounded-btn"
  >
    <svg class="inline-block h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
      <path
        fill-rule="evenodd"
        d="M10.707 3.293a1 1 0 010 1.414L7.414 9H16a1 1 0 110 2H7.414l3.293 3.293a1 1 0 11-1.414 1.414l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 0z"
        clip-rule="evenodd"
      />
    </svg>
    <span> To the Dashboard </span>
  </Link>
</template>

<style scoped lang="postcss"></style>
