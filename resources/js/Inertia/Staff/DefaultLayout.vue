<template>
  <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 no-select">
    <!-- Main Content -->
    <main class="flex-1 px-4 py-4">
      <slot />
    </main>

    <!-- Bottom Navigation -->
    <nav class="sticky bottom-0 z-50 pb-safe">
      <div
        class="backdrop-blur-2xl bg-white/70 dark:bg-gray-900/70 border-t border-white/30 dark:border-gray-700/30 shadow-2xl rounded-t-3xl mx-2 mb-2 overflow-hidden"
      >
        <div class="flex justify-around items-center py-2">
          <Link
            href="/inertia-teachers"
            class="flex flex-col flex-1 justify-center items-center p-3 rounded-2xl mx-1 transition-all duration-200"
            :class="{
              'bg-blue-500/20 dark:bg-blue-500/30 text-blue-600 dark:text-blue-400 scale-105': activeMenu === 'home',
              'text-gray-600 dark:text-gray-400 hover:bg-gray-100/50 dark:hover:bg-gray-800/50': activeMenu !== 'home',
            }"
            @click="triggerHapticFeedback"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-6 h-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
            </svg>
            <span class="mt-1 text-xs font-medium">Asosiy</span>
          </Link>

          <Link
            href="/inertia-teachers/groups"
            class="flex flex-col flex-1 justify-center items-center p-3 rounded-2xl mx-1 transition-all duration-200"
            :class="{
              'bg-blue-500/20 dark:bg-blue-500/30 text-blue-600 dark:text-blue-400 scale-105': activeMenu === 'groups',
              'text-gray-600 dark:text-gray-400 hover:bg-gray-100/50 dark:hover:bg-gray-800/50': activeMenu !== 'groups',
            }"
            @click="triggerHapticFeedback"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-6 h-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
            <span class="mt-1 text-xs font-medium">Guruhlar</span>
          </Link>

          <Link
            href="/inertia-teachers/settings"
            class="flex flex-col flex-1 justify-center items-center p-3 rounded-2xl mx-1 transition-all duration-200"
            :class="{
              'bg-blue-500/20 dark:bg-blue-500/30 text-blue-600 dark:text-blue-400 scale-105': activeMenu === 'settings',
              'text-gray-600 dark:text-gray-400 hover:bg-gray-100/50 dark:hover:bg-gray-800/50': activeMenu !== 'settings',
            }"
            @click="triggerHapticFeedback"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-6 h-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
            <span class="mt-1 text-xs font-medium">Sozlamalar</span>
          </Link>
        </div>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";

const page = usePage();

// Compute active menu based on current URL
const activeMenu = computed(() => {
  const path = page.url.split("?")[0];
  if (path === "/inertia-teachers") return "home";
  if (path.startsWith("/inertia-teachers/groups")) return "groups";
  if (path.includes("settings")) return "settings";
  return "home";
});

// Function to get the Telegram Web App instance
const getTelegramWebApp = () => {
  return window.Telegram?.WebApp;
};

// Initialize Telegram Web App
onMounted(() => {
  const webApp = getTelegramWebApp();
  if (webApp) {
    // Expand to full height
    webApp.expand();
    // Enable smooth scrolling
    webApp.enableClosingConfirmation();
    // Set header color to match design
    webApp.setHeaderColor("#F5F5F5");
    // Set background color
    webApp.setBackgroundColor("#F5F5F5");
  }
});

// Function to trigger haptic feedback
const triggerHapticFeedback = () => {
  const webApp = getTelegramWebApp();
  if (webApp?.HapticFeedback) {
    try {
      webApp.HapticFeedback.impactOccurred("light");
    } catch (e) {
      console.warn("HapticFeedback not supported:", e);
    }
  }
};
</script>

<style>
.no-select {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -webkit-touch-callout: none;
}

.backdrop-blur-2xl {
  backdrop-filter: blur(40px);
  -webkit-backdrop-filter: blur(40px);
}

.backdrop-blur-xl {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

.pb-safe {
  padding-bottom: env(safe-area-inset-bottom, 0);
}

@supports (backdrop-filter: blur(40px)) or (-webkit-backdrop-filter: blur(40px)) {
  .backdrop-blur-2xl {
    background-color: rgba(255, 255, 255, 0.7);
}

  .dark .backdrop-blur-2xl {
    background-color: rgba(17, 24, 39, 0.7);
  }
}
</style>
