<script setup lang="ts">
  import { Link, useForm } from '@inertiajs/vue3'
  import InputError from '@/Components/Form/FormError.vue'
  import InputField from '@/Components/Form/FormField.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import InputLabel from '@/Components/Form/FormLabel.vue'

  const props = withDefaults(
    defineProps<{
      signup?: boolean
    }>(),
    {
      signup: false,
    }
  )

  const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
  })

  const submit = () => {
    form.post(route(props.signup ? 'register' : 'admin.users.store'), {
      preserveScroll: true,
      onFinish: () => {
        if (props.signup) {
          form.reset('password', 'password_confirmation')
        }
      },
    })
  }
</script>

<template>
  <form @submit.prevent="submit">
    <div>
      <InputLabel for="first_name" value="First Name" />

      <InputField id="first_name" v-model="form.first_name" type="text" required autofocus autocomplete="name" />

      <InputError class="mt-1" :message="form.errors.first_name" />
    </div>

    <div class="mt-3">
      <InputLabel for="last_name" value="Last Name" />

      <InputField id="last_name" v-model="form.last_name" type="text" required autofocus autocomplete="name" />

      <InputError class="mt-1" :message="form.errors.last_name" />
    </div>

    <div class="mt-3">
      <InputLabel for="email" value="Email" />

      <InputField id="email" v-model="form.email" type="email" required autocomplete="username" />

      <InputError class="mt-1" :message="form.errors.email" />
    </div>

    <template v-if="signup">
      <div class="mt-3">
        <InputLabel for="password" value="Password" />

        <InputField id="password" v-model="form.password" type="password" required autocomplete="new-password" />

        <InputError class="mt-1" :message="form.errors.password" />
      </div>

      <div class="mt-3">
        <InputLabel for="password_confirmation" value="Confirm Password" />

        <InputField
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          autocomplete="new-password"
        />

        <InputError class="mt-1" :message="form.errors.password_confirmation" />
      </div>

      <div class="flex items-center justify-end mt-3">
        <Link
          :href="route('login')"
          class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Already registered?
        </Link>

        <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Register
        </PrimaryButton>
      </div>
    </template>
    <template v-else>
      <div class="mt-3">
        <InputLabel for="role" value="Role" />

        <select id="role" v-model="form.role" class="select select-bordered w-full select-sm p-0 px-3">
          <option value="" disabled>Select a role</option>
          <option value="admin">Admin</option>
          <option value="consultant">Consultant</option>
          <option value="client">Client</option>
        </select>

        <InputError class="mt-1" :message="form.errors.role" />
      </div>
      <div class="mt-3">
        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Create User
        </PrimaryButton>
      </div>
    </template>
  </form>
</template>

<style scoped lang="postcss"></style>
