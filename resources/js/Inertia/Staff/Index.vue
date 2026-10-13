<template>
  <DefaultLayout>
    <div class="space-y-4 pb-24">
      <!-- Welcome Section with Teacher Name -->
      <div
        class="overflow-hidden relative backdrop-blur-xl bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 rounded-3xl shadow-2xl"
      >
        <div class="absolute inset-0 bg-white/10"></div>
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12 blur-xl"
        ></div>
        <div class="relative px-5 py-7">
          <div class="flex items-center justify-between mb-2">
            <div>
              <p class="text-sm font-medium text-white/80 mb-1">{{ getTimeGreeting() }}</p>
              <h1 class="text-2xl font-bold text-white">
                {{ user.name }}
              </h1>
            </div>
            <span class="inline-block text-4xl animate-wave">👋</span>
          </div>
        </div>
      </div>

      <!-- Quick Stats Grid -->
      <div class="grid grid-cols-2 gap-3 mt-1">
        <!-- Today's Groups Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <div
                class="flex items-center justify-center w-10 h-10 bg-blue-500/20 dark:bg-blue-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-blue-600 dark:text-blue-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Bugungi guruhlar
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ groups.length }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Jami darslar</p>
            </div>
          </div>
        </div>

        <!-- Attendance Taken Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <div
                class="flex items-center justify-center w-10 h-10 bg-green-500/20 dark:bg-green-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-green-600 dark:text-green-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Davomat olingan
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ groups.filter((g) => g.attendance_exists).length }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tugallangan</p>
            </div>
          </div>
        </div>

        <!-- Attendance Pending Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <div
                class="flex items-center justify-center w-10 h-10 bg-yellow-500/20 dark:bg-yellow-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-yellow-600 dark:text-yellow-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Davomat olinmagan
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ groups.filter((g) => !g.attendance_exists).length }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kutilmoqda</p>
            </div>
          </div>
        </div>

        <!-- Total Students Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <div
                class="flex items-center justify-center w-10 h-10 bg-purple-500/20 dark:bg-purple-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-purple-600 dark:text-purple-400"
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
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Jami o'quvchi
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{
                  groups.reduce((sum, group) => sum + (group.students_count || 0), 0)
                }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Barcha guruhlar</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Groups List -->
      <div
        v-if="groups.length > 0"
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-3 border-b border-gray-200/50 dark:border-gray-700/50">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
              Bugungi darslar
            </h2>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400"
              >{{ groups.length }} ta</span
            >
          </div>
        </div>
        <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
          <Link
            v-for="group in groups"
            :key="group.id"
            :href="`/inertia-teachers/groups/${group.id}/attendance`"
            class="block p-4 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30 active:bg-gray-100/50 dark:active:bg-gray-700/50 first:rounded-t-2xl last:rounded-b-2xl"
            @click="triggerHapticFeedback"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h3
                    class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                  >
                    {{ group.name }}
                  </h3>
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full',
                      group.attendance_exists
                        ? 'bg-green-100/80 dark:bg-green-900/40 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800'
                        : 'bg-yellow-100/80 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
                    ]"
                  >
                    {{ group.attendance_exists ? "✅" : "⏰" }}
                  </span>
                </div>
                <div class="flex flex-col gap-1">
                  <div
                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-3.5 h-3.5"
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
                    <span class="truncate">{{ group.students_count }} o'quvchi</span>
                  </div>
                  <div
                    v-if="group.days && group.days.length > 0"
                    class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-500"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-3.5 h-3.5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                      />
                    </svg>
                    <div class="flex gap-1">
                      <span
                        v-for="day in group.days.slice(0, 1)"
                        :key="day"
                        class="px-1.5 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded"
                      >
                        {{ day.substring(0, 3) }}
                      </span>
                      <span v-if="group.days.length > 1" class="text-xs text-gray-400">
                        +{{ group.days.length - 1 }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex flex-col items-end gap-1 ml-3 flex-shrink-0">
                <span
                  v-if="group.start_time"
                  class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:text-blue-400 bg-blue-100/80 dark:bg-blue-900/40 rounded-full border border-blue-200 dark:border-blue-800 whitespace-nowrap"
                >
                  {{ group.start_time }}
                </span>
                <span
                  class="text-xs font-medium"
                  :class="[
                    group.attendance_exists
                      ? 'text-green-600 dark:text-green-400'
                      : 'text-yellow-600 dark:text-yellow-400',
                  ]"
                >
                  {{ group.attendance_exists ? "Ko'rish" : "Davomat olish" }}
                </span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-4 h-4 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7"
                  />
                </svg>
              </div>
            </div>
          </Link>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg mt-1">
        <div class="p-10 text-center">
          <div class="flex flex-col items-center justify-center">
            <div
              class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3"
            >
              <span class="text-3xl">😴</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
              Bugun dam olish kuni!
            </h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm max-w-xs mx-auto">
              Bugun uchun rejalashtirilgan darslar yo'q. Yaxshi dam oling! 🌟
            </p>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@/Inertia/Staff/DefaultLayout.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ref, watch, onMounted } from "vue";

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  groups: {
    type: Array,
    required: true,
  },
});

// Reactive reference for flash message
const flashMessage = ref(null);

// Get Inertia page props
const page = usePage();

// Check if flash message exists on component mount
onMounted(() => {
  const successMessage = page.props.flash?.success;
  if (successMessage) {
    flashMessage.value = successMessage;
    triggerTelegramAlert(flashMessage.value);
  }
});

// Watch for changes in flash messages
watch(
  () => page.props.flash?.success,
  (newFlashMessage) => {
    if (newFlashMessage) {
      flashMessage.value = newFlashMessage;
      triggerTelegramAlert(flashMessage.value);
    }
  }
);

// Function to get the Telegram Web App instance
const getTelegramWebApp = () => {
  return window.Telegram?.WebApp;
};

// Function to trigger haptic feedback
const triggerHapticFeedback = () => {
  const webApp = getTelegramWebApp();
  if (webApp?.HapticFeedback) {
    try {
      webApp.HapticFeedback.impactOccurred("medium");
    } catch (e) {
      console.warn("HapticFeedback not supported:", e);
    }
  }
};

// Function to trigger Telegram alert
const triggerTelegramAlert = (message) => {
  const webApp = getTelegramWebApp();
  if (webApp?.showAlert) {
    try {
      webApp.showAlert(message);
    } catch (e) {
      console.warn("Alert not supported:", e);
    }
  }
};

// Get time greeting
const getTimeGreeting = () => {
  const hour = new Date().getHours();
  if (hour < 12) return "Xayrli tong";
  if (hour < 17) return "Xayrli kun";
  if (hour < 21) return "Xayrli kech";
  return "Xayrli tun";
};
</script>

<style scoped>
.animate-wave {
  animation: wave 2s infinite;
  transform-origin: 70% 70%;
}

@keyframes wave {
  0% {
    transform: rotate(0deg);
  }
  10% {
    transform: rotate(14deg);
  }
  20% {
    transform: rotate(-8deg);
  }
  30% {
    transform: rotate(14deg);
  }
  40% {
    transform: rotate(-4deg);
  }
  50% {
    transform: rotate(10deg);
  }
  60% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(0deg);
  }
}
</style>
