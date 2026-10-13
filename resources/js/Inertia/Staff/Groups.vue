<template>
  <DefaultLayout>
    <div class="space-y-4 pb-24">
      <!-- Stats Grid -->
      <div class="grid grid-cols-3 gap-3">
        <!-- Active Groups Card -->
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
                Aktiv
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ stats.active }}
              </p>
            </div>
          </div>
        </div>

        <!-- Demo Groups Card -->
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
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Demo
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ stats.demo }}
              </p>
            </div>
          </div>
        </div>

        <!-- Completed Groups Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <div
                class="flex items-center justify-center w-10 h-10 bg-gray-500/20 dark:bg-gray-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-gray-600 dark:text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Tugagan
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ stats.completed }}
              </p>
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
              Barcha guruhlar
            </h2>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400"
              >{{ stats.total }} ta</span
            >
          </div>
        </div>
        <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
          <Link
            v-for="group in groups"
            :key="group.id"
            :href="`/inertia-teachers/groups/${group.id}`"
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
                      'inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full border',
                      group.state === 'active'
                        ? 'bg-green-100/80 dark:bg-green-900/40 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800'
                        : group.state === 'demo'
                        ? 'bg-blue-100/80 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800'
                        : 'bg-gray-100/80 dark:bg-gray-700/40 text-gray-700 dark:text-gray-400 border-gray-200 dark:border-gray-600',
                    ]"
                  >
                    {{ getStateText(group.state) }}
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
                    v-if="group.course"
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
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                      />
                    </svg>
                    <span class="truncate">{{ group.course?.name }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center ml-3 flex-shrink-0">
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
      <div
        v-else
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="p-10 text-center">
          <div class="flex flex-col items-center justify-center">
            <div
              class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3"
            >
              <span class="text-3xl">📚</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
              Guruhlar topilmadi
            </h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm max-w-xs mx-auto">
              Hozircha hech qanday guruh mavjud emas
            </p>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@/Inertia/Staff/DefaultLayout.vue";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  groups: {
    type: Object,
    required: true,
  },
});

// Quick stats computed
const stats = computed(() => ({
  total: props.groups.length,
  active: props.groups.filter((g) => g.state === "active").length,
  demo: props.groups.filter((g) => g.state === "demo").length,
  completed: props.groups.filter((g) => g.state === "completed").length,
}));

const getStateText = (state) => {
  switch (state) {
    case "demo":
      return "Demo";
    case "active":
      return "Aktiv";
    case "completed":
      return "Yakunlangan";
    default:
      return "Noma'lum";
  }
};

const triggerHapticFeedback = () => {
  const webApp = window?.Telegram?.WebApp;
  if (webApp && webApp.HapticFeedback) {
    try {
      webApp.HapticFeedback.impactOccurred("medium");
    } catch (e) {
      console.warn("HapticFeedback not supported:", e);
    }
  }
};
</script>
