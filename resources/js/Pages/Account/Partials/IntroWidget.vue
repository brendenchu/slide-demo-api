<script setup lang="ts"></script>

<template>
  <section class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <header>
      <h2 class="text-lg font-medium text-gray-900">
        Welcome to your dashboard, {{ $page.props.auth.user.first_name }}.
      </h2>

      <p class="mt-1 text-sm text-gray-600">
        This provides a quick overview of your account and the projects you have created.
      </p>
    </header>
  </section>
</template>

<style scoped></style>
