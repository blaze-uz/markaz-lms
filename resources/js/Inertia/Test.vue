<script setup>
import { useDark } from "@vueuse/core";
import TestInfoModal from "./TestInfoModal.vue";
import { ref, defineProps } from "vue";
import { router } from "@inertiajs/vue3";

const isDark = useDark();
const showInfoModal = ref(false);

const props = defineProps({
  quizzes: Array,
  selectedQuiz: Object,
});

const selectQuiz = (quizId) => {
  triggerHapticFeedback("selectionChanged");
  router.reload({
    only: ["selectedQuiz"],
    data: {
      quiz_id: quizId,
    },
  });
  showInfoModal.value = true;
};

// Function to get the Telegram Web App instance
const getTelegramWebApp = () => {
  return window.Telegram.WebApp;
};

// Function to trigger haptic feedback, checking if supported
const triggerHapticFeedback = (type) => {
  const webApp = getTelegramWebApp();
  if (webApp && webApp.HapticFeedback) {
    try {
      switch (type) {
        case "selectionChanged":
          webApp.HapticFeedback.selectionChanged();
          break;
        case "success":
          webApp.HapticFeedback.notificationOccurred("success");
          break;
        case "error":
          webApp.HapticFeedback.notificationOccurred("error");
          break;
        case "warning":
          webApp.HapticFeedback.notificationOccurred("warning");
          break;
        default:
          webApp.HapticFeedback.impactOccurred("light");
      }
    } catch (e) {
      console.warn("HapticFeedback not supported:", e);
    }
  }
};
</script>

<template>
  <div class="max-w-sm p-4 mx-auto">
    <h2 class="text-lg font-semibold text-center text-gray-900 dark:text-white">
      Mavjud imtihonlar
    </h2>
    <div
      v-if="quizzes.length > 0"
      class="flex items-center p-4 mt-4 bg-gray-100 rounded-lg"
      v-for="quiz in quizzes"
      :key="quiz.id"
      @click="selectQuiz(quiz.id)"
    >
      <div
        class="relative flex items-center justify-center w-12 h-12 bg-white rounded-full shadow-md"
      >
        {{ quiz.emodji }}
      </div>
      <div class="ml-4">
        <div class="font-medium text-gray-800">
          {{ quiz.name }}
        </div>
      </div>
      <div class="ml-auto text-right">
        <div
          class="relative flex items-center justify-center w-8 h-8 bg-white rounded-full shadow-md"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-6 h-6 text-gray-800"
          >
            <path
              d="M13.1717 12.0007L8.22192 7.05093L9.63614 5.63672L16.0001 12.0007L9.63614 18.3646L8.22192 16.9504L13.1717 12.0007Z"
            ></path>
          </svg>
        </div>
      </div>
    </div>
    <div
      v-else
      class="flex flex-col justify-center mt-4 text-center text-gray-500 dark:text-gray-400"
    >
      <h1 class="text-center" style="font-size: 3rem">🤷‍♂️</h1>
      <p class="mt-4">Ma'lumot topilmadi</p>
    </div>
  </div>
  <TestInfoModal v-model="showInfoModal" :selectedQuiz="selectedQuiz" />
</template>
