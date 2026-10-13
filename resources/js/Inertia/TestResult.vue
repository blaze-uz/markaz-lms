<script setup>
import { router } from "@inertiajs/vue3";
import { useDark } from "@vueuse/core";
import { ref, defineProps } from "vue";
const isDark = useDark();
const props = defineProps({
  result: {
    type: Object,
    required: true,
  },
});

const backToMain = () => {
  router.visit("/test");
};
</script>
<template>
  <div class="max-w-sm p-4 mx-auto mt-5">
    <h2 class="text-lg font-semibold text-center text-gray-900 dark:text-white">
      Imtihon natijasi
    </h2>
    <div class="flex flex-col justify-center mt-5 align-center">
      <div class="relative mx-auto text-center size-40">
        <svg
          class="-rotate-90 size-full"
          viewBox="0 0 36 36"
          xmlns="http://www.w3.org/2000/svg"
        >
          <!-- Background Circle -->
          <circle
            cx="18"
            cy="18"
            r="16"
            fill="none"
            class="text-gray-200 stroke-current dark:bg-gray-900"
            stroke-width="2"
          ></circle>
          <!-- Progress Circle -->
          <circle
            cx="18"
            cy="18"
            r="16"
            fill="none"
            class="text-blue-600 stroke-current dark:text-blue-500"
            stroke-width="2"
            stroke-dasharray="100"
            :stroke-dashoffset="100 - props.result.score"
            stroke-linecap="round"
          ></circle>
        </svg>

        <!-- Centered Percentage Text -->
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-2xl font-bold text-center text-blue-600 dark:text-blue-500">
            {{ props.result.score }}%
          </span>
        </div>
      </div>
      <div class="mt-5 text-center">
        <span class="text-lg font-semibold text-center text-gray-800 dark:text-white">
          {{
            props.result.status === "passed"
              ? "🎉 Muvaffaqiyatli o'tdingiz"
              : "❎ Qayta urinib ko'ring"
          }}
        </span>
      </div>

      <!-- back to main button -->
      <button
        @click="backToMain"
        class="w-full px-4 py-2 mt-5 text-white bg-blue-600 rounded-lg hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600"
      >
        Asosiy sahifaga qaytish
      </button>
    </div>
  </div>
</template>
