<script setup>
import { reactive, ref, nextTick, onMounted, onUnmounted, getCurrentInstance } from "vue";
import axios from "axios";

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  lead: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "converted"]);

const { proxy } = getCurrentInstance();

const loading = ref(false);
const errors = ref({});
const studentData = ref({
  name: "",
  surname: "",
  phone: "",
  phone2: "",
  address: "",
  comment: "",
});

// Phone input masking
const loadInputmaskScript = () => {
  return new Promise((resolve, reject) => {
    // Check if Inputmask is already loaded
    if (window.Inputmask) {
      resolve();
      return;
    }

    // Create script element
    const script = document.createElement("script");
    script.src = "/assets/js/inputmask/inputmask.js";
    script.onload = () => resolve();
    script.onerror = () => reject(new Error("Failed to load Inputmask"));
    document.head.appendChild(script);
  });
};

const initializePhoneMask = async () => {
  try {
    await loadInputmaskScript();

    // Wait a bit more to ensure the library is fully loaded
    await new Promise((resolve) => setTimeout(resolve, 100));

    const phoneInput = document.querySelector("#convertPhone");
    if (phoneInput && window.Inputmask) {
      const im = new window.Inputmask("+\\9\\98(99)999-99-99", {
        clearIncomplete: true,
        removeMaskOnSubmit: true,
      });
      im.mask(phoneInput);
    }
  } catch (error) {
    console.error("Error loading phone mask:", error);
  }
};

// Initialize data when lead prop changes
const initializeStudentData = () => {
  if (props.lead) {
    // Try to split the lead name into first and last name
    const fullName = props.lead.name || "";
    const nameParts = fullName.trim().split(" ");

    // Pre-fill student data from lead
    studentData.value = {
      name: nameParts[0] || "",
      surname: nameParts.slice(1).join(" ") || "",
      phone: props.lead.phone || "",
      phone2: props.lead.phone2 || "",
      address: props.lead.address || "",
      comment: props.lead.comment || "",
    };
  }
};

// Watch for visibility changes to initialize data and phone mask
const handleVisibilityChange = async () => {
  if (props.visible && props.lead) {
    initializeStudentData();
    await nextTick();
    await initializePhoneMask();
  }
};

// Handle swap name and surname
const handleSwapNames = () => {
  const tempName = studentData.value.name;
  studentData.value.name = studentData.value.surname;
  studentData.value.surname = tempName;
};

// Client-side validation
const validateForm = () => {
  errors.value = {};

  if (!studentData.value.name?.trim()) {
    errors.value.name = proxy.$t("translation.validation.required", {
      attribute: proxy.$t("translation.students.name"),
    });
  }

  if (!studentData.value.surname?.trim()) {
    errors.value.surname = proxy.$t("translation.validation.required", {
      attribute: proxy.$t("translation.students.surname"),
    });
  }

  if (!studentData.value.phone?.trim()) {
    errors.value.phone = proxy.$t("translation.validation.required", {
      attribute: proxy.$t("translation.students.phone"),
    });
  }

  return Object.keys(errors.value).length === 0;
};

// Handle convert confirm
const handleConfirmConvert = async () => {
  if (!props.lead || loading.value) return;

  // Clear previous errors
  errors.value = {};

  // Client-side validation
  if (!validateForm()) {
    return;
  }

  try {
    loading.value = true;

    const formData = new FormData();
    formData.append("lead_id", props.lead.id);

    // Send name and surname separately
    formData.append("name", studentData.value.name.trim());
    formData.append("surname", studentData.value.surname.trim());
    if (studentData.value.phone) {
      formData.append("phone", studentData.value.phone);
    }
    if(studentData.value.phone2) {
      formData.append("phone2", studentData.value.phone2);
    }
    if(studentData.value.address) {
      formData.append("address", studentData.value.address);
    }
    if (studentData.value.comment) {
      formData.append("comment", studentData.value.comment);
    }

    const response = await axios.post("/admin/leads/convert-to-student", formData, {
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content"),
      },
    });

    if (response.data.success) {
      if (window.notifyMessage) {
        window.notifyMessage(
          response.data.message || "Student created successfully",
          "success"
        );
      }

      emit("converted", props.lead);
      handleClose();
    }
  } catch (error) {
    console.error("Error converting lead to student:", error);

    // Handle validation errors from backend
    if (error.response?.status === 422 && error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      if (window.notifyMessage) {
        const errorMessage =
          error.response?.data?.message || "Failed to convert lead to student";
        window.notifyMessage(errorMessage, "danger");
      }
    }
  } finally {
    loading.value = false;
  }
};

// Handle modal close
const handleClose = () => {
  // Reset form data and errors
  studentData.value = {
    name: "",
    surname: "",
    phone: "",
    phone2: "",
    address: "",
    comment: "",
  };
  errors.value = {};
  loading.value = false;
  emit("close");
};

// Handle ESC key press to close modal
const handleEscKey = (event) => {
  if (event.key === "Escape" && !loading.value && props.visible) {
    handleClose();
  }
};

onMounted(() => {
  // Add ESC key listener
  document.addEventListener("keydown", handleEscKey);

  // Initialize if already visible
  if (props.visible) {
    handleVisibilityChange();
  }
});

// Remove ESC key listener when component unmounts
onUnmounted(() => {
  document.removeEventListener("keydown", handleEscKey);
});

// Watch for prop changes
import { watch } from "vue";
watch(
  () => props.visible,
  (newValue) => {
    if (newValue) {
      handleVisibilityChange();
    }
  }
);
</script>

<template>
  <div v-if="visible">
    <!-- Modal backdrop -->
    <div class="modal-backdrop fade show"></div>

    <!-- Modal -->
    <div
      class="modal fade show"
      style="display: block"
      tabindex="-1"
      @click.self="handleClose"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="ri-user-add-line me-2"></i>
              {{ $t("translation.leads.convert_to_student") }}
            </h5>
            <button
              type="button"
              class="btn-close"
              @click="handleClose"
              :disabled="loading"
            ></button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="handleConfirmConvert">
              <div class="row">
                <div class="col">
                  <div class="mb-3">
                    <label for="convertName">
                      {{ $t("translation.students.name") }}
                      <span class="text-danger">*</span>
                    </label>
                    <input
                      type="text"
                      id="convertName"
                      class="form-control"
                      :class="{ 'is-invalid': errors.name }"
                      v-model="studentData.name"
                      :disabled="loading"
                      required
                    />
                    <div v-if="errors.name" class="invalid-feedback">
                      {{ errors.name }}
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="mb-3">
                    <label for="convertSurname">
                      {{ $t("translation.students.surname") }}
                      <span class="text-danger">*</span>
                    </label>
                    <input
                      type="text"
                      id="convertSurname"
                      class="form-control"
                      :class="{ 'is-invalid': errors.surname }"
                      v-model="studentData.surname"
                      :disabled="loading"
                      required
                    />
                    <div v-if="errors.surname" class="invalid-feedback">
                      {{ errors.surname }}
                    </div>
                  </div>
                </div>
                <div class="col-auto">
                  <div class="mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                      <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        @click="handleSwapNames"
                        :disabled="loading"
                        :title="$t('translation.leads.swap_names')"
                      >
                        <i class="ri-refresh-line"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="lead?.phone" class="mb-3 form-group">
                <label for="convertPhone">
                  {{ $t("translation.students.phone") }}
                  <span class="text-danger">*</span>
                </label>
                <input
                  type="tel"
                  id="convertPhone"
                  class="form-control phone"
                  :class="{ 'is-invalid': errors.phone }"
                  v-model="studentData.phone"
                  :disabled="loading"
                  required
                  placeholder="Phone"
                />
                <div v-if="errors.phone" class="invalid-feedback">
                  {{ errors.phone }}
                </div>
              </div>

              <div class="mb-3 form-group">
                <label for="convertPhone2">{{ $t("translation.students.phone2") }}</label>
                <input
                  type="tel"
                  id="convertPhone2"
                  class="form-control"
                  :class="{ 'is-invalid': errors.phone2 }"
                  v-model="studentData.phone2"
                  :disabled="loading"
                  placeholder="+998"
                />
                <div v-if="errors.phone2" class="invalid-feedback">
                  {{ errors.phone2 }}
                </div>
              </div>

              <div class="mb-3">
                <label for="convertAddress">{{ $t("translation.students.address") }}</label>
                <input
                  type="text"
                  id="convertAddress"
                  class="form-control"
                  :class="{ 'is-invalid': errors.address }"
                  v-model="studentData.address"
                  :disabled="loading"
                  :placeholder="$t('translation.students.address')"
                />
                <div v-if="errors.address" class="invalid-feedback">
                  {{ errors.address }}
                </div>
              </div>

              <div class="mb-3">
                <label for="convertComment">{{
                  $t("translation.students.comment")
                }}</label>
                <textarea
                  id="convertComment"
                  class="form-control"
                  v-model="studentData.comment"
                  :disabled="loading"
                  :placeholder="$t('translation.students.comment')"
                  rows="3"
                ></textarea>
              </div>

              <div class="mb-3">
                <button class="btn btn-success w-100" type="submit" :disabled="loading">
                  <span
                    v-if="loading"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                  ></span>
                  <i v-else class="fas fa-save me-2"></i>
                  {{
                    loading
                      ? $t("translation.leads.converting")
                      : $t("translation.btn.save")
                  }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal {
  z-index: 1050;
}

.modal-backdrop {
  z-index: 1040;
}
</style>
