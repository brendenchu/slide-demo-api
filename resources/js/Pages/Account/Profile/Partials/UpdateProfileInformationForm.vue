<script setup lang="ts">
  import InputError from '@/Components/Form/FormError.vue'
  import InputLabel from '@/Components/Form/FormLabel.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import InputField from '@/Components/Form/FormField.vue'
  import { Link, useForm, usePage } from '@inertiajs/vue3'

  defineProps<{
    mustVerifyEmail?: boolean
    status?: string
  }>()

  const user = usePage().props.auth.user

  const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    email: user.email,
  })
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>

      <p class="mt-1 text-sm text-gray-600">Update your account's profile information and email address.</p>
    </header>

    <form class="mt-4 space-y-6" @submit.prevent="form.patch(route('profile.update'))">
      <div>
        <InputLabel for="first_name" value="First Name" />

        <InputField
          id="first_name"
          v-model="form.first_name"
          type="text"
          class="mt-1 block w-full"
          required
          autofocus
          autocomplete="name"
        />

        <InputError class="mt-1" :message="form.errors.first_name" />
      </div>

      <div>
        <InputLabel for="last_name" value="Last Name" />

        <InputField
          id="last_name"
          v-model="form.last_name"
          type="text"
          class="mt-1 block w-full"
          required
          autocomplete="name"
        />

        <InputError class="mt-1" :message="form.errors.last_name" />
      </div>

      <div>
        <InputLabel for="email" value="Email" />

        <InputField
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
          required
          autocomplete="username"
        />

        <InputError class="mt-1" :message="form.errors.email" />
      </div>

      <div v-if="mustVerifyEmail && user.email_verified_at === null">
        <p class="text-sm mt-1 text-gray-800">
          Your email address is unverified.
          <Link
            :href="route('verification.send')"
            method="post"
            as="button"
            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Click here to re-send the verification email.
          </Link>
        </p>

        <div v-show="status === 'verification-link-sent'" class="mt-1 font-medium text-sm text-green-600">
          A new verification link has been sent to your email address.
        </div>
      </div>

      <div class="flex items-center gap-3">
        <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
        </Transition>
      </div>
    </form>
  </section>
</template>
