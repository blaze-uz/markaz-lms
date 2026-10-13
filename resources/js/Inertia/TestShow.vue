<script setup>
import { computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useDark } from "@vueuse/core";

const isDark = useDark();

const props = defineProps({
  quiz: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  answers: {},
  quizId: props.quiz.id,
  uniqueId: props.quiz.unique_id,
  currentQuestionIndex: 0,
});

const currentQuestion = computed(() => props.quiz.tests[form.currentQuestionIndex]);

// Check if all questions have been answered
const allQuestionsAnswered = computed(() => {
  console.log(props.quiz);
  return props.quiz.tests.every((question) => form.answers[question.id] !== undefined);
});

// Check if the current question is the last one
const isLastQuestion = computed(() => {
  return form.currentQuestionIndex === props.quiz.tests.length - 1;
});

// Check if it's the first question
const isFirstQuestion = computed(() => {
  return form.currentQuestionIndex === 0;
});

// Check if the "Next" button should be disabled
const isNextDisabled = computed(() => {
  return isLastQuestion.value;
});

const nextQuestion = () => {
  if (isLastQuestion.value) {
    if (allQuestionsAnswered.value) {
      form.post("/save-answers");
      triggerHapticFeedback("success");
    }
  } else {
    form.currentQuestionIndex++;
    triggerHapticFeedback("selectionChanged");
  }
};

const prevQuestion = () => {
  if (form.currentQuestionIndex > 0) {
    form.currentQuestionIndex--;
    triggerHapticFeedback("selectionChanged");
  }
};

const selectAnswer = (questionId, answerId) => {
  form.answers[questionId] = answerId;
  triggerHapticFeedback("selectionChanged");
};

const handleSubmit = () => {
  console.log("Submitting answers...");

  form.post(`/test/${props.quiz.id}/save`, {
    preserveState: true,
  });

  triggerHapticFeedback("success");
};

// Function to get the Telegram Web App instance
const getTelegramWebApp = () => {
  return window.Telegram.WebApp;
};

const backToMain = () => {
  router.visit("/test");
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
  <div class="w-full test-container">
    <div class="w-full px-3">
      <!-- Check if there are tests in the quiz -->
      <div v-if="props.quiz.tests && props.quiz.tests.length > 0">
        <div class="mb-5 question">
          <div
            class="flex items-center justify-center flex-shrink-0 w-6 h-6 mb-3 text-white rounded-full bg-slate-400"
          >
            {{ form.currentQuestionIndex + 1 }}
          </div>
          <div>
            {{ currentQuestion?.question }}
          </div>
        </div>
        <div class="grid gap-2">
          <div
            v-for="answer in currentQuestion?.answers"
            :key="answer.id"
            class="flex items-center gap-2"
          >
            <input
              type="radio"
              :name="`question-${currentQuestion.id}`"
              class="hidden peer"
              :id="`question-${currentQuestion.id}-answer-${answer.id}`"
              :value="answer.id"
              v-model="form.answers[currentQuestion.id]"
              @change="selectAnswer(currentQuestion.id, answer.id)"
            />
            <label
              class="w-full h-full p-4 transition-all duration-100 rounded-lg cursor-pointer bg-slate-100 hover:bg-slate-200 peer-checked:bg-purple-500 peer-checked:text-white dark:text-white dark:bg-gray-500"
              :for="`question-${currentQuestion.id}-answer-${answer.id}`"
            >
              {{ answer.answer }}
            </label>
          </div>
        </div>
        <div class="flex justify-between mt-5">
          <button
            @click="prevQuestion"
            :disabled="isFirstQuestion"
            class="px-4 py-2 text-white bg-gray-500 rounded-lg disabled:opacity-50"
          >
            Oldingi
          </button>
          <button
            @click="nextQuestion"
            :disabled="isNextDisabled"
            class="px-4 py-2 text-white bg-purple-500 rounded-lg disabled:opacity-50"
          >
            Keyingi
          </button>
        </div>
        <div v-if="allQuestionsAnswered && isLastQuestion" class="w-full mt-5">
          <button
            @click="handleSubmit"
            class="w-full px-4 py-2 text-white bg-green-500 rounded-lg"
          >
            Tugatish
          </button>
        </div>
      </div>
      <!-- Show this if there are no tests -->
      <div v-else>
        <p class="text-center text-gray-500">🤷‍♂️ Ma'lumotlar topilmadi</p>
        <!-- back home -->
        <div class="flex justify-center">
          <button
            @click="backToMain"
            class="px-4 py-2 mt-5 text-white bg-blue-600 rounded-lg hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600"
          >
            🏠 Asosiy sahifaga qaytish
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.test-container {
  height: 80vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
