<script setup lang="ts">
  import { Head, Link, useForm } from '@inertiajs/vue3'
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { Paginator, User } from '@/types'
  import PaginatorLinks from '@/Components/Search/PaginatorLinks.vue'

  defineProps<{
    users: User[]
    paginator: Paginator<User>
  }>()

  const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
  })

  const clearFields = () => {
    form.first_name = ''
    form.last_name = ''
    form.email = ''
  }
</script>

<template>
  <Head title="Browse Users" />
  <AuthenticatedLayout>
    <div class="py-6 md:py12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid-cols-1 md:grid-cols-2 grid gap-4">
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-4">
          <h1 class="text-4xl text-gray-900">Browse Users</h1>
          <p class="text-gray-600">Browse all users in the system. You can edit or delete users from this page.</p>
          <p>
            <Link :href="route('admin.dashboard')" class="underline text-sm text-gray-600 hover:text-gray-900">
              &laquo; Back to Admin Home
            </Link>
          </p>
        </section>
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-12">
          <div class="space-y-4">
            <h2 class="text-2xl text-gray-900">Search Filters</h2>
            <p class="text-gray-600">Use the search filters below to find users.</p>
            <!-- Search filters go here -->
            <form @submit.prevent="form.post(route('admin.users.browse.post'))">
              <div class="flex flex-col gap-3">
                <div class="w-full">
                  <label class="sr-only" for="grid-first-name"> First Name </label>
                  <input
                    id="grid-first-name"
                    v-model="form.first_name"
                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                    type="text"
                    placeholder="First Name"
                  />
                </div>
                <div class="w-full">
                  <label class="sr-only" for="grid-last-name"> Last Name </label>
                  <input
                    id="grid-last-name"
                    v-model="form.last_name"
                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                    type="text"
                    placeholder="Last Name"
                  />
                </div>
                <div class="w-full">
                  <label class="sr-only" for="grid-email"> Email </label>
                  <input
                    id="grid-email"
                    v-model="form.email"
                    class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                    type="email"
                    placeholder="Email"
                  />
                </div>
                <div class="w-full flex justify-end gap-2">
                  <!-- Search button -->
                  <button
                    type="submit"
                    class="bg-primary hover:bg-secondary text-primary-content hover:text-secondary-content font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                  >
                    Search
                  </button>
                  <!-- Clear button -->
                  <button
                    type="button"
                    class="bg-neutral hover:bg-secondary text-neutral-content hover:text-secondary-content font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    @click="clearFields"
                  >
                    Clear
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-12 col-span-2">
          <div class="space-y-4">
            <h2 class="text-2xl text-gray-900">Search Results</h2>
            <p class="text-gray-600">The results of your search are displayed below.</p>
          </div>
          <div v-if="users.length > 0" class="space-y-4">
            <table class="table-auto w-full">
              <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal text-left">
                  <th class="px-4 py-2">First Name</th>
                  <th class="px-4 py-2">Last Name</th>
                  <th class="px-4 py-2">Email</th>
                  <th class="px-4 py-2">Verified</th>
                  <th class="px-4 py-2">Role</th>
                  <th class="px-4 py-2">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td class="border px-4 py-2">{{ user.first_name }}</td>
                  <td class="border px-4 py-2">{{ user.last_name }}</td>
                  <td class="border px-4 py-2">{{ user.email }}</td>
                  <td class="border px-4 py-2">
                    <span v-if="user.email_verified_at" class="text-green-500">Yes</span>
                    <span v-else class="text-red-500">No</span>
                  </td>
                  <td class="border px-4 py-2">{{ user.roles.join(', ') }}</td>
                  <td class="border px-4 py-2">
                    <div class="flex flex-wrap gap-2">
                      <Link
                        v-if="user.email_verified_at"
                        class="text-sm bg-primary text-primary-content hover:bg-secondary hover:text-secondary-content px-2 py-1 rounded shadow"
                        :href="route('admin.users.show', user.id)"
                      >
                        View
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <PaginatorLinks :links="paginator.links" />
          </div>
          <div v-else class="space-y-4">
            <p class="text-gray-600">No users found.</p>
          </div>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped lang="postcss"></style>
