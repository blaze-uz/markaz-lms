<template>
  <StudentLayout :settings="settings">
    <div class="space-y-4 pb-20">
      <!-- Header -->
      <div
        class="overflow-hidden relative backdrop-blur-xl bg-gradient-to-br from-slate-500 via-gray-600 to-zinc-700 rounded-3xl shadow-2xl"
      >
        <div class="absolute inset-0 bg-white/10"></div>
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12 blur-xl"
        ></div>
        <div class="relative px-6 py-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-2xl font-bold text-white mb-1">Sozlamalar</h1>
              <p class="text-sm font-medium text-white/80">
                Profil va ko'rinish sozlamalari
              </p>
            </div>
            <div
              class="flex items-center justify-center w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-7 h-7 text-white"
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
            </div>
          </div>
        </div>
      </div>

      <!-- Display Name -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Profil</h2>
        </div>
        <div class="p-5">
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
              <label
                for="display_name"
                class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white"
              >
                Ism familiya
              </label>
              <div class="relative">
                <input
                  type="text"
                  id="display_name"
                  v-model="form.display_name"
                  class="block w-full appearance-none rounded-xl border bg-white/80 dark:bg-gray-700/80 backdrop-blur-sm px-4 py-3 text-sm font-medium text-gray-900 dark:text-white shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                  :class="{
                    'border-red-300 dark:border-red-500': form.errors.display_name,
                    'border-gray-200 dark:border-gray-600': !form.errors.display_name,
                  }"
                  placeholder="Ismingizni kiriting"
                />
              </div>
              <p
                v-if="form.errors.display_name"
                class="mt-2 text-xs text-red-600 dark:text-red-400"
              >
                {{ form.errors.display_name }}
              </p>
            </div>
            <div class="flex justify-end pt-2">
              <button
                type="submit"
                class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold text-white bg-blue-600 dark:bg-blue-500 rounded-xl shadow-lg transition-all duration-200 hover:bg-blue-700 dark:hover:bg-blue-600 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="form.processing || !form.display_name.trim()"
              >
                <span v-if="form.processing">Saqlanmoqda...</span>
                <span v-else>Saqlash</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Appearance -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ko'rinish</h2>
        </div>
        <div class="p-4 space-y-3">
          <div
            class="relative flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer backdrop-blur-sm"
            :class="{
              'border-blue-500 bg-blue-50/80 dark:bg-blue-900/30': appearance === 'light',
              'border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 hover:border-gray-300 dark:hover:border-gray-600':
                appearance !== 'light',
            }"
            @click="updateAppearance('light')"
          >
            <div class="flex items-center gap-3">
              <div
                class="flex items-center justify-center w-11 h-11 rounded-xl transition-colors"
                :class="{
                  'bg-blue-500/20 dark:bg-blue-500/30': appearance === 'light',
                  'bg-gray-100 dark:bg-gray-700': appearance !== 'light',
                }"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-6 h-6"
                  :class="{
                    'text-blue-600 dark:text-blue-400': appearance === 'light',
                    'text-gray-500 dark:text-gray-400': appearance !== 'light',
                  }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                  />
                </svg>
              </div>
              <div>
                <p
                  class="text-sm font-semibold"
                  :class="{
                    'text-blue-600 dark:text-blue-400': appearance === 'light',
                    'text-gray-900 dark:text-white': appearance !== 'light',
                  }"
                >
                  Yorug'
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Yorug' rejim</p>
              </div>
            </div>
            <div
              v-if="appearance === 'light'"
              class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500"
            >
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div
              v-else
              class="flex items-center justify-center w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600"
            ></div>
          </div>

          <div
            class="relative flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer backdrop-blur-sm"
            :class="{
              'border-blue-500 bg-blue-50/80 dark:bg-blue-900/30': appearance === 'dark',
              'border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 hover:border-gray-300 dark:hover:border-gray-600':
                appearance !== 'dark',
            }"
            @click="updateAppearance('dark')"
          >
            <div class="flex items-center gap-3">
              <div
                class="flex items-center justify-center w-11 h-11 rounded-xl transition-colors"
                :class="{
                  'bg-blue-500/20 dark:bg-blue-500/30': appearance === 'dark',
                  'bg-gray-100 dark:bg-gray-700': appearance !== 'dark',
                }"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-6 h-6"
                  :class="{
                    'text-blue-600 dark:text-blue-400': appearance === 'dark',
                    'text-gray-500 dark:text-gray-400': appearance !== 'dark',
                  }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                  />
                </svg>
              </div>
              <div>
                <p
                  class="text-sm font-semibold"
                  :class="{
                    'text-blue-600 dark:text-blue-400': appearance === 'dark',
                    'text-gray-900 dark:text-white': appearance !== 'dark',
                  }"
                >
                  Qorong'i
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Qorong'i rejim</p>
              </div>
            </div>
            <div
              v-if="appearance === 'dark'"
              class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500"
            >
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div
              v-else
              class="flex items-center justify-center w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600"
            ></div>
          </div>

          <div
            class="relative flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer backdrop-blur-sm"
            :class="{
              'border-blue-500 bg-blue-50/80 dark:bg-blue-900/30':
                appearance === 'system',
              'border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 hover:border-gray-300 dark:hover:border-gray-600':
                appearance !== 'system',
            }"
            @click="updateAppearance('system')"
          >
            <div class="flex items-center gap-3">
              <div
                class="flex items-center justify-center w-11 h-11 rounded-xl transition-colors"
                :class="{
                  'bg-blue-500/20 dark:bg-blue-500/30': appearance === 'system',
                  'bg-gray-100 dark:bg-gray-700': appearance !== 'system',
                }"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-6 h-6"
                  :class="{
                    'text-blue-600 dark:text-blue-400': appearance === 'system',
                    'text-gray-500 dark:text-gray-400': appearance !== 'system',
                  }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <div>
                <p
                  class="text-sm font-semibold"
                  :class="{
                    'text-blue-600 dark:text-blue-400': appearance === 'system',
                    'text-gray-900 dark:text-white': appearance !== 'system',
                  }"
                >
                  Tizim
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Tizim sozlamasiga moslashadi
                </p>
              </div>
            </div>
            <div
              v-if="appearance === 'system'"
              class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500"
            >
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div
              v-else
              class="flex items-center justify-center w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import StudentLayout from "../Layouts/StudentLayout.vue";

const props = defineProps({
  settings: Object,
  student: Object,
  telegraphChat: Object,
  errors: Object,
});

const appearance = ref(localStorage.getItem("appearance") || "system");
const form = useForm({
  display_name: props.telegraphChat?.display_name || "",
});

const updateProfile = () => {
  form.patch("/twa/settings/profile", {
    preserveScroll: true,
    onSuccess: () => {
      if (window.Telegram?.WebApp?.HapticFeedback) {
        window.Telegram.WebApp.HapticFeedback.notificationOccurred("success");
      }
      window.Telegram.WebApp.showPopup({
        title: "Muvaffaqiyatli",
        message: "Profil yangilandi",
        buttons: [{ type: "ok" }],
      });
    },
    onError: () => {
      if (window.Telegram?.WebApp?.HapticFeedback) {
        window.Telegram.WebApp.HapticFeedback.notificationOccurred("error");
      }
      window.Telegram.WebApp.showPopup({
        title: "Xatolik",
        message: "Profil yangilanmadi. Qaytadan urinib ko'ring",
        buttons: [{ type: "ok" }],
      });
    },
  });
};

const updateAppearance = (value) => {
  // Add haptic feedback
  if (window.Telegram?.WebApp) {
    window.Telegram.WebApp.HapticFeedback.impactOccurred("medium");
  }

  appearance.value = value;
  localStorage.setItem("appearance", value);

  // Apply theme
  document.documentElement.classList.remove("light", "dark");
  if (value === "system") {
    applySystemTheme();
  } else {
    document.documentElement.classList.add(value);
  }

  // Haptic feedback
  if (window.Telegram?.WebApp?.HapticFeedback) {
    window.Telegram.WebApp.HapticFeedback.selectionChanged();
  }
};

const applySystemTheme = () => {
  if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
    document.documentElement.classList.add("dark");
  } else {
    document.documentElement.classList.add("light");
  }
};

// Initialize theme
if (appearance.value === "system") {
  applySystemTheme();
} else {
  document.documentElement.classList.add(appearance.value);
}

// Listen for system theme changes
window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
  if (appearance.value === "system") {
    applySystemTheme();
  }
});
</script>
