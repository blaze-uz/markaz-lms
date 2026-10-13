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
              <p class="text-sm font-medium text-white/80 mb-1">Davomat tahrirlash</p>
              <h1 class="text-2xl font-bold text-white">
                {{ props.group.name }}
              </h1>
            </div>
            <span class="inline-block text-4xl">✏️</span>
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
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
                <span class="text-sm font-medium">{{ today }}</span>
              </div>
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
                <span class="text-sm font-medium">{{ props.students.length }} o'quvchi</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Validation Errors Alert - Below Group Card -->
      <div
        v-if="Object.keys(form.errors).length > 0"
        class="overflow-hidden backdrop-blur-xl bg-red-50/90 dark:bg-red-900/30 rounded-2xl border-2 border-red-300 dark:border-red-700 shadow-lg p-4 animate-slide-down"
      >
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0 mt-0.5">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-6 h-6 text-red-600 dark:text-red-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="text-sm font-bold text-red-800 dark:text-red-200 mb-1">
              ⚠️ Xatoliklar bor
            </h3>
            <p class="text-xs text-red-700 dark:text-red-300 mb-2">
              Iltimos, quyidagi maydonlarni to'ldiring:
            </p>
            <ul class="text-xs text-red-600 dark:text-red-400 space-y-1 max-h-32 overflow-y-auto">
              <li v-for="(error, key) in form.errors" :key="key" class="flex items-start gap-1">
                <span class="text-red-500 flex-shrink-0">•</span>
                <span class="break-words">{{ error }}</span>
              </li>
            </ul>
          </div>
          <button
            @click="form.clearErrors()"
            class="flex-shrink-0 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 transition-colors"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-3 gap-3">
        <!-- Total Students Card -->
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
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Jami
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ props.students.length }}
              </p>
            </div>
          </div>
        </div>

        <!-- Present Count Card -->
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
                Kelgan
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ presentCount }}
              </p>
            </div>
          </div>
        </div>

        <!-- Absent Count Card -->
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
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                Kelmagan
              </p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ absentCount }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="flex space-x-2">
        <button
          @click="markAllPresent"
          :disabled="isSubmitting"
          :class="[
            'flex-1 px-3 py-2.5 text-sm font-medium text-white rounded-xl transition-colors shadow-lg',
            isSubmitting
              ? 'bg-gray-400 cursor-not-allowed'
              : 'bg-green-600 hover:bg-green-700 active:bg-green-800',
          ]"
        >
          ✓ Hammasi +
        </button>
        <button
          @click="markAllAbsent"
          :disabled="isSubmitting"
          :class="[
            'flex-1 px-3 py-2.5 text-sm font-medium text-white rounded-xl transition-colors shadow-lg',
            isSubmitting
              ? 'bg-gray-400 cursor-not-allowed'
              : 'bg-red-600 hover:bg-red-700 active:bg-red-800',
          ]"
        >
          ✗ Hammasi -
        </button>
      </div>

      <!-- Students List -->
      <div class="space-y-3">
        <div
          v-for="student in props.students"
          :key="student.id"
          class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
        >
          <!-- Student Header -->
          <div class="p-4 border-b border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1 min-w-0">
                <div
                  :class="[
                    'flex items-center justify-center w-10 h-10 text-sm font-bold rounded-xl',
                    form.status[student.id]
                      ? 'bg-green-500/20 dark:bg-green-500/30 text-green-700 dark:text-green-400'
                      : 'bg-gray-500/20 dark:bg-gray-500/30 text-gray-700 dark:text-gray-400',
                  ]"
                >
                  {{ getInitials(student.name, student.surname) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                  >
                    {{ student.name }} {{ student.surname }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    ID: {{ student.id }}
                  </p>
                </div>
              </div>

              <!-- Toggle Button -->
              <button
                @click="toggleAttendance(student.id)"
                :disabled="isSubmitting"
                :class="[
                  'px-4 py-2 text-xs font-medium rounded-xl transition-colors',
                  isSubmitting
                    ? 'bg-gray-200 text-gray-500 cursor-not-allowed dark:bg-gray-600 dark:text-gray-400'
                    : form.status[student.id]
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800'
                    : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-800',
                ]"
              >
                {{ form.status[student.id] ? "Kelgan" : "Kelmagan" }}
              </button>
            </div>
          </div>

          <!-- Input Row -->
          <div class="p-4 bg-gray-50/50 dark:bg-gray-700/30">
            <div :class="['grid gap-3', showHomework ? 'grid-cols-3' : 'grid-cols-2']">
              <div>
                <label
                  class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5"
                >
                  Baho
                </label>
                <input
                  type="tel"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  min="0"
                  max="100"
                  placeholder="5"
                  v-model="form.score[student.id]"
                  :disabled="isSubmitting"
                  :class="[
                    'w-full px-3 py-2 text-sm border rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 transition-colors',
                    form.errors[`score.${student.id}`]
                      ? 'border-red-500 focus:ring-red-500/50 focus:border-red-500'
                      : isSubmitting
                      ? 'border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                      : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-blue-500/50 focus:border-blue-500',
                  ]"
                />
                <p
                  v-if="form.errors[`score.${student.id}`]"
                  class="mt-1 text-xs text-red-600 dark:text-red-400"
                >
                  {{ form.errors[`score.${student.id}`] }}
                </p>
              </div>
              <div>
                <label
                  class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5"
                >
                  Izoh
                </label>
                <input
                  type="text"
                  placeholder="Izoh..."
                  v-model="form.comment[student.id]"
                  :disabled="isSubmitting"
                  :class="[
                    'w-full px-3 py-2 text-sm border rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 transition-colors',
                    form.errors[`comment.${student.id}`]
                      ? 'border-red-500 focus:ring-red-500/50 focus:border-red-500'
                      : isSubmitting
                      ? 'border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                      : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-blue-500/50 focus:border-blue-500',
                  ]"
                />
                <p
                  v-if="form.errors[`comment.${student.id}`]"
                  class="mt-1 text-xs text-red-600 dark:text-red-400"
                >
                  {{ form.errors[`comment.${student.id}`] }}
                </p>
              </div>
              <div v-if="showHomework">
                <label
                  class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5"
                >
                  Uy vazifasi
                </label>
                <button
                  type="button"
                  @click="form.homework[student.id] = !form.homework[student.id]"
                  :disabled="isSubmitting"
                  :class="[
                    'w-full px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 flex items-center justify-center gap-2',
                    form.errors[`homework.${student.id}`]
                      ? 'border-2 border-red-500'
                      : '',
                    isSubmitting
                      ? 'cursor-not-allowed opacity-50'
                      : 'cursor-pointer',
                    form.homework[student.id]
                      ? 'bg-green-500 text-white hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                  ]"
                >
                  <svg
                    v-if="form.homework[student.id]"
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
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  <span>{{ form.homework[student.id] ? "Bajarildi" : "Bajarilmadi" }}</span>
                </button>
                <p
                  v-if="form.errors[`homework.${student.id}`]"
                  class="mt-1 text-xs text-red-600 dark:text-red-400"
                >
                  {{ form.errors[`homework.${student.id}`] }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Fixed Bottom Button -->
      <div
        class="fixed bottom-20 left-0 right-0 z-30 px-4 pb-safe"
      >
        <div
          class="backdrop-blur-xl bg-white/80 dark:bg-gray-900/80 border border-white/30 dark:border-gray-700/30 rounded-2xl shadow-2xl p-3"
        >
          <button
            type="button"
            @click="submitForm"
            :disabled="isSubmitting"
            :class="[
              'w-full py-3 text-white font-semibold rounded-xl transition-colors shadow-lg flex items-center justify-center space-x-2',
              isSubmitting
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800',
            ]"
          >
            <div
              v-if="isSubmitting"
              class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"
            ></div>
            <span>{{ isSubmitting ? "Yuklanmoqda..." : "💾 Davomatni saqlash" }}</span>
          </button>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@/Inertia/Staff/DefaultLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
  group: {
    type: Object,
    required: true,
  },
  students: {
    type: Array,
    required: true,
  },
  attendance: {
    type: Object,
    required: true,
  },
  validationSettings: {
    type: Object,
    required: false,
    default: () => ({}),
  },
});

// Check if homework should be shown
const showHomework = computed(() => {
  return props.validationSettings?.homework?.required ?? false;
});

// Add loading state
const isSubmitting = ref(false);

// Get today's date in dd.mm.yyyy format
const date = new Date();
const today = [
  String(date.getDate()).padStart(2, "0"),
  String(date.getMonth() + 1).padStart(2, "0"),
  date.getFullYear(),
].join(".");

// Initialize form with existing attendance data
const form = useForm({
  status: {},
  comment: {},
  score: {},
  homework: {},
  date: today,
});

// Initialize form data with existing attendance
props.students.forEach((student) => {
  const attendance = props.attendance[student.id];
  form.status[student.id] = attendance ? attendance.status === "present" : false;
  form.comment[student.id] = attendance ? attendance.comment : "";
  form.score[student.id] = attendance ? attendance.score : "";
  form.homework[student.id] = attendance ? (attendance.homework === true || attendance.homework === '1' || attendance.homework === 1) : false;
});

// Computed properties for stats
const presentCount = computed(() => {
  return Object.values(form.status).filter((status) => status).length;
});

const absentCount = computed(() => {
  return props.students.length - presentCount.value;
});

const attendancePercentage = computed(() => {
  if (props.students.length === 0) return 0;
  return Math.round((presentCount.value / props.students.length) * 100);
});

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

const submitForm = () => {
  if (isSubmitting.value) return; // Prevent double submission

  triggerHapticFeedback();
  isSubmitting.value = true;

  form.put(
    `/inertia-teachers/groups/${props.group.id}/attendance`,
    {
      preserveState: false,
      preserveScroll: false,
      transform: (data) => {
        const transformedData = {
          status: {},
          comment: {},
          score: {},
          homework: {},
          date: data.date,
        };

        props.students.forEach((student) => {
          transformedData.status[student.id] = form.status[student.id];
          transformedData.comment[student.id] = form.comment[student.id] || null;
          transformedData.score[student.id] = form.score[student.id] || null;
          transformedData.homework[student.id] = form.homework[student.id] || false;
        });

        return transformedData;
      },
      onStart: () => {
        isSubmitting.value = true;
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
      onSuccess: () => {
        isSubmitting.value = false;
      },
      onError: (errors) => {
        console.error("Form submission errors:", errors);
        isSubmitting.value = false;
        triggerHapticFeedback();
        // Inertia automatically sets form.errors, so we just need to show alert
        // Show Telegram alert for validation errors
        const webApp = window?.Telegram?.WebApp;
        if (webApp?.showAlert) {
          try {
            const errorObj = errors || form.errors || {};
            const errorKeys = Object.keys(errorObj);
            const errorCount = errorKeys.length;
            
            if (errorCount > 0) {
              // Get field names from error keys
              const fieldNames = errorKeys.map((key) => {
                // Extract field name (e.g., "homework.2509" -> "Homework")
                const field = key.split('.')[0];
                // Capitalize first letter and translate
                const fieldMap = {
                  'homework': 'Uy vazifasi',
                  'comment': 'Izoh',
                  'score': 'Baho',
                  'status': 'Status'
                };
                return fieldMap[field] || field;
              });
              
              // Remove duplicates
              const uniqueFields = [...new Set(fieldNames)];
              const fieldsText = uniqueFields.join(', ');
              
              webApp.showAlert(`Xatoliklar bor. ${fieldsText} maydonlari to'ldirilishi shart.`);
            } else {
              webApp.showAlert("Xatoliklar bor. Iltimos, barcha majburiy maydonlarni to'ldiring.");
            }
          } catch (e) {
            console.warn("Alert not supported:", e);
          }
        }
      },
    }
  );
};

const getInitials = (name, surname) => {
  return `${name.charAt(0)}${surname ? surname.charAt(0) : ""}`;
};

const markAllPresent = () => {
  props.students.forEach((student) => {
    form.status[student.id] = true;
  });
  triggerHapticFeedback();
};

const markAllAbsent = () => {
  props.students.forEach((student) => {
    form.status[student.id] = false;
  });
  triggerHapticFeedback();
};

const toggleAttendance = (studentId) => {
  form.status[studentId] = !form.status[studentId];
  triggerHapticFeedback();
};
</script>

<style scoped>
.pb-safe {
  padding-bottom: env(safe-area-inset-bottom, 0);
}

@keyframes slide-down {
  from {
    opacity: 0;
    transform: translateY(-10px);
}
  to {
    opacity: 1;
    transform: translateY(0);
}
}

.animate-slide-down {
  animation: slide-down 0.3s ease-out;
}
</style>
