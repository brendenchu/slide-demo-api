<script setup lang="ts">
  import { Project, Token, User } from '@/types'
  import { Head, Link } from '@inertiajs/vue3'
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

  defineProps<{
    user: User
    projects: Project[]
    tokens: Token[]
  }>()
</script>

<template>
  <Head title="Client Profile" />
  <AuthenticatedLayout>
    <div class="py-6 md:py12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-4">
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-12">
          <div class="space-y-4">
            <h1 class="text-4xl text-gray-900">{{ user.name }}</h1>
            <p class="text-gray-600">
              This is the user's profile page. Here you can view and edit the user's information.
            </p>
            <p>
              <Link :href="route('admin.dashboard')" class="underline text-sm text-gray-600 hover:text-gray-900">
                &laquo; Back to Admin Home
              </Link>
            </p>
          </div>
          <div v-show="tokens" class="space-y-4">
            <h2 class="text-2xl text-gray-900">Dashboard</h2>
            <p class="text-gray-600">
              The dashboard presents processed data and calculations from the user's responses in the Cost Gathering
              Form.
            </p>
            <div v-for="token in tokens" :key="token.id" class="flex flex-wrap gap-2">
              <Link
                v-if="token.project.status === 'Published'"
                class="text-sm bg-primary text-primary-content hover:bg-secondary hover:text-secondary-content px-2 py-1 rounded shadow"
                :href="
                  route('story.dashboard', {
                    project: token.project.id,
                    token: token.id,
                  })
                "
              >
                View Dashboard
              </Link>
              <Link
                class="text-sm bg-primary text-primary-content hover:bg-secondary hover:text-secondary-content px-2 py-1 rounded shadow"
                :href="
                  route('story.continue', {
                    project: token.project.id,
                    token: token.id,
                  })
                "
              >
                Edit Responses
              </Link>
            </div>
          </div>
        </section>
        <section class="p-8 md:p-16 bg-white shadow sm:rounded-lg space-y-12">
          <div class="space-y-4">
            <h2 class="text-2xl text-gray-900">Profile</h2>
            <div class="flex flex-col gap-3">
              <div class="flex gap-2">
                <div class="w-1/5">
                  <strong>First Name</strong>
                </div>
                <div class="w-4/5">{{ user.first_name }}</div>
              </div>
              <div class="flex gap-2">
                <div class="w-1/5">
                  <strong>Last Name</strong>
                </div>
                <div class="w-4/5">{{ user.last_name }}</div>
              </div>
              <div class="flex gap-2">
                <div class="w-1/5">
                  <strong>Email</strong>
                </div>
                <div class="w-4/5">{{ user.email }}</div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped lang="postcss"></style>
