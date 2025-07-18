<script setup lang="ts">
  import GuestLayout from '@/Layouts/GuestLayout.vue'
  import InputError from '@/Components/Form/FormError.vue'
  import InputLabel from '@/Components/Form/FormLabel.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import InputField from '@/Components/Form/FormField.vue'
  import { Head, useForm } from '@inertiajs/vue3'

  defineProps<{
    status?: string
  }>()

  const form = useForm({
    email: '',
  })

  const submit = () => {
    form.post(route('password.email'))
  }
</script>

<template>
  <GuestLayout>
    <Head title="Forgot Password" />

    <div class="mb-3 text-sm text-gray-600">
      Forgot your password? No problem. Just let us know your email address and we will email you a password reset link
      that will allow you to choose a new one.
    </div>

    <div v-if="status" class="mb-3 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" value="Email" />

        <InputField
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-1" :message="form.errors.email" />
      </div>

      <div class="flex items-center justify-end mt-3">
        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Email Password Reset Link
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
