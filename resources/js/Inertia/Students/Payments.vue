<template>
  <StudentLayout :settings="settings">
    <div class="space-y-4 pb-20">
      <!-- Header with Balance -->
      <div
        class="overflow-hidden relative backdrop-blur-xl bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-600 rounded-3xl shadow-2xl"
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
              <h1 class="text-2xl font-bold text-white mb-1">To'lovlar</h1>
              <p class="text-sm font-medium text-white/80">Balans va to'lovlar tarixi</p>
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
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/20">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-medium text-white/70 mb-1">Joriy balans</p>
                <p
                  class="text-2xl font-bold"
                  :class="balance >= 0 ? 'text-white' : 'text-red-200'"
                >
                  {{ formatCurrency(Math.abs(balance)) }}
                </p>
                <p class="text-xs font-medium text-white/70 mt-1">
                  <span v-if="balance >= 0">To'landi</span>
                  <span v-else>Qarz</span>
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-white/70 mb-1">Jami to'lovlar</p>
                <p class="text-2xl font-bold text-white">{{ payments.length }}</p>
                <p class="text-xs font-medium text-white/70 mt-1">To'lovlar soni</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Debts Section -->
      <div
        v-if="debts.length > 0"
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
              Qarzdorliklar
            </h2>
            <div class="flex items-center gap-3">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                Jami: {{ debts.length }} ta
              </span>
              <span class="text-xs font-medium text-red-600 dark:text-red-400">
                {{ formatCurrency(totalDebtAmount) }} so'm
              </span>
            </div>
          </div>
        </div>
        <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
          <div
            v-for="debt in debts"
            :key="debt.id"
            class="px-5 py-4 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30"
          >
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ debt.group_name }}
                  </h3>
                  <span
                    v-if="debt.is_paid"
                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-green-700 dark:text-green-400 bg-green-100/80 dark:bg-green-900/40 rounded-full"
                  >
                    To'landi
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400 bg-red-100/80 dark:bg-red-900/40 rounded-full"
                  >
                    To'lanmagan
                  </span>
                </div>
                <div class="flex flex-col gap-1.5 mt-2">
                  <div
                    v-if="debt.range"
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
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                      />
                    </svg>
                    <span>{{ formatDebtRange(debt.range) }}</span>
                  </div>
                  <div class="flex items-center gap-4 mt-1">
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Jami:</p>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ formatCurrency(debt.total_amount) }}
                      </p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">To'langan:</p>
                      <p class="text-sm font-medium text-green-600 dark:text-green-400">
                        {{ formatCurrency(debt.paid_amount) }}
                      </p>
                    </div>
                    <div v-if="!debt.is_paid">
                      <p class="text-xs text-gray-500 dark:text-gray-400">Qolgan:</p>
                      <p class="text-sm font-medium text-red-600 dark:text-red-400">
                        {{ formatCurrency(debt.left_amount) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex flex-col items-end gap-2 ml-3 flex-shrink-0">
                <div
                  class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden"
                >
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="debt.is_paid ? 'bg-green-500' : 'bg-red-500'"
                    :style="{
                      width: `${
                        debt.is_paid ? 100 : (debt.left_amount / debt.total_amount) * 100
                      }%`,
                    }"
                  ></div>
                </div>
                <div v-if="debt.date" class="text-xs text-gray-500 dark:text-gray-400">
                  {{ debt.date }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payments List -->
      <div
        class="overflow-hidden backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-2xl border border-white/30 dark:border-gray-700/30 shadow-lg"
      >
        <div class="px-5 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
              To'lovlar tarixi
            </h2>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
              {{ payments.length }} ta
            </span>
          </div>
        </div>

        <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
          <template v-if="payments.length">
            <div
              v-for="payment in payments"
              :key="payment.id"
              class="px-5 py-4 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30"
            >
              <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                      {{ formatCurrency(payment.amount) }} UZS
                    </span>
                    <span
                      v-if="payment.has_refund"
                      class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400 bg-red-100/80 dark:bg-red-900/40 rounded-full"
                    >
                      Qaytarildi
                    </span>
                  </div>
                  <div class="flex flex-col gap-1.5 mt-2">
                    <div
                      v-if="payment.group_name"
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
                      <span class="truncate">{{ payment.group_name }}</span>
                    </div>
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
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                      </svg>
                      <span>{{ payment.payment_type }}</span>
                    </div>
                    <div
                      v-if="payment.comment"
                      class="flex items-start gap-2 text-xs text-gray-500 dark:text-gray-500 mt-0.5"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-3.5 h-3.5 mt-0.5 flex-shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                        />
                      </svg>
                      <span class="line-clamp-2">{{ payment.comment }}</span>
                    </div>
                    <div
                      v-if="payment.transaction_id"
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
                          d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"
                        />
                      </svg>
                      <span class="font-mono truncate"
                        >ID: {{ payment.transaction_id }}</span
                      >
                    </div>
                  </div>
                </div>
                <div class="flex flex-col items-end gap-2 ml-3 flex-shrink-0">
                  <div class="text-xs font-medium text-gray-900 dark:text-white">
                    {{ formatDate(payment.date) }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatTime(payment.date) }}
                  </div>
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
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                  To'lovlar mavjud emas
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Hozircha to'lovlar tarixi mavjud emas
                </p>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>

<script setup>
import { computed } from "vue";
import StudentLayout from "../Layouts/StudentLayout.vue";

const props = defineProps({
  payments: {
    type: Array,
    required: true,
  },
  debts: {
    type: Array,
    default: () => [],
  },
  balance: {
    type: Number,
    required: true,
  },
  settings: {
    type: Object,
    required: true,
  },
});

const formatCurrency = (amount) => {
  if (!amount && amount !== 0) return "0";
  return new Intl.NumberFormat("uz-UZ").format(Math.abs(amount));
};

const formatDate = (dateString) => {
  if (!dateString) return "-";
  const [date] = dateString.split(" ");
  return date;
};

const formatTime = (dateString) => {
  if (!dateString) return "";
  const parts = dateString.split(" ");
  return parts.length > 1 ? parts[1] : "";
};

const totalDebtAmount = computed(() => {
  return props.debts
    .filter((debt) => !debt.is_paid)
    .reduce((sum, debt) => sum + (debt.left_amount || 0), 0);
});

const lastPaymentDate = computed(() => {
  if (!props.payments.length) return null;
  const firstPayment = props.payments[0];
  return formatDate(firstPayment.date);
});

const formatDebtRange = (range) => {
  if (!range) return "-";
  const parts = range.split("/");
  if (parts.length === 2) {
    const [start, end] = parts;
    const startDate = new Date(start);
    const endDate = new Date(end);
    return `${startDate.toLocaleDateString("uz-UZ", {
      month: "short",
      day: "numeric",
    })} - ${endDate.toLocaleDateString("uz-UZ", {
      month: "short",
      day: "numeric",
      year: "numeric",
    })}`;
  }
  return range;
};
</script>
