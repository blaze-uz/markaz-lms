<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const emits = defineEmits(["update:modelValue"]);
const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  selectedQuiz: {
    type: Object,
    required: true,
  },
});

const loading = ref(true);

watch(
  () => props.selectedQuiz,
  (newVal) => {
    if (newVal) {
      loading.value = false;
    }
  }
);

function closeModal() {
  triggerHapticFeedback("selectionChanged");
  updateModalValue(false);
}

function startTest(quizId) {
  triggerHapticFeedback("success");
  updateModalValue(false);
  router.get(`/test/${quizId}/start`);
}

function updateModalValue(value) {
  emits("update:modelValue", value);
}

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
  <!-- Main modal -->
  <Transition name="test-modal">
    <div
      v-show="modelValue"
      id="default-modal"
      tabindex="-1"
      aria-hidden="true"
      class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full"
    >
      <div class="relative w-full max-w-2xl max-h-full p-4 m-auto">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
          <!-- Modal header -->
          <div
            class="flex items-center justify-between p-4 border-b rounded-t md:p-5 dark:border-gray-600"
          >
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
              Imtihon haqida ma'lumot
            </h3>
            <button
              type="button"
              class="inline-flex items-center justify-center w-8 h-8 text-sm text-gray-400 bg-transparent rounded-lg hover:bg-gray-200 hover:text-gray-900 ms-auto dark:hover:bg-gray-600 dark:hover:text-white"
              data-modal-hide="default-modal"
              @click="closeModal"
            >
              <svg
                class="w-3 h-3"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 14 14"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                />
              </svg>
              <span class="sr-only" @click="closeModal"> Close modal </span>
            </button>
          </div>
          <!-- Modal body -->
          <div class="p-4 space-y-4 md:p-5">
            <div class="flex justify-center">
              <div
                class="relative flex items-center justify-center w-24 h-24 bg-white rounded-full shadow-md dark:bg-gray-800"
              >
                <span class="text-4xl">
                  {{ selectedQuiz?.emodji }}
                </span>
              </div>
            </div>
            <h2 class="text-lg font-semibold text-center text-gray-900 dark:text-white">
              {{ selectedQuiz?.name }}
            </h2>
            <p class="text-sm text-center text-gray-500 dark:text-gray-400">
              {{ selectedQuiz?.description }}
            </p>
            <div class="flex items-center justify-evenly">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Savollar:
                  <span class="font-semibold text-gray-900 dark:text-white">
                    {{ selectedQuiz?.tests_count }}
                  </span>
                  ta
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  O'tish balli:
                  <span class="font-semibold text-gray-900 dark:text-white">
                    {{ selectedQuiz?.passing_percentage }}
                  </span>
                  %
                </p>
              </div>
            </div>
          </div>
          <!-- Modal footer -->
          <div
            class="flex items-center justify-between p-4 border-t border-gray-200 rounded-b md:p-5 dark:border-gray-600"
          >
            <button
              data-modal-hide="default-modal"
              type="button"
              class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
              @click="closeModal"
            >
              Yopish
            </button>
            <button
              data-modal-hide="default-modal"
              type="button"
              class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
              @click="startTest(selectedQuiz.id)"
            >
              Boshlash
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>
<style scoped>
.test-modal-enter-active,
.test-modal-leave-active {
  transition: opacity 0.3s;
}
.test-modal-enter-from,
.test-modal-leave-to {
  opacity: 0;
}
</style>
