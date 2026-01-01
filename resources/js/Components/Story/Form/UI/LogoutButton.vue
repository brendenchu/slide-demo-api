<script setup lang="ts">
  import { Link } from '@inertiajs/vue3'
</script>

<template>
  <!-- To the Dashboard button -->
  <Link :href="route('logout')" method="post" as="button" class="btn btn-outline btn-primary btn-sm rounded-btn">
    <span> Log Out </span>
  </Link>
</template>

<style scoped lang="postcss"></style>
