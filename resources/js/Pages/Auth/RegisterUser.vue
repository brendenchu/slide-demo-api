<script setup lang="ts">
  import GuestLayout from '@/Layouts/GuestLayout.vue'
  import { Head } from '@inertiajs/vue3'
  import CreateUserForm from '@/Pages/Admin/Partials/CreateUserForm.vue'
</script>

<template>
  <GuestLayout>
    <Head title="Register" />
    <CreateUserForm signup />
  </GuestLayout>
</template>
