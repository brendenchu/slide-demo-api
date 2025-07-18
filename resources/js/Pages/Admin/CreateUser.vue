<script setup lang="ts">
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import CreateUserForm from './Partials/CreateUserForm.vue'
  import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Create New User" />
  <AuthenticatedLayout>
    <div class="py-6 md:py12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid-cols-1 md:grid-cols-2 grid gap-4">
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-4">
          <h1 class="text-4xl text-gray-900">Create New User</h1>
          <p class="text-gray-600">
            Welcome to the user setup page. Here you can create a new user account. This will send an email to the user
            with a link to reset their password.
          </p>
          <p>
            <Link :href="route('admin.dashboard')" class="underline text-sm text-gray-600 hover:text-gray-900">
              &laquo; Back to Admin Home
            </Link>
          </p>
        </section>
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
          <CreateUserForm />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
