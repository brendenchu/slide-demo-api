<script lang="ts" setup>
  import { ref } from 'vue'
  import Dropdown from '@/Components/DropdownMenu.vue'
  import DropdownLink from '@/Components/DropdownLink.vue'
  import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
  import FlashProvider from '@/Components/Flash/FlashProvider.vue'
  import PageFooter from '@/Components/PageFooter.vue'

  const showingNavigationDropdown = ref(false)
</script>

<template>
  <FlashProvider>
    <div class="min-h-screen bg-gray-100 flex flex-col">
      <nav class="bg-white border-b border-gray-100 z-40">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <h1 class="text-2xl font-bold text-gray-900">Slide Form Demo</h1>
              </div>
            </div>
            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                        type="button"
                      >
                        <svg
                          fill="currentColor"
                          height="24"
                          viewBox="0 0 512 512"
                          width="24"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <!--!Font Awesome Pro 6.5.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc.-->
                          <path
                            class="fa-secondary"
                            d="M399 384.2C376.9 345.8 335.4 320 288 320H224c-47.4 0-88.9 25.8-111 64.2c35.2 39.2 86.2 63.8 143 63.8s107.8-24.7 143-63.8zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256 16a72 72 0 1 0 0-144 72 72 0 1 0 0 144z"
                            opacity=".4"
                          />
                          <path
                            class="fa-primary"
                            d="M256 272a72 72 0 1 0 0-144 72 72 0 1 0 0 144zm0 176c56.8 0 107.8-24.7 143-63.8C376.9 345.8 335.4 320 288 320H224c-47.4 0-88.9 25.8-111 64.2c35.2 39.2 86.2 63.8 143 63.8z"
                          />
                        </svg>
                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            clip-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            fill-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>
                  <template #content>
                    <!-- Authentication -->
                    <div class="flex flex-col px-4 py-2 text-md border-b">
                      <span class="font-bold">{{
                        `${$page.props.auth.user.first_name} ${$page.props.auth.user.last_name}`
                      }}</span>
                      <small class="text-gray-600">{{ $page.props.auth.user.email }}</small>
                    </div>
                    <DropdownLink :href="route('profile.edit')"> Profile</DropdownLink>
                    <DropdownLink :href="route('logout')" as="button" method="post"> Log Out </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>
            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
              <button
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                @click="showingNavigationDropdown = !showingNavigationDropdown"
              >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    d="M4 6h16M4 12h16M4 18h16"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    d="M6 18L18 6M6 6l12 12"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
              <div class="font-medium text-base text-gray-800">
                {{ $page.props.auth.user.first_name }}
              </div>
              <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')"> Profile</ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" as="button" method="post"> Log Out </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header v-if="$slots.header" class="bg-white shadow z-30">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="stretched">
        <slot />
      </main>

      <!-- Page Footer -->
      <PageFooter />
    </div>
  </FlashProvider>
</template>
