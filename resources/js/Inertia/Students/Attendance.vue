<template>
  <StudentLayout :settings="settings">
    <div class="space-y-4 pb-20 no-tap-highlight">
      <!-- Header with Summary -->
      <div
        class="overflow-hidden relative backdrop-blur-xl bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 rounded-3xl shadow-2xl"
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
              <h1 class="text-2xl font-bold text-white mb-1">Davomat</h1>
              <p class="text-sm font-medium text-white/80">
                {{ selectedGroupName || groups[0]?.name || "Guruh" }}
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
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
              </svg>
            </div>
          </div>
          <div v-if="overallStats" class="mt-4 pt-4 border-t border-white/20">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-medium text-white/70 mb-1">Umumiy davomat</p>
                <p class="text-2xl font-bold text-white">
                  {{ overallStats.percentage }}%
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-white/70 mb-1">Jami darslar</p>
                <p class="text-2xl font-bold text-white">{{ overallStats.total }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Group Selection -->
      <div
        v-if="groups.length > 1"
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="p-4">
          <label
            for="group"
            class="block mb-3 text-sm font-semibold text-gray-900 dark:text-white"
          >
            Guruhni tanlang
          </label>
          <div class="relative">
            <select
              id="group"
              v-model="selectedGroup"
              @change="updateGroup"
              class="block w-full appearance-none rounded-xl border border-gray-200 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 backdrop-blur-sm px-4 py-3 text-sm font-medium text-gray-900 dark:text-white shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
            >
              <option
                v-for="group in groups"
                :key="group.id"
                :value="group.id"
                :selected="group.id === selectedGroup"
              >
                {{ group.name }}
              </option>
            </select>
            <div
              class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Period Selection -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="p-4">
          <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            Davrni tanlang
          </p>
          <div
            class="flex gap-2 p-1 bg-gray-100/50 dark:bg-gray-700/50 rounded-xl backdrop-blur-sm"
          >
            <button
              v-for="period in periods"
              :key="period.value"
              @click="updatePeriod(period.value)"
              class="flex-1 px-4 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 ease-in-out"
              :class="[
                selectedPeriod === period.value
                  ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-md scale-[1.02]'
                  : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white',
              ]"
            >
              {{ period.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Chart -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 pt-5 pb-3">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">
            Davomat grafigi
          </h2>
        </div>
        <div class="p-4 pt-2">
          <div class="h-[280px]">
            <canvas ref="chartCanvas"></canvas>
          </div>
        </div>
      </div>

      <!-- Monthly Stats -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">
            Batafsil statistika
          </h2>
        </div>
        <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
          <template v-if="monthlyStatsReversed.length">
            <div
              v-for="stat in monthlyStatsReversed"
              :key="stat.month"
              class="px-5 py-4 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30"
            >
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                  stat.month
                }}</span>
                <div class="flex items-center gap-2">
                  <!-- Status badge for daily view -->
                  <span
                    v-if="stat.status"
                    class="px-2 py-0.5 text-xs font-medium rounded-full"
                    :class="stat.status === 'present' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'"
                  >
                    {{ stat.status === 'present' ? 'Kelgan' : 'Kelmagan' }}
                  </span>
                  <!-- Score badge -->
                  <span
                    v-if="stat.score"
                    class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                  >
                    Baho: {{ stat.score }}
                  </span>
                  <span v-else class="text-sm font-bold" :class="getTextColor(stat.percentage)">
                    {{ stat.percentage }}%
                  </span>
                </div>
              </div>
              <div
                class="w-full h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden"
              >
                <div
                  class="h-full rounded-full transition-all duration-500 ease-out"
                  :class="getPercentageColor(stat.percentage)"
                  :style="{ width: `${stat.percentage}%` }"
                ></div>
              </div>
              <div
                class="flex justify-between items-center mt-2 text-xs text-gray-500 dark:text-gray-400"
              >
                <span>Borgan: {{ stat.present || 0 }}</span>
                <div class="flex items-center gap-3">
                  <!-- Homework indicator (only for daily view when homework exists) -->
                  <span v-if="stat.homework !== null && stat.homework !== undefined" class="flex items-center gap-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-3.5 h-3.5"
                      :class="stat.homework ? 'text-emerald-500' : 'text-rose-400'"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        v-if="stat.homework"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                      <path
                        v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                    <span :class="stat.homework ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400'">
                      {{ stat.homework ? 'Bajarildi' : 'Bajarilmadi' }}
                    </span>
                  </span>
                  <span>Jami: {{ stat.total || 0 }}</span>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="p-12 text-center">
              <div class="flex flex-col items-center justify-center">
                <div
                  class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4"
                >
                  <svg
                    class="w-8 h-8 text-gray-400"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                  Ma'lumot yo'q
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Bu davr uchun davomat ma'lumotlari mavjud emas
                </p>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>

<style>
.no-tap-highlight {
  -webkit-tap-highlight-color: transparent;
}

/* Prevent text selection on buttons */
button {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -webkit-touch-callout: none;
}
</style>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import StudentLayout from "../Layouts/StudentLayout.vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

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
  selectedGroupId: {
    type: Number,
    required: true,
  },
  attendanceData: {
    type: Object,
    required: true,
  },
});

const selectedGroup = ref(props.selectedGroupId);
const chartCanvas = ref(null);
let chart = null;

const periods = [
  { label: "6 oy", value: "6_months" },
  { label: "Joriy oy", value: "this_month" },
  { label: "O'tgan oy", value: "last_month" },
];
const selectedPeriod = ref("6_months");

const getPercentageColor = (percentage) => {
  if (percentage >= 70) return "bg-emerald-500";
  if (percentage >= 40) return "bg-amber-500";
  return "bg-rose-500";
};

const getTextColor = (percentage) => {
  if (percentage >= 70) return "text-emerald-600";
  if (percentage >= 40) return "text-amber-600";
  return "text-rose-600";
};

const formatMonth = (date) => {
  if (selectedPeriod.value === "this_month" || selectedPeriod.value === "last_month") {
    return date; // Already in dd.mm.yyyy format from backend
  }
  return date; // Already in MMM YYYY format from backend
};

const chartData = computed(() => {
  const labels = Object.keys(props.attendanceData).map((date) => {
    if (selectedPeriod.value === "this_month" || selectedPeriod.value === "last_month") {
      const [day] = date.split(".");
      return day;
    }
    return date;
  });

  const data = Object.values(props.attendanceData).map((item) => item.percentage);

  if (selectedPeriod.value === "6_months") {
    return {
      labels,
      datasets: [
        {
          data,
          backgroundColor: data.map((value) =>
            value >= 70 ? "#10b981" : value >= 40 ? "#f59e0b" : "#f43f5e"
          ),
          borderRadius: 6,
        },
      ],
    };
  } else {
    return {
      labels,
      datasets: [
        {
          data,
          borderColor: "#3b82f6",
          backgroundColor: "rgba(59, 130, 246, 0.1)",
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointBackgroundColor: "#3b82f6",
          pointBorderColor: "#fff",
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
        },
      ],
    };
  }
});

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      backgroundColor: "#1f2937",
      titleColor: "#fff",
      bodyColor: "#fff",
      padding: 12,
      displayColors: false,
      callbacks: {
        title: (context) => {
          const label = context[0].label;
          if (
            selectedPeriod.value === "this_month" ||
            selectedPeriod.value === "last_month"
          ) {
            const date = Object.keys(props.attendanceData)[context[0].dataIndex];
            return date;
          }
          return label;
        },
        label: (context) => `Davomat: ${context.raw}%`,
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 100,
      grid: {
        display: false,
      },
      ticks: {
        callback: (value) => `${value}%`,
        font: {
          size: 11,
        },
      },
    },
    x: {
      grid: {
        display: false,
      },
      ticks: {
        font: {
          size: 11,
        },
        maxRotation: selectedPeriod.value === "6_months" ? 45 : 0,
      },
    },
  },
}));

const monthlyStatsReversed = computed(() => {
  const dates = Object.keys(props.attendanceData);
  return dates.map((date) => ({
    month: date,
    percentage: props.attendanceData[date].percentage,
    present: props.attendanceData[date].present || 0,
    total: props.attendanceData[date].total || 0,
    homework: props.attendanceData[date].homework,
    score: props.attendanceData[date].score || null,
    status: props.attendanceData[date].status || null,
  }));
});

const selectedGroupName = computed(() => {
  const group = props.groups.find((g) => g.id === selectedGroup.value);
  return group?.name || null;
});

const overallStats = computed(() => {
  if (!monthlyStatsReversed.value.length) {
    return null;
  }
  const totals = monthlyStatsReversed.value.reduce(
    (acc, stat) => {
      acc.present += stat.present || 0;
      acc.total += stat.total || 0;
      return acc;
    },
    { present: 0, total: 0 }
  );
  const percentage =
    totals.total > 0 ? Math.round((totals.present / totals.total) * 100) : 0;
  return {
    percentage,
    present: totals.present,
    total: totals.total,
  };
});

const updateGroup = () => {
  // Trigger haptic feedback
  if (window.Telegram?.WebApp?.HapticFeedback) {
    window.Telegram.WebApp.HapticFeedback.impactOccurred("light");
  }

  router.get(
    `/twa/attendance`,
    {
      group_id: selectedGroup.value,
      period: selectedPeriod.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        if (chart) {
          chart.destroy();
          const ctx = chartCanvas.value.getContext("2d");
          chart = new Chart(ctx, {
            type: selectedPeriod.value === "6_months" ? "bar" : "line",
            data: chartData.value,
            options: chartOptions.value,
          });
        }
      },
    }
  );
};

const updatePeriod = (period) => {
  // Trigger haptic feedback
  if (window.Telegram?.WebApp?.HapticFeedback) {
    window.Telegram.WebApp.HapticFeedback.impactOccurred("light");
  }

  selectedPeriod.value = period;
  router.get(
    `/twa/attendance`,
    {
      group_id: selectedGroup.value,
      period: period,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        if (chart) {
          chart.destroy();
          const ctx = chartCanvas.value.getContext("2d");
          chart = new Chart(ctx, {
            type: period === "6_months" ? "bar" : "line",
            data: chartData.value,
            options: chartOptions.value,
          });
        }
      },
    }
  );
};

onMounted(() => {
  const ctx = chartCanvas.value.getContext("2d");
  chart = new Chart(ctx, {
    type: selectedPeriod.value === "6_months" ? "bar" : "line",
    data: chartData.value,
    options: chartOptions.value,
  });
});

onBeforeUnmount(() => {
  if (chart) {
    chart.destroy();
  }
});
</script>
