<script lang="ts" setup>
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { Head } from '@inertiajs/vue3'
  import { Project } from '@/types'
  import IntroWidget from './Partials/IntroWidget.vue'
  import TeamWidget from './Partials/TeamWidget.vue'
  import ProjectWidget from './Partials/ProjectWidget.vue'

  defineProps<{
    projects?: Project[]
  }>()
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <div class="py-6 md:py12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid-cols-2 grid gap-4">
        <IntroWidget />
        <TeamWidget />
        <ProjectWidget v-if="projects && projects.length > 0" :projects="projects" class="col-span-2" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
