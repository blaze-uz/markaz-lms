<script setup>
import DefaultLayout from "@/Inertia/Staff/DefaultLayout.vue";
import { ref, computed } from "vue";
import { useDark } from "@vueuse/core";

const isDark = useDark();

// Notification settings reactive state
const attendanceReminders = ref(true);
const messageNotifications = ref(false);

// Function to trigger haptic feedback
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

// Toggle actions
const toggleDarkMode = () => {
  isDark.value = !isDark.value;
  triggerHapticFeedback();
};

const toggleAttendanceReminders = () => {
  attendanceReminders.value = !attendanceReminders.value;
  triggerHapticFeedback();
};

const toggleMessageNotifications = () => {
  messageNotifications.value = !messageNotifications.value;
  triggerHapticFeedback();
};

// Settings sections
const settings = computed(() => [
  {
    title: "Tashqi ko'rinish",
    icon: "🎨",
    items: [
      {
        label: "Qorong'i rejim",
        description: "Qorong'i va yorug' rejim o'rtasida almashtiring",
        type: "toggle",
        value: isDark.value,
        action: toggleDarkMode,
      },
    ],
  },
  {
    title: "Bildirishnomalar",
    icon: "🔔",
    items: [
      {
        label: "Davomat eslatmalari",
        description: "Dars boshlanishidan oldin eslatma olish",
        type: "toggle",
        value: attendanceReminders.value,
        action: toggleAttendanceReminders,
      },
      {
        label: "Yangi xabarlar",
        description: "Yangi xabarlar kelganda xabar berish",
        type: "toggle",
        value: messageNotifications.value,
        action: toggleMessageNotifications,
      },
    ],
  },
  {
    title: "Ilova haqida",
    icon: "ℹ️",
    items: [
      {
        label: "Versiya",
        description: "3.0",
        type: "info",
      },
      {
        label: "Ishlab chiquvchi",
        description: "Markaz LMS",
        type: "info",
      },
    ],
  },
]);
</script>

<template>
  <DefaultLayout>
    <div class="space-y-4 pb-24">
      <!-- Header Card with Gradient -->
      <div
        class="overflow-hidden relative backdrop-blur-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-600 rounded-3xl shadow-2xl"
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
              <p class="text-sm font-medium text-white/80 mb-1">Sozlamalar</p>
              <h1 class="text-2xl font-bold text-white">
                Ilova sozlamalari
              </h1>
            </div>
            <span class="inline-block text-4xl">⚙️</span>
          </div>
          <p class="text-sm text-white/90 mt-2">
            Ilovani o'zingizga moslashtiring
          </p>
        </div>
      </div>

        <!-- Settings Sections -->
      <div class="space-y-4">
          <div
            v-for="section in settings"
            :key="section.title"
          class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
          >
            <!-- Section Header -->
            <div
            class="px-5 py-4 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-gray-50/50 to-transparent dark:from-gray-700/30"
            >
              <h2
              class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2"
              >
              <span class="text-xl">{{ section.icon }}</span>
                {{ section.title }}
              </h2>
            </div>

            <!-- Section Items -->
          <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
            <div
              v-for="item in section.items"
              :key="item.label"
              class="p-4 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30"
            >
                <!-- Toggle Item -->
                <div
                  v-if="item.type === 'toggle'"
                  class="flex items-center justify-between"
                >
                <div class="flex-1 min-w-0 pr-4">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                      {{ item.label }}
                    </p>
                  <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                      {{ item.description }}
                    </p>
                  </div>

                <!-- Modern Toggle Switch -->
                  <button
                    @click="item.action"
                  type="button"
                  role="switch"
                  :aria-checked="item.value"
                  class="relative inline-flex h-8 w-14 flex-shrink-0 cursor-pointer rounded-full transition-all duration-200 ease-in-out focus:outline-none"
                  :class="
                    item.value
                      ? 'bg-blue-500'
                      : 'bg-gray-400 dark:bg-gray-600'
                  "
                  >
                    <span
                    class="pointer-events-none inline-block h-6 w-6 mt-1 rounded-full bg-white shadow-lg transition-all duration-200 ease-in-out"
                    :class="item.value ? 'translate-x-7' : 'translate-x-1'
                    "
                    ></span>
                  </button>
                </div>

                <!-- Info Item -->
                <div
                  v-else-if="item.type === 'info'"
                  class="flex items-center justify-between"
                >
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                      {{ item.label }}
                    </p>
                  </div>
                <div class="ml-4">
                  <span
                    class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300"
                  >
                      {{ item.description }}
                  </span>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-3">
          <!-- App Version Info -->
        <div
          class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg p-4"
        >
            <div class="text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700/50 rounded-lg mb-2">
              <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                Markaz LMS Staff
              </span>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300"
              >
                v3.0
              </span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              Markaz LMS Staff v3.0
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              © {{ new Date().getFullYear() }} Markaz LMS. Barcha huquqlar himoyalangan.
            </p>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

