<script lang="ts" setup>
  import GuestLayout from '@/Layouts/GuestLayout.vue'
  import InputError from '@/Components/Form/FormError.vue'
  import InputLabel from '@/Components/Form/FormLabel.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import InputField from '@/Components/Form/FormField.vue'
  import { Head, useForm } from '@inertiajs/vue3'

  defineProps<{
    canResetPassword?: boolean
    status?: string
  }>()

  const form = useForm({
    email: '',
    password: '',
    remember: false,
  })

  const submit = () => {
    form.post(route('login'), {
      onFinish: () => {
        form.reset('password')
      },
    })
  }
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />
    <div v-if="status" class="mb-3 font-medium text-sm text-green-600">
      {{ status }}
    </div>
    <div class="mb-3">
      <small>Enter 'guest' as the username and password to log in as a guest.</small>
    </div>
    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" value="Email or Username" />
        <InputField
          id="email"
          v-model="form.email"
          autocomplete="username"
          autofocus
          class="mt-1 block w-full"
          required
          type="text"
        />
        <InputError :message="form.errors.email" class="mt-1" />
      </div>

      <div class="mt-3">
        <InputLabel for="password" value="Password" />
        <InputField
          id="password"
          v-model="form.password"
          autocomplete="current-password"
          class="mt-1 block w-full"
          required
          type="password"
        />
        <InputError :message="form.errors.password" class="mt-1" />
      </div>
      <div class="w-full items-center mt-6">
        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="btn-outline">
          Log In
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
