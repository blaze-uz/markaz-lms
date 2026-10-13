<template>
  <DefaultLayout>
    <div class="space-y-4 pb-24">
      <!-- Group Info Card -->
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
              <p class="text-sm font-medium text-white/80 mb-1">Guruh</p>
              <h1 class="text-2xl font-bold text-white">
                {{ group.name }}
              </h1>
            </div>
            <span class="inline-block text-4xl">📚</span>
          </div>
          <div class="mt-4 pt-4 border-t border-white/20">
            <div class="flex items-center gap-4 text-white/90">
              <div class="flex items-center gap-2">
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
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
                <span class="text-sm font-medium">{{ stats.total }} o'quvchi</span>
              </div>
              <div v-if="group.course" class="flex items-center gap-2">
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
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                  />
                </svg>
                <span class="text-sm font-medium">{{ group.course?.name }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-3 gap-3">
        <!-- Active Students Card -->
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
                Faol
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ stats.active }}
              </p>
            </div>
          </div>
        </div>

        <!-- Debtors Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <div
                class="flex items-center justify-center w-10 h-10 bg-red-500/20 dark:bg-red-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-red-600 dark:text-red-400"
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
                Qarzdor
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ stats.debtors }}
              </p>
            </div>
          </div>
        </div>

        <!-- Total Debt Card -->
        <div
          class="overflow-hidden relative backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg transition-all duration-300 hover:scale-[1.02]"
        >
          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <div
                class="flex items-center justify-center w-10 h-10 bg-orange-500/20 dark:bg-orange-500/30 rounded-xl"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-orange-600 dark:text-orange-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Umumiy qarz
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ formatBalanceShort(stats.totalDebt) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Students List -->
      <div
        v-if="students.length > 0"
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-3 border-b border-gray-200/50 dark:border-gray-700/50">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
              O'quvchilar
            </h2>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400"
              >{{ stats.total }} ta</span
            >
          </div>
        </div>
        <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
          <div
            v-for="student in students"
            :key="student.id"
            @click="triggerHapticFeedback"
            class="block p-4 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30 active:bg-gray-100/50 dark:active:bg-gray-700/50 first:rounded-t-2xl last:rounded-b-2xl cursor-pointer"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1 min-w-0">
                <div
                  class="w-10 h-10 bg-blue-500/20 dark:bg-blue-500/30 rounded-xl flex items-center justify-center flex-shrink-0"
                >
                  <span
                    class="text-sm font-bold text-blue-600 dark:text-blue-400"
                  >
                    {{ student.name.charAt(0) }}{{ student.surname.charAt(0) }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <h3
                    class="text-sm font-semibold text-gray-900 dark:text-white truncate mb-0.5"
                  >
                    {{ student.name }} {{ student.surname }}
                  </h3>
                  <div
                    class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400"
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
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                      />
                    </svg>
                    <span class="truncate">{{ formatPhone(student.phone) }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2 ml-3 flex-shrink-0">
                <div class="text-right">
                  <span
                    :class="[
                      'text-xs font-semibold',
                      getBalanceStatus(student.balance).color,
                    ]"
                  >
                    {{ getBalanceStatus(student.balance).sign
                    }}{{ formatBalance(parseBalance(student.balance)) }}
                  </span>
                  <div
                    :class="[
                      'w-2 h-2 rounded-full mt-1 mx-auto',
                      parseBalance(student.balance) >= 0
                        ? 'bg-green-500'
                        : 'bg-red-500',
                    ]"
                  ></div>
                </div>
              </div>
            </div>
          </div>
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
              <span class="text-3xl">👥</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
              O'quvchilar topilmadi
            </h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm max-w-xs mx-auto">
              Bu guruhda hozircha o'quvchilar mavjud emas
            </p>
          </div>
        </div>
      </div>

      <!-- Attendance Section -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-3 border-b border-gray-200/50 dark:border-gray-700/50">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
              Davomat
            </h2>
          </div>
        </div>
        <div class="p-4">
          <!-- Date Filter -->
          <div class="mb-4" v-if="attendanceDates.length > 0">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
              Sana tanlang
            </label>
            <select
              v-model="selectedDate"
              @change="loadAttendance"
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500"
            >
              <option
                v-for="dateOption in attendanceDates"
                :key="dateOption.value"
                :value="dateOption.value"
              >
                {{ dateOption.label }}
              </option>
            </select>
          </div>
          <div v-else class="mb-4 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl text-center">
            <p class="text-xs text-gray-600 dark:text-gray-400">
              Hozircha davomat mavjud emas
            </p>
          </div>

          <!-- Attendance List -->
          <div v-if="attendances.length > 0" class="space-y-2">
            <div
              v-for="attendance in attendances"
              :key="attendance.id"
              class="p-3 bg-gray-50/50 dark:bg-gray-700/30 rounded-xl border border-gray-200/50 dark:border-gray-600/50"
            >
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ attendance.student_name }}
                </h3>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-lg',
                    attendance.status === 'present'
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                      : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                  ]"
                >
                  {{ attendance.status === 'present' ? 'Kelgan' : 'Kelmagan' }}
                </span>
              </div>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div v-if="attendance.score" class="flex items-center gap-1">
                  <span class="text-gray-600 dark:text-gray-400">Baho:</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{
                    attendance.score
                  }}</span>
                </div>
                <div
                  v-if="attendance.homework"
                  class="flex items-center gap-1"
                >
                  <span class="text-gray-600 dark:text-gray-400">Uy vazifasi:</span>
                  <span class="font-medium text-green-600 dark:text-green-400"
                    >Bajarildi</span
                  >
                </div>
                <div
                  v-if="attendance.comment"
                  class="col-span-2 flex items-start gap-1 mt-1"
                >
                  <span class="text-gray-600 dark:text-gray-400">Izoh:</span>
                  <span class="font-medium text-gray-900 dark:text-white flex-1">{{
                    attendance.comment
                  }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty Attendance State -->
          <div
            v-else
            class="p-8 text-center"
          >
            <div class="flex flex-col items-center justify-center">
              <div
                class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-2"
              >
                <span class="text-2xl">📅</span>
              </div>
              <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                Davomat topilmadi
              </h3>
              <p class="text-gray-600 dark:text-gray-400 text-xs">
                Tanlangan sana uchun davomat mavjud emas
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@/Inertia/Staff/DefaultLayout.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  group: {
    type: Object,
    required: true,
  },
  students: {
    type: Object,
    required: true,
  },
  attendances: {
    type: Array,
    default: () => [],
  },
  attendanceDates: {
    type: Array,
    default: () => [],
  },
  selectedDate: {
    type: String,
    default: () => new Date().toISOString().split('T')[0],
  },
});

const selectedDate = ref(props.selectedDate);

const loadAttendance = () => {
  router.get(
    `/inertia-teachers/groups/${props.group.id}`,
    { date: selectedDate.value },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Computed stats
const stats = computed(() => ({
  total: props.students.length,
  debtors: props.students.filter((s) => parseBalance(s.balance) < 0).length,
  active: props.students.filter((s) => parseBalance(s.balance) >= 0).length,
  totalDebt: Math.abs(
    props.students
      .filter((s) => parseBalance(s.balance) < 0)
      .reduce((sum, s) => sum + parseBalance(s.balance), 0)
  ),
}));

// Format phone number
const formatPhone = (phone) => {
  if (!phone) return "";
  const cleaned = phone.replace(/\D/g, "");
  const match = cleaned.match(/^(\d{2})(\d{3})(\d{2})(\d{2})$/);
  if (match) {
    return `+998 (${match[1]}) ${match[2]}-${match[3]}-${match[4]}`;
  }
  return phone;
};

// Parse balance - handle different formats
const parseBalance = (balance) => {
  if (!balance && balance !== 0) return 0;

  // If it's already a number, return it
  if (typeof balance === "number") return balance;

  // Convert to string and clean it
  let cleanedBalance = balance.toString();

  // Remove currency symbols and text
  cleanedBalance = cleanedBalance.replace(/[A-Za-z\s]/g, "");

  // Handle negative sign
  const isNegative = cleanedBalance.includes("-");

  // Remove all non-digit characters except decimal point
  cleanedBalance = cleanedBalance.replace(/[^\d.]/g, "");

  // Convert to number
  const parsed = parseFloat(cleanedBalance) || 0;

  return isNegative ? -parsed : parsed;
};

// Format balance for display with space separators and currency
const formatBalance = (balance) => {
  const parsed = parseBalance(balance);
  const absValue = Math.abs(parsed);

  // Format with space as thousand separator
  const formatted = new Intl.NumberFormat("uz-UZ", {
    style: "decimal",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
    useGrouping: true,
  }).format(absValue);

  return formatted.replace(/,/g, " ") + " uzs";
};

// Format balance for stats (shorter version)
const formatBalanceShort = (balance) => {
  const parsed = parseBalance(balance);
  const absValue = Math.abs(parsed);

  if (absValue >= 1000000) {
    return Math.round(absValue / 1000000) + "M";
  } else if (absValue >= 1000) {
    return Math.round(absValue / 1000) + "k";
  } else {
    return Math.round(absValue).toString();
  }
};

// Get balance status with formatted value
const getBalanceStatus = (balance) => {
  const parsed = parseBalance(balance);
  if (parsed > 0)
    return {
      color: "text-green-600 dark:text-green-400",
      sign: "+",
      formatted: formatBalance(balance),
    };
  if (parsed < 0)
    return {
      color: "text-red-600 dark:text-red-400",
      sign: "-",
      formatted: formatBalance(balance),
    };
  return {
    color: "text-gray-600 dark:text-gray-400",
    sign: "",
    formatted: formatBalance(balance),
  };
};

// Trigger haptic feedback
const triggerHapticFeedback = () => {
  const webApp = window?.Telegram?.WebApp;
  if (webApp && webApp.HapticFeedback) {
    try {
      webApp.HapticFeedback.impactOccurred("light");
    } catch (e) {
      console.warn("HapticFeedback not supported:", e);
    }
  }
};
</script>
