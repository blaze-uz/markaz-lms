<script setup>
import {
  reactive,
  ref,
  onMounted,
  onUnmounted,
  nextTick,
  getCurrentInstance,
  watch,
} from "vue";
import axios from "axios";
import DynamicLeadFields from "./DynamicLeadFields.vue";

const props = defineProps({
  collections: Array,
  courses: Array,
  teachers: Array,
  boards: Array,
  leadDynamicFields: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "created"]);

const { proxy } = getCurrentInstance();

const form = reactive({
  name: "",
  phone: "",
  phone2: "",
  address: "",
  collection_id: "",
  board_id: "",
  course_id: "",
  teacher_id: "",
  chosen_days: "",
  chosen_time: "",
  comment: "",
  custom_attributes: {},
});

watch(
  () => props.leadDynamicFields,
  (fields) => {
    if (!fields?.length) {
      return;
    }
    for (const f of fields) {
      if (form.custom_attributes[f.key] === undefined) {
        form.custom_attributes[f.key] = "";
      }
    }
  },
  { immediate: true, deep: true }
);

const errors = reactive({});
const loading = ref(false);

// Day options for button checkboxes
const dayOptions = [
  { value: "odd", label: "translation.leads.odd" },
  { value: "even", label: "translation.leads.even" },
];

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

    const phoneInput = document.querySelector("#phone");
    if (phoneInput && window.Inputmask) {
      const im = new window.Inputmask("+\\9\\98(99)999-99-99", {
        clearIncomplete: true,
        removeMaskOnSubmit: true,
      });
      im.mask(phoneInput);
    }
    const phoneInput2 = document.querySelector("#phone2");
    if (phoneInput2 && window.Inputmask) {
      const im = new window.Inputmask("+\\9\\98(99)999-99-99", {
        clearIncomplete: true,
        removeMaskOnSubmit: true,
      });
      im.mask(phoneInput2);
    }
  } catch (error) {
    console.error("Error loading phone mask:", error);
  }
};

onMounted(async () => {
  await nextTick();
  // Initialize phone input mask
  await initializePhoneMask();
});

const handleCollectionSelect = (collectionId) => {
  form.collection_id = form.collection_id === collectionId ? "" : collectionId;
  if (errors.collection_id) {
    delete errors.collection_id;
  }
};

const handleDaySelect = (dayValue) => {
  form.chosen_days = form.chosen_days === dayValue ? "" : dayValue;
  if (errors.chosen_days) {
    delete errors.chosen_days;
  }
};

const handleSubmit = async () => {
  if (loading.value) return;

  loading.value = true;
  Object.keys(errors).forEach((key) => delete errors[key]);

  try {
    console.log("Form data being sent:", form); // Debug log
    const response = await axios.post("/admin/leads", form, {
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content"),
      },
    });

    if (response.data.success) {
      emit("created", response.data.lead);

      // Show success message
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }

      emit("close");
    }
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors || {});
    } else {
      if (window.notifyMessage) {
        window.notifyMessage(proxy.$t("translation.alert_messages.error"), "danger");
      }
    }
  } finally {
    loading.value = false;
  }
};

const handleClose = () => {
  emit("close");
};

// Handle ESC key press to close modal
const handleEscKey = (event) => {
  if (event.key === "Escape" && !loading.value) {
    handleClose();
  }
};

onMounted(() => {
  // Add ESC key listener
  document.addEventListener("keydown", handleEscKey);
});

// Remove ESC key listener when component unmounts
onUnmounted(() => {
  document.removeEventListener("keydown", handleEscKey);
});

const courseSearchQuery = ref("");
const selectedCourse = ref(null);
const showCourseDropdown = ref(false);
const filteredCourses = ref([]);

const filterCourses = () => {
  if (courseSearchQuery.value.length === 0) {
    // Show all courses when no search query
    filteredCourses.value = props.courses;
  } else {
    // Filter courses based on search query
    filteredCourses.value = props.courses.filter((course) =>
      course.name.toLowerCase().includes(courseSearchQuery.value.toLowerCase())
    );
  }
};

const selectCourse = (course) => {
  form.course_id = course.id;
  selectedCourse.value = course;
  courseSearchQuery.value = "";
  showCourseDropdown.value = false;
  if (errors.course_id) {
    delete errors.course_id;
  }
  console.log("Selected course:", course.id, course.name); // Debug log
};

const openCourseDropdown = () => {
  courseSearchQuery.value = "";
  showCourseDropdown.value = true;
  filterCourses();
};

const clearCourse = () => {
  form.course_id = "";
  selectedCourse.value = null;
  courseSearchQuery.value = "";
  showCourseDropdown.value = false;
};

const hideCourseDropdown = () => {
  setTimeout(() => {
    showCourseDropdown.value = false;
    courseSearchQuery.value = "";
  }, 200);
};

const teacherSearchQuery = ref("");
const selectedTeacher = ref(null);
const showTeacherDropdown = ref(false);
const filteredTeachers = ref([]);

const filterTeachers = () => {
  if (teacherSearchQuery.value.length === 0) {
    // Show all teachers when no search query
    filteredTeachers.value = props.teachers;
  } else {
    // Filter teachers based on search query
    filteredTeachers.value = props.teachers.filter((teacher) =>
      teacher.name.toLowerCase().includes(teacherSearchQuery.value.toLowerCase())
    );
  }
};

const selectTeacher = (teacher) => {
  form.teacher_id = teacher.id;
  selectedTeacher.value = teacher;
  teacherSearchQuery.value = "";
  showTeacherDropdown.value = false;
  if (errors.teacher_id) {
    delete errors.teacher_id;
  }
  console.log("Selected teacher:", teacher.id, teacher.name); // Debug log
};

const openTeacherDropdown = () => {
  teacherSearchQuery.value = "";
  showTeacherDropdown.value = true;
  filterTeachers();
};

const clearTeacher = () => {
  form.teacher_id = "";
  selectedTeacher.value = null;
  teacherSearchQuery.value = "";
  showTeacherDropdown.value = false;
};

const hideTeacherDropdown = () => {
  setTimeout(() => {
    showTeacherDropdown.value = false;
    teacherSearchQuery.value = "";
  }, 200);
};
</script>

<template>
  <div>
    <div class="modal-backdrop fade show"></div>
    <div
      class="modal fade show"
      tabindex="-1"
      style="display: block"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="ri-user-add-line me-2"></i>
              {{ $t("translation.leads.create_lead") }}
            </h5>
            <button
              type="button"
              class="btn-close"
              @click="handleClose"
              :disabled="loading"
            ></button>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <!-- Name -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-user-line me-1"></i>
                    {{ $t("translation.leads.name") }}
                    <span class="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.name }"
                    v-model="form.name"
                    :placeholder="$t('translation.leads.name')"
                    :disabled="loading"
                    autocomplete="name"
                  />
                  <div v-if="errors.name" class="invalid-feedback">
                    {{ errors.name }}
                  </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-phone-line me-1"></i>
                    {{ $t("translation.leads.phone") }}
                    <span class="text-danger">*</span>
                  </label>
                  <input
                    type="tel"
                    id="phone"
                    class="form-control phone"
                    :class="{ 'is-invalid': errors.phone }"
                    v-model="form.phone"
                    :placeholder="$t('translation.leads.phone')"
                    :disabled="loading"
                    autocomplete="tel"
                  />
                  <div v-if="errors.phone" class="invalid-feedback">
                    {{ errors.phone }}
                  </div>
                </div>

                <!-- Phone 2 -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-phone-line me-1"></i>
                    {{ $t("translation.leads.phone") }} 2
                  </label>
                  <input
                    type="tel"
                    id="phone2"
                    class="form-control phone"
                    :class="{ 'is-invalid': errors.phone2 }"
                    v-model="form.phone2"
                    :placeholder="$t('translation.leads.phone') + ' 2'"
                    :disabled="loading"
                    autocomplete="tel"
                  />
                  <div v-if="errors.phone2" class="invalid-feedback">
                    {{ errors.phone2 }}
                  </div>
                </div>

                <!-- Address -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-map-pin-line me-1"></i>
                    {{ $t("translation.leads.address") }}
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.address }"
                    v-model="form.address"
                    :placeholder="$t('translation.leads.address')"
                    :disabled="loading"
                    autocomplete="street-address"
                  />
                  <div v-if="errors.address" class="invalid-feedback">
                    {{ errors.address }}
                  </div>
                </div>

                <DynamicLeadFields
                  v-model="form.custom_attributes"
                  :fields="leadDynamicFields"
                  :errors="errors"
                  :disabled="loading"
                />

                <!-- Collections - Button Checkbox -->
                <div class="col-12">
                  <label class="form-label">
                    <i class="ri-folder-line me-1"></i>
                    {{ $t("translation.leads.collection") }}
                    <span class="text-danger">*</span>
                  </label>
                  <div class="d-flex flex-wrap gap-2">
                    <div
                      v-for="collection in collections"
                      :key="collection.id"
                      class="form-check"
                    >
                      <input
                        type="radio"
                        class="btn-check"
                        :id="`collection-${collection.id}`"
                        :value="collection.id"
                        :checked="form.collection_id == collection.id"
                        @change="handleCollectionSelect(collection.id)"
                        :disabled="loading"
                      />
                      <label
                        class="btn btn-outline-primary"
                        :for="`collection-${collection.id}`"
                      >
                        {{ collection.name }}
                      </label>
                    </div>
                  </div>
                  <div v-if="errors.collection_id" class="text-danger small mt-1">
                    {{ errors.collection_id }}
                  </div>
                </div>

                <!-- Board -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-dashboard-line me-1"></i>
                    {{ $t("translation.leads.board") }}
                  </label>
                  <select
                    class="form-select"
                    :class="{ 'is-invalid': errors.board_id }"
                    v-model="form.board_id"
                    :disabled="loading"
                  >
                    <option value="">{{ $t("translation.leads.select_board") }}</option>
                    <option
                      v-for="board in props.boards"
                      :key="board.id"
                      :value="board.id"
                    >
                      {{ board.name }}
                    </option>
                  </select>
                  <div v-if="errors.board_id" class="invalid-feedback">
                    {{ errors.board_id }}
                  </div>
                </div>

                <!-- Course -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-book-line me-1"></i>
                    {{ $t("translation.leads.course") }}
                  </label>
                  <div class="position-relative">
                    <!-- Display input when something is selected -->
                    <div
                      v-if="selectedCourse && !showCourseDropdown"
                      class="form-control d-flex justify-content-between align-items-center"
                      :class="{ 'is-invalid': errors.course_id }"
                      @click="openCourseDropdown"
                      style="cursor: pointer"
                    >
                      <span>{{ selectedCourse.name }}</span>
                      <div class="d-flex align-items-center">
                        <i
                          class="ri-close-line me-1 text-muted"
                          @click.stop="clearCourse"
                          style="cursor: pointer"
                          title="Clear selection"
                        ></i>
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </div>
                    <!-- Search input when dropdown is open or nothing selected -->
                    <input
                      v-else
                      type="text"
                      class="form-control"
                      :class="{ 'is-invalid': errors.course_id }"
                      :placeholder="$t('translation.leads.select_course')"
                      v-model="courseSearchQuery"
                      @focus="openCourseDropdown"
                      @blur="hideCourseDropdown"
                      @input="filterCourses"
                      :disabled="loading"
                      autocomplete="off"
                    />
                    <div
                      v-if="showCourseDropdown"
                      class="dropdown-menu show position-absolute w-100"
                      style="max-height: 200px; overflow-y: auto; z-index: 1050"
                    >
                      <button
                        v-for="course in filteredCourses"
                        :key="course.id"
                        type="button"
                        class="dropdown-item"
                        @mousedown.prevent="selectCourse(course)"
                      >
                        {{ course.name }}
                      </button>
                      <div
                        v-if="filteredCourses.length === 0"
                        class="dropdown-item-text text-muted"
                      >
                        {{ $t("translation.no_results_found") }}
                      </div>
                    </div>
                  </div>
                  <div v-if="errors.course_id" class="invalid-feedback">
                    {{ errors.course_id }}
                  </div>
                </div>

                <!-- Teacher -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-user-3-line me-1"></i>
                    {{ $t("translation.leads.teacher") }}
                  </label>
                  <div class="position-relative">
                    <!-- Display input when something is selected -->
                    <div
                      v-if="selectedTeacher && !showTeacherDropdown"
                      class="form-control d-flex justify-content-between align-items-center"
                      :class="{ 'is-invalid': errors.teacher_id }"
                      @click="openTeacherDropdown"
                      style="cursor: pointer"
                    >
                      <span>{{ selectedTeacher.name }}</span>
                      <div class="d-flex align-items-center">
                        <i
                          class="ri-close-line me-1 text-muted"
                          @click.stop="clearTeacher"
                          style="cursor: pointer"
                          title="Clear selection"
                        ></i>
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </div>
                    <!-- Search input when dropdown is open or nothing selected -->
                    <input
                      v-else
                      type="text"
                      class="form-control"
                      :class="{ 'is-invalid': errors.teacher_id }"
                      :placeholder="$t('translation.leads.select_teacher')"
                      v-model="teacherSearchQuery"
                      @focus="openTeacherDropdown"
                      @blur="hideTeacherDropdown"
                      @input="filterTeachers"
                      :disabled="loading"
                      autocomplete="off"
                    />
                    <div
                      v-if="showTeacherDropdown"
                      class="dropdown-menu show position-absolute w-100"
                      style="max-height: 200px; overflow-y: auto; z-index: 1050"
                    >
                      <button
                        v-for="teacher in filteredTeachers"
                        :key="teacher.id"
                        type="button"
                        class="dropdown-item"
                        @mousedown.prevent="selectTeacher(teacher)"
                      >
                        {{ teacher.name }}
                      </button>
                      <div
                        v-if="filteredTeachers.length === 0"
                        class="dropdown-item-text text-muted"
                      >
                        {{ $t("translation.no_results_found") }}
                      </div>
                    </div>
                  </div>
                  <div v-if="errors.teacher_id" class="invalid-feedback">
                    {{ errors.teacher_id }}
                  </div>
                </div>

                <!-- Chosen Time -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-time-line me-1"></i>
                    {{ $t("translation.leads.chosen_time") }}
                  </label>
                  <input
                    type="time"
                    class="form-control"
                    :class="{ 'is-invalid': errors.chosen_time }"
                    v-model="form.chosen_time"
                    :disabled="loading"
                  />
                  <div v-if="errors.chosen_time" class="invalid-feedback">
                    {{ errors.chosen_time }}
                  </div>
                </div>

                <!-- Chosen Days - Button Checkbox -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ri-calendar-2-line me-1"></i>
                    {{ $t("translation.leads.chosen_days") }}
                  </label>
                  <div class="d-flex flex-wrap gap-2">
                    <div
                      v-for="dayOption in dayOptions"
                      :key="dayOption.value"
                      class="form-check"
                    >
                      <input
                        type="radio"
                        class="btn-check"
                        :id="`day-${dayOption.value}`"
                        :value="dayOption.value"
                        :checked="form.chosen_days === dayOption.value"
                        @change="handleDaySelect(dayOption.value)"
                        :disabled="loading"
                      />
                      <label
                        class="btn btn-outline-primary"
                        :for="`day-${dayOption.value}`"
                      >
                        {{ $t(dayOption.label) }}
                      </label>
                    </div>
                  </div>
                  <div v-if="errors.chosen_days" class="text-danger small mt-1">
                    {{ errors.chosen_days }}
                  </div>
                </div>

                <!-- Comment -->
                <div class="col-12">
                  <label class="form-label">
                    <i class="ri-message-2-line me-1"></i>
                    {{ $t("translation.leads.comment") }}
                  </label>
                  <textarea
                    class="form-control"
                    :class="{ 'is-invalid': errors.comment }"
                    v-model="form.comment"
                    :placeholder="$t('translation.leads.comment_placeholder')"
                    rows="3"
                    :disabled="loading"
                  ></textarea>
                  <div v-if="errors.comment" class="invalid-feedback">
                    {{ errors.comment }}
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-light"
                @click="handleClose"
                :disabled="loading"
              >
                <i class="ri-close-line me-1"></i>
                {{ $t("translation.cancel") }}
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <div
                  v-if="loading"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                >
                  <span class="visually-hidden">{{ $t("translation.loading") }}</span>
                </div>
                <i v-else class="ri-save-line me-1"></i>
                {{ $t("translation.leads.create_lead") }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-content {
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-label i {
  color: var(--bs-primary);
}

.btn-check:checked + .btn-outline-primary {
  background-color: #3f5189 !important;
  border-color: #3f5189 !important;
  color: white;
}

.btn-outline-primary {
  font-size: 0.875rem;
  padding: 0.375rem 0.75rem;
}

.form-check {
  padding-left: 0 !important;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.text-danger {
  color: #dc3545 !important;
}

.invalid-feedback {
  display: block;
}

/* Searchable select styles */
.dropdown-menu {
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  margin-top: 2px;
}

.dropdown-item {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  color: #212529;
  transition: background-color 0.15s ease-in-out;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
  color: #212529;
}

.dropdown-item:focus {
  background-color: #e9ecef;
  color: #212529;
  outline: none;
}

.dropdown-item-text {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-style: italic;
}
</style>
