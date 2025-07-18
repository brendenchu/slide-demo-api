<script setup lang="ts">
  import { Project } from '@/types'
  import { Link } from '@inertiajs/vue3'

  const props = defineProps<{
    projects: Project[]
  }>()
</script>

<template>
  <section class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <header>
      <h2 class="text-lg font-medium text-gray-900">Projects</h2>
      <p class="mt-1 text-sm text-gray-600">A list of projects you have created.</p>
    </header>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-1">
      <div v-for="project in props.projects" :key="project.id" class="text-gray-900">
        <div
          class="border rounded border-gray-500 p-2 text-gray-900 hover:bg-gray-200 hover:text-white flex gap-3 justify-between"
        >
          <div>
            <p>
              <span class="text-sm text-gray-600">ID: {{ project.id }}</span>
            </p>
            <p>
              <span class="text-sm text-gray-600">Status: {{ project.status }}</span>
            </p>
            <p></p>
          </div>
          <div>
            <p>
              <Link
                v-if="project.status === 'Draft'"
                :href="route('story.continue', project.id)"
                class="text-sm bg-green-400 hover:bg-green-600 text-white px-2 py-1 rounded shadow"
              >
                Edit
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
