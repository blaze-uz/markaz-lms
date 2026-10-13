<script setup>
import { computed, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";

// Shown in place of any mini-app page until this Telegram session is verified:
// the server only trusts the initData Telegram signed, never a telegram_id in the URL.
const props = defineProps({
  next: {
    type: String,
    default: "/inertia-teachers",
  },
  error: {
    type: String,
    default: null,
  },
});

const notInTelegram = ref(false);
const message = computed(() =>
  props.error ?? (notInTelegram.value ? "Ilovani Telegram bot orqali oching." : null)
);

onMounted(() => {
  // A refused attempt is shown, not retried.
  if (props.error) return;

  const webApp = window.Telegram?.WebApp;
  if (!webApp?.initData) {
    notInTelegram.value = true;
    return;
  }

  webApp.ready?.();
  router.post(
    "/inertia-teachers/auth",
    { init_data: webApp.initData, next: props.next },
    { replace: true }
  );
});
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-6"
  >
    <div class="max-w-sm w-full text-center space-y-4">
      <template v-if="message">
        <p class="text-lg font-semibold text-gray-900 dark:text-white">
          Kirish imkoni yo'q
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ message }}</p>
      </template>
      <template v-else>
        <div
          class="mx-auto h-10 w-10 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"
        ></div>
        <p class="text-sm text-gray-600 dark:text-gray-400">Tekshirilmoqda...</p>
      </template>
    </div>
  </div>
</template>
