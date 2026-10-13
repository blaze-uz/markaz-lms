<script setup>
import { ref, onMounted, getCurrentInstance } from "vue";
import axios from "axios";

const props = defineProps({
  leadId: {
    type: [Number, String],
    required: true,
  },
});

const loading = ref(true);
const reminders = ref([]);
const error = ref(null);
const showAddForm = ref(false);

// Form data for adding new reminder
const newReminder = ref({
  reminder_date: "",
  reminder_time: "",
  reminder_note: "",
});

// Form validation errors
const formErrors = ref({});

// Get translation helper
const { proxy } = getCurrentInstance();

// Fetch reminders for the lead
const fetchReminders = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/admin/leads/${props.leadId}/reminders`, {
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content"),
        Accept: "application/json",
      },
    });
    reminders.value = response.data;
  } catch (err) {
    error.value = err.message;
    console.error("Error fetching reminders:", err);
  } finally {
    loading.value = false;
  }
};

// Add new reminder
const addReminder = async () => {
  try {
    formErrors.value = {};

    const response = await axios.post(
      `/admin/leads/${props.leadId}/add-reminder`,
      newReminder.value,
      {
        headers: {
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content"),
          Accept: "application/json",
        },
      }
    );

    // Reset form and refresh reminders
    newReminder.value = {
      reminder_date: "",
      reminder_time: "",
      reminder_note: "",
    };
    showAddForm.value = false;
    await fetchReminders();
  } catch (err) {
    if (err.response?.status === 422) {
      formErrors.value = err.response.data.errors || {};
    } else {
      error.value = err.message;
    }
    console.error("Error adding reminder:", err);
  }
};

// Update reminder status
const updateReminderStatus = async (reminderId, status) => {
  try {
    await axios.put(
      `/admin/leads/${reminderId}/update-reminder`,
      { status },
      {
        headers: {
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content"),
          Accept: "application/json",
        },
      }
    );

    await fetchReminders();
  } catch (err) {
    error.value = err.message;
    console.error("Error updating reminder:", err);
  }
};

// Delete reminder
const deleteReminder = async (reminderId) => {
  if (!confirm(proxy.$t("translation.leads.confirm_delete_reminder"))) {
    return;
  }

  try {
    await axios.delete(`/admin/leads/reminders/${reminderId}`, {
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content"),
        Accept: "application/json",
      },
    });

    await fetchReminders();
  } catch (err) {
    error.value = err.message;
    console.error("Error deleting reminder:", err);
  }
};

// Format date for display
const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}.${month}.${year}`;
};

// Format time for display
const formatTime = (timeString) => {
  if (!timeString) return "";
  return timeString.substring(0, 5); // HH:MM format
};

// Get status badge class
const getStatusBadgeClass = (status) => {
  return status === "completed"
    ? "bg-success-subtle text-success"
    : "bg-warning-subtle text-warning";
};

// Load reminders when component mounts
onMounted(() => {
  fetchReminders();
});
</script>

<template>
  <div class="reminders-container">
    <!-- Header with Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="mb-0">{{ $t("translation.leads.reminders") }}</h6>
      <button class="btn btn-sm btn-primary" @click="showAddForm = !showAddForm">
        <i class="ri-add-line me-1"></i>
        {{ $t("translation.leads.add_reminder") }}
      </button>
    </div>

    <!-- Add Reminder Form -->
    <div v-if="showAddForm" class="card mb-3">
      <div class="card-body">
        <form @submit.prevent="addReminder">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">{{ $t("translation.leads.date") }}</label>
              <input
                type="date"
                class="form-control"
                :class="{ 'is-invalid': formErrors.reminder_date }"
                v-model="newReminder.reminder_date"
                required
              />
              <div v-if="formErrors.reminder_date" class="invalid-feedback">
                {{ formErrors.reminder_date[0] }}
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ $t("translation.leads.time") }}</label>
              <input
                type="time"
                class="form-control"
                :class="{ 'is-invalid': formErrors.reminder_time }"
                v-model="newReminder.reminder_time"
                required
              />
              <div v-if="formErrors.reminder_time" class="invalid-feedback">
                {{ formErrors.reminder_time[0] }}
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">{{ $t("translation.leads.comment") }}</label>
            <textarea
              class="form-control"
              :class="{ 'is-invalid': formErrors.reminder_note }"
              v-model="newReminder.reminder_note"
              rows="3"
              :placeholder="$t('translation.leads.reminder_note_placeholder')"
            ></textarea>
            <div v-if="formErrors.reminder_note" class="invalid-feedback">
              {{ formErrors.reminder_note[0] }}
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success btn-sm">
              <i class="ri-save-line me-1"></i>
              {{ $t("translation.btn.save") }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-3">
      <div class="spinner-border spinner-border-sm" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <!-- Reminders List -->
    <div v-else-if="reminders.length > 0">
      <div class="list-group">
        <div v-for="reminder in reminders" :key="reminder.id" class="list-group-item">
          <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center mb-2">
                <i class="ri-calendar-line me-2 text-primary"></i>
                <strong>{{ formatDate(reminder.reminder_date) }}</strong>
                <span class="mx-2">•</span>
                <i class="ri-time-line me-1 text-primary"></i>
                <strong>{{ formatTime(reminder.reminder_time) }}</strong>
                <span
                  class="badge ms-2"
                  :class="getStatusBadgeClass(reminder.reminder_status)"
                >
                  {{ $t(`translation.leads.${reminder.reminder_status}`) }}
                </span>
              </div>
              <p v-if="reminder.reminder_note" class="mb-2 text-muted">
                {{ reminder.reminder_note }}
              </p>
              <small class="text-muted">
                <i class="ri-user-line me-1"></i>
                {{ reminder.causer?.name || "N/A" }} •
                {{ formatDate(reminder.created_at) }}
              </small>
            </div>
            <div class="ms-2 d-flex gap-1">
              <button
                v-if="reminder.reminder_status === 'pending'"
                class="btn btn-sm btn-outline-success"
                @click="updateReminderStatus(reminder.id, 'completed')"
                :title="$t('translation.leads.mark_completed')"
              >
                <i class="ri-check-line"></i>
              </button>
              <button
                class="btn btn-sm btn-outline-danger"
                @click="deleteReminder(reminder.id)"
                :title="$t('translation.leads.delete_reminder')"
              >
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-4 text-muted">
      <i class="ri-calendar-line fs-1 mb-2 d-block"></i>
      <p>{{ $t("translation.leads.no_reminders") }}</p>
    </div>
  </div>
</template>

<style scoped>
.reminders-container {
  min-height: 200px;
}

.list-group-item {
  border-left: 4px solid #e9ecef;
  transition: border-color 0.2s;
}

.list-group-item:hover {
  border-left-color: #0d6efd;
}

.badge {
  font-size: 0.75rem;
}
</style>
