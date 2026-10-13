<script setup>
import { ref, onMounted, getCurrentInstance } from "vue";
import axios from "axios";

const loading = ref(true);
const reminders = ref([]);
const error = ref(null);

// Get translation helper
const { proxy } = getCurrentInstance();

// Fetch user's reminders
const fetchUserReminders = async () => {
  try {
    loading.value = true;
    const response = await axios.get("/admin/leads/user-reminders", {
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
    console.error("Error fetching user reminders:", err);
  } finally {
    loading.value = false;
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

    await fetchUserReminders();
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

    await fetchUserReminders();
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

// Check if reminder is overdue
const isOverdue = (reminderDate, reminderTime, status) => {
  if (status === "completed") return false;

  const now = new Date();
  const reminderDateTime = new Date(`${reminderDate} ${reminderTime}`);
  return reminderDateTime < now;
};

// Get priority class for reminder
const getPriorityClass = (reminderDate, reminderTime, status) => {
  if (status === "completed") return "";

  const now = new Date();
  const reminderDateTime = new Date(`${reminderDate} ${reminderTime}`);
  const diffInHours = (reminderDateTime - now) / (1000 * 60 * 60);

  if (diffInHours < 0) return "border-danger"; // Overdue
  if (diffInHours < 24) return "border-warning"; // Due within 24 hours
  return "";
};

// Load reminders when component mounts
onMounted(() => {
  fetchUserReminders();
});
</script>

<template>
  <div class="user-reminders-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">{{ $t("translation.leads.my_reminders") }}</h4>
        <p class="text-muted mb-0">{{ $t("translation.leads.my_reminders_desc") }}</p>
      </div>
      <button
        class="btn btn-outline-primary btn-sm"
        @click="fetchUserReminders"
        :disabled="loading"
      >
        <i class="ri-refresh-line me-1"></i>
        {{ $t("translation.btn.refresh") }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="text-muted mt-2">{{ $t("translation.leads.loading_reminders") }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="alert alert-danger">
      <i class="ri-error-warning-line me-2"></i>
      {{ error }}
    </div>

    <!-- Reminders List -->
    <div v-else-if="reminders.length > 0" class="row">
      <div
        v-for="reminder in reminders"
        :key="reminder.id"
        class="col-lg-6 col-xl-4 mb-3"
      >
        <div
          class="card h-100"
          :class="
            getPriorityClass(
              reminder.reminder_date,
              reminder.reminder_time,
              reminder.reminder_status
            )
          "
        >
          <div class="card-body">
            <!-- Lead Info -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="card-title mb-1">{{ reminder.lead?.name || "N/A" }}</h6>
                <small class="text-muted">
                  <i class="ri-phone-line me-1"></i>
                  {{ reminder.lead?.phone || "N/A" }}
                </small>
              </div>
              <span class="badge" :class="getStatusBadgeClass(reminder.reminder_status)">
                {{ $t(`translation.leads.${reminder.reminder_status}`) }}
              </span>
            </div>

            <!-- Reminder Date & Time -->
            <div class="mb-3">
              <div class="d-flex align-items-center mb-1">
                <i class="ri-calendar-line me-2 text-primary"></i>
                <strong>{{ formatDate(reminder.reminder_date) }}</strong>
                <span class="mx-2">•</span>
                <i class="ri-time-line me-1 text-primary"></i>
                <strong>{{ formatTime(reminder.reminder_time) }}</strong>
              </div>
              <div
                v-if="
                  isOverdue(
                    reminder.reminder_date,
                    reminder.reminder_time,
                    reminder.reminder_status
                  )
                "
                class="text-danger small"
              >
                <i class="ri-alarm-warning-line me-1"></i>
                {{ $t("translation.leads.overdue") }}
              </div>
            </div>

            <!-- Reminder Note -->
            <div v-if="reminder.reminder_note" class="mb-3">
              <p class="card-text text-muted small">{{ reminder.reminder_note }}</p>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2">
              <button
                v-if="reminder.reminder_status === 'pending'"
                class="btn btn-sm btn-success flex-fill"
                @click="updateReminderStatus(reminder.id, 'completed')"
              >
                <i class="ri-check-line me-1"></i>
                {{ $t("translation.leads.mark_completed") }}
              </button>
              <button
                class="btn btn-sm btn-outline-danger"
                @click="deleteReminder(reminder.id)"
              >
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>

            <!-- Created info -->
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                {{ $t("translation.leads.created") }}:
                {{ formatDate(reminder.created_at) }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-5">
      <i class="ri-calendar-event-line fs-1 text-muted d-block mb-3"></i>
      <h4 class="text-muted">{{ $t("translation.leads.no_reminders") }}</h4>
      <p class="text-muted">{{ $t("translation.leads.no_reminders_desc") }}</p>
    </div>
  </div>
</template>

<style scoped>
.card {
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.border-danger {
  border-color: #dc3545 !important;
}

.border-warning {
  border-color: #ffc107 !important;
}

.badge {
  font-size: 0.75rem;
}

.btn-sm {
  font-size: 0.8rem;
}
</style>
