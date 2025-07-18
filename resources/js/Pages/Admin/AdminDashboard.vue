<script setup lang="ts">
  import { Head } from '@inertiajs/vue3'
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import LookupUserForm from './Partials/LookupUserForm.vue'

  const goToCreateUser = () => {
    window.location.href = route('admin.users.create')
  }

  const goToBrowseUsers = () => {
    window.location.href = route('admin.users.index')
  }
</script>

<template>
  <Head title="Admin Home" />
  <AuthenticatedLayout>
    <div class="py-6 md:py12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Introduction -->
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-12 md:col-span-2">
          <div class="space-y-4">
            <h1 class="text-4xl text-gray-900">Hello, {{ $page.props.auth.user.first_name }}.</h1>
            <p class="text-gray-600">
              Welcome to the Slide Form Demo admin home. As an admin, you have access to all the features of the
              application.
            </p>
          </div>
        </section>
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-12 md:col-span-2">
          <div class="space-y-4">
            <h2 class="text-2xl text-gray-900">Browse Users</h2>
            <p class="text-gray-600">Browse all users in the system. You can edit or delete users from this page.</p>
            <PrimaryButton @click="goToBrowseUsers">Browse Users</PrimaryButton>
          </div>
          <div class="space-y-4">
            <h2 class="text-2xl text-gray-900">Manage Existing User</h2>
            <p class="text-gray-600">
              Find user by email address. This will load the user's profile and dashboard where you can manage their
              account.
            </p>
            <LookupUserForm />
          </div>
          <div class="space-y-4">
            <h2 class="text-2xl text-gray-900">Create New User</h2>
            <p class="text-gray-600">
              Create a new user account. This will send an email to the user with a link to reset their password.
            </p>
            <PrimaryButton @click="goToCreateUser">New User</PrimaryButton>
          </div>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped lang="postcss"></style>
