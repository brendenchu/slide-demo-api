<script setup lang="ts">
  import { defineProps } from 'vue'
  import { PaginatorLinks } from '@/types'
  import { Link } from '@inertiajs/vue3'

  defineProps<{
    links: PaginatorLinks
  }>()
</script>

<template>
  <div v-if="links.length">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, key) in links">
        <div
          v-if="link.url === null"
          :key="`pagination-no-link-${key}`"
          class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
        >
          {{ link.label }}
        </div>
        <Link
          v-else
          :key="`pagination-link-${key}`"
          preserve-scroll
          class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
          :class="{ 'bg-white': link.active }"
          :href="link.url"
        >
          {{ link.label }}
        </Link>
      </template>
    </div>
  </div>
</template>

<style scoped lang="postcss"></style>
