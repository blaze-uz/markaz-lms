<template>
  <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 no-select">
    <!-- Top Bar with Logo -->
    <div class="z-50 top-bar">
      <header
        class="sticky top-0 w-full backdrop-blur-xl bg-white/80 dark:bg-gray-900/80 border-b border-white/20 dark:border-gray-700/30 shadow-sm"
      >
        <div class="px-4 py-3">
          <div class="flex justify-center items-center">
            <div class="flex flex-1 justify-center items-center">
              <img :src="settings.logo_url" alt="Logo" class="w-auto h-8" />
            </div>
          </div>
        </div>
      </header>
    </div>

    <!-- Main Content -->
    <main class="flex-1 px-4 py-4">
      <slot></slot>
    </main>

    <!-- Bottom Navigation -->
    <nav class="sticky bottom-0 z-50 pb-safe">
      <div
        class="backdrop-blur-2xl bg-white/70 dark:bg-gray-900/70 border-t border-white/30 dark:border-gray-700/30 shadow-2xl rounded-t-3xl mx-2 mb-2 overflow-hidden"
      >
        <div class="flex justify-around items-center py-2">
          <Link
            v-for="(item, index) in navigation"
            :key="index"
            :href="item.href"
            class="flex flex-col flex-1 justify-center items-center p-3 rounded-2xl mx-1 transition-all duration-200"
            :class="{
              'bg-blue-500/20 dark:bg-blue-500/30 text-blue-600 dark:text-blue-400 scale-105': $page.url.startsWith(
                item.href
              ),
              'text-gray-600 dark:text-gray-400 hover:bg-gray-100/50 dark:hover:bg-gray-800/50': !$page.url.startsWith(
                item.href
              ),
            }"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-6 h-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                v-if="item.name === 'Asosiy'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
              <path
                v-if="item.name === 'Davomat'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
              <path
                v-if="item.name === 'To\'lovlar'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2"
              />
              <path
                v-if="item.name === 'Sozlamalar'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
            <span class="mt-1 text-xs font-medium">{{ item.name }}</span>
          </Link>
        </div>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { onMounted } from "vue";

const props = defineProps({
  settings: {
    type: Object,
    required: true,
  },
});

const navigation = [
  { name: "Asosiy", href: "/twa/dashboard" },
  { name: "Davomat", href: "/twa/attendance" },
  { name: "To'lovlar", href: "/twa/payments" },
  { name: "Sozlamalar", href: "/twa/settings" },
];

onMounted(() => {
  // Expand to full screen and add initial haptic
  if (window.Telegram?.WebApp) {
    window.Telegram.WebApp.expand();
    window.Telegram.WebApp.HapticFeedback.notificationOccurred("success");
  }
});

const handleMenuClick = () => {
  // Trigger haptic feedback
  if (window.Telegram?.WebApp) {
    window.Telegram.WebApp.HapticFeedback.impactOccurred("light");
  }
};
</script>

<style>
.text-primary {
  color: #2563eb; /* Tailwind blue-600 */
}

.top-bar {
  position: sticky !important;
  top: 0 !important;
  z-index: 50 !important;
}

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
