<script setup lang="ts">
  import { Link } from '@inertiajs/vue3'
</script>

<template>
  <section class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <header>
      <h2 class="text-lg font-medium text-gray-900">{{ $page.props.auth.team.name }}</h2>
      <p class="mt-1 text-sm text-gray-600">This is the current team you are working in.</p>
    </header>
    <p class="mt-1 text-sm text-gray-500">
      <Link :href="route('team.select')" class="font-medium text-indigo-600 hover:text-indigo-500"> Change Team</Link>
    </p>
  </section>
</template>

<style scoped></style>
