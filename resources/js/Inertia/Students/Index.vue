<template>
  <StudentLayout :settings="settings">
    <div class="space-y-4 pb-20">
      <!-- Welcome Section with Student Name -->
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
        <div class="relative px-6 py-8">
          <div class="flex items-center justify-between mb-2">
            <div>
              <p class="text-sm font-medium text-white/80 mb-1">Assalomu alaykum</p>
              <h1 class="text-2xl font-bold text-white">
                {{ student.name }} {{ student.surname }}
              </h1>
            </div>
            <span class="inline-block text-4xl animate-wave">👋</span>
          </div>
          <div class="mt-4 pt-4 border-t border-white/20">
            <div class="flex items-center gap-2 text-white/90">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              <span class="text-sm font-medium">{{ student.phone }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Stats Grid -->
      <div class="grid grid-cols-2 gap-3">
        <!-- Today's Lesson Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-4">
            <div class="flex items-center justify-between mb-3">
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
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Bugungi dars
              </p>
              <div v-if="todays_group_time" class="space-y-1">
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                  {{ todays_group_time.start }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ todays_group_time.group?.name }}
                </p>
              </div>
              <div v-else>
                <p class="text-base font-semibold text-gray-400 dark:text-gray-500">-</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Dars yo'q</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-4">
            <div class="flex items-center justify-between mb-3">
              <div
                :class="[
                  'flex items-center justify-center w-10 h-10 rounded-xl',
                  {
                    'bg-red-500/20 dark:bg-red-500/30': attendance_percentage < 60,
                    'bg-yellow-500/20 dark:bg-yellow-500/30':
                      attendance_percentage >= 60 && attendance_percentage < 80,
                    'bg-green-500/20 dark:bg-green-500/30': attendance_percentage >= 80,
                  },
                ]"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  :class="[
                    'w-5 h-5',
                    {
                      'text-red-600 dark:text-red-400': attendance_percentage < 60,
                      'text-yellow-600 dark:text-yellow-400':
                        attendance_percentage >= 60 && attendance_percentage < 80,
                      'text-green-600 dark:text-green-400': attendance_percentage >= 80,
                    },
                  ]"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Davomat
              </p>
              <p
                :class="[
                  'text-lg font-bold',
                  {
                    'text-red-600 dark:text-red-400': attendance_percentage < 60,
                    'text-yellow-600 dark:text-yellow-400':
                      attendance_percentage >= 60 && attendance_percentage < 80,
                    'text-green-600 dark:text-green-400': attendance_percentage >= 80,
                  },
                ]"
              >
                {{ attendance_percentage }}%
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                <span v-if="attendance_percentage >= 80">A'lo</span>
                <span v-else-if="attendance_percentage >= 60">Yaxshi</span>
                <span v-else>Yomon</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Groups Count Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-4">
            <div class="flex items-center justify-between mb-3">
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
                Guruhlar
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ groups.length }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Faol guruhlar</p>
            </div>
          </div>
        </div>

        <!-- Balance Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-4">
            <div class="flex items-center justify-between mb-3">
              <div
                class="flex items-center justify-center w-10 h-10 bg-emerald-500/20 dark:bg-emerald-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Balans
              </p>
              <p
                :class="[
                  'text-lg font-bold',
                  student.balance >= 0
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-red-600 dark:text-red-400',
                ]"
              >
                {{ formatBalance(student.balance) }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                <span v-if="student.balance >= 0">To'landi</span>
                <span v-else>Qarz</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Groups List -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
              Guruhlarim
            </h2>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400"
              >{{ groups.length }} ta</span
            >
          </div>
        </div>
        <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
          <div v-if="groups.length === 0" class="p-8 text-center">
            <div class="flex flex-col items-center justify-center">
              <div
                class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-8 h-8 text-gray-400"
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
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Hozircha guruhlar yo'q
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                Siz hali hech qanday guruhga qo'shilmagansiz
              </p>
            </div>
          </div>
          <Link
            v-for="group in groups"
            :key="group.id"
            :href="`/twa/attendance?group_id=${group.id}`"
            class="block p-4 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30 active:bg-gray-100/50 dark:active:bg-gray-700/50"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h3
                    class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                  >
                    {{ group.name }}
                  </h3>
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
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                      />
                    </svg>
                    <span class="truncate">{{
                      group.teacher?.name || "O'qituvchi yo'q"
                    }}</span>
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
              <div class="flex flex-col items-end gap-2 ml-3">
                <span
                  v-if="group.start_time && group.end_time"
                  class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 dark:text-green-400 bg-green-100/80 dark:bg-green-900/40 rounded-full border border-green-200 dark:border-green-800"
                >
                  {{ group.start_time }} - {{ group.end_time }}
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100/80 dark:bg-gray-700/50 rounded-full"
                >
                  Bugun dars yo'q
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
    </div>
  </StudentLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import StudentLayout from "../Layouts/StudentLayout.vue";

const props = defineProps({
  settings: {
    type: Object,
    required: true,
  },
  student: {
    type: Object,
    required: true,
  },
  groups: {
    type: Array,
    required: true,
  },
  todays_group_time: {
    type: Object,
    required: false,
  },
  attendance_percentage: {
    type: Number,
    required: true,
  },
});

// Format balance
const formatBalance = (balance) => {
  if (balance === null || balance === undefined) {
    return "0 so'm";
  }
  const formatted = Math.abs(balance).toLocaleString("uz-UZ");
  return `${formatted} so'm`;
};
</script>

<style scoped>
.text-blue-500 {
  color: #1a56db;
}

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
