<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const props = defineProps({
  lead: Object,
  visible: Boolean,
});

const emit = defineEmits(["close", "confirm"]);

const loading = ref(false);

const handleConfirm = () => {
  if (loading.value) return;
  loading.value = true;
  emit("confirm", props.lead);
};

const handleCancel = () => {
  if (loading.value) return;
  emit("close");
};

// Handle ESC key press to close modal
const handleEscKey = (event) => {
  if (event.key === "Escape" && !loading.value) {
    handleCancel();
  }
};

onMounted(() => {
  document.addEventListener("keydown", handleEscKey);
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleEscKey);
});

// Expose loading state to parent
defineExpose({
  setLoading: (state) => {
    loading.value = state;
  },
});
</script>

<template>
  <div v-if="visible">
    <div class="modal-backdrop fade show"></div>
    <div
      class="modal fade show"
      tabindex="-1"
      style="display: block"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <button
              type="button"
              class="btn-close"
              @click="handleCancel"
              :disabled="loading"
            ></button>
          </div>

          <div class="modal-body text-center pt-0">
            <div class="mb-4">
              <div class="avatar-lg mx-auto mb-4">
                <div
                  class="avatar-title bg-danger-subtle text-danger display-4 rounded-circle"
                >
                  <i class="ri-delete-bin-line"></i>
                </div>
              </div>
              <h4 class="mb-2">{{ $t("translation.leads.delete_lead") }}?</h4>
              <p class="text-muted fs-15 mb-4">
                {{ $t("translation.alert_messages.are_you_sure") }}
                <br />
                <strong class="text-danger">{{ lead?.name }}</strong> - {{ lead?.phone }}
              </p>
            </div>

            <div class="d-flex gap-2 justify-content-center">
              <button
                type="button"
                class="btn btn-light"
                @click="handleCancel"
                :disabled="loading"
              >
                <i class="ri-close-line me-1"></i>
                {{ $t("translation.cancel") }}
              </button>
              <button
                type="button"
                class="btn btn-danger"
                @click="handleConfirm"
                :disabled="loading"
              >
                <div
                  v-if="loading"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                >
                  <span class="visually-hidden">{{ $t("translation.loading") }}</span>
                </div>
                <i v-else class="ri-delete-bin-line me-1"></i>
                {{ $t("translation.leads.delete") }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-content {
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.avatar-lg {
  width: 5rem;
  height: 5rem;
}

.avatar-title {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-danger-subtle {
  background-color: rgba(220, 53, 69, 0.1) !important;
}

.text-danger {
  color: #dc3545 !important;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}
</style>
