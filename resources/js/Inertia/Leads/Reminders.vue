<script setup>
import { ref, onMounted, computed } from "vue";
import LeadsNavTabs from "./Components/LeadsNavTabs.vue";
import axios from "axios";

const loading = ref(true);
const reminders = ref([]);
const error = ref(null);
const activeFilter = ref('all');
const statistics = ref({
  total: 0,
  pending: 0,
  completed: 0,
  overdue: 0,
});

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
    calculateStatistics();
  } catch (err) {
    error.value = err.message;
    console.error("Error fetching user reminders:", err);
  } finally {
    loading.value = false;
  }
};

// Calculate statistics
const calculateStatistics = () => {
  const stats = {
    total: reminders.value.length,
    pending: 0,
    completed: 0,
    overdue: 0,
  };

  reminders.value.forEach((reminder) => {
    if (reminder.reminder_status === "completed") {
      stats.completed++;
    } else {
      stats.pending++;
      if (
        isOverdue(
          reminder.reminder_date,
          reminder.reminder_time,
          reminder.reminder_status
        )
      ) {
        stats.overdue++;
      }
    }
  });

  statistics.value = stats;
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
  if (!confirm("Are you sure you want to delete this reminder?")) {
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

// Filter reminders based on active filter
const filteredReminders = computed(() => {
  if (activeFilter.value === 'all') {
    return reminders.value;
  }
  
  return reminders.value.filter(reminder => {
    switch (activeFilter.value) {
      case 'pending':
        return reminder.reminder_status === 'pending' && !isOverdue(reminder.reminder_date, reminder.reminder_time, reminder.reminder_status);
      case 'completed':
        return reminder.reminder_status === 'completed';
      case 'overdue':
        return reminder.reminder_status === 'pending' && isOverdue(reminder.reminder_date, reminder.reminder_time, reminder.reminder_status);
      default:
        return true;
    }
  });
});

// Set active filter
const setFilter = (filter) => {
  activeFilter.value = filter;
};

// Get filter button class
const getFilterButtonClass = (filter) => {
  return activeFilter.value === filter ? 'btn-primary' : 'btn-light';
};

// Load reminders when component mounts
onMounted(() => {
  fetchUserReminders();
});
</script>

<template>
  <!-- Wrap everything in a container with full height -->
  <div class="kanban-container">
    <!-- Header section with tabs -->
    <div class="header-tabs mb-4 rounded">
      <div class="d-flex justify-content-between align-items-center px-3">
        <!-- Left side tabs -->
        <div class="tab-buttons">
          <LeadsNavTabs active="reminders" />
        </div>

        <!-- Right side filters and refresh -->
        <div class="tab-actions d-flex align-items-center">
          <!-- Filter buttons -->
          <div class="btn-group me-3">
            <button
              type="button"
              class="btn btn-sm"
              :class="getFilterButtonClass('all')"
              @click="setFilter('all')"
            >
              {{ $t("translation.leads.all") }} ({{ statistics.total }})
            </button>
            <button
              type="button"
              class="btn btn-sm"
              :class="getFilterButtonClass('pending')"
              @click="setFilter('pending')"
            >
              {{ $t("translation.leads.pending") }} ({{ statistics.pending - statistics.overdue }})
            </button>
            <button
              type="button"
              class="btn btn-sm"
              :class="getFilterButtonClass('overdue')"
              @click="setFilter('overdue')"
            >
              {{ $t("translation.leads.overdue") }} ({{ statistics.overdue }})
            </button>
            <button
              type="button"
              class="btn btn-sm"
              :class="getFilterButtonClass('completed')"
              @click="setFilter('completed')"
            >
              {{ $t("translation.leads.completed") }} ({{ statistics.completed }})
            </button>
          </div>
          
          <!-- Refresh button -->
          <button
            class="btn btn-outline-primary"
            @click="fetchUserReminders"
            :disabled="loading"
          >
            <i
              class="ri-refresh-line"
              :class="{ 'spinner-border spinner-border-sm': loading }"
            ></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm rounded-circle bg-primary-subtle">
                  <span
                    class="avatar-title rounded-circle bg-primary-subtle text-primary"
                  >
                    <i class="ri-calendar-event-line fs-16"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-medium text-muted text-truncate fs-13">
                  {{ $t("translation.leads.total_reminders") }}
                </p>
                <h4 class="mb-0">{{ statistics.total }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm rounded-circle bg-warning-subtle">
                  <span
                    class="avatar-title rounded-circle bg-warning-subtle text-warning"
                  >
                    <i class="ri-time-line fs-16"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-medium text-muted text-truncate fs-13">
                  {{ $t("translation.leads.pending") }}
                </p>
                <h4 class="mb-0">{{ statistics.pending }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm rounded-circle bg-success-subtle">
                  <span
                    class="avatar-title rounded-circle bg-success-subtle text-success"
                  >
                    <i class="ri-check-line fs-16"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-medium text-muted text-truncate fs-13">
                  {{ $t("translation.leads.completed") }}
                </p>
                <h4 class="mb-0">{{ statistics.completed }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm rounded-circle bg-danger-subtle">
                  <span class="avatar-title rounded-circle bg-danger-subtle text-danger">
                    <i class="ri-alarm-warning-line fs-16"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-medium text-muted text-truncate fs-13">
                  {{ $t("translation.leads.overdue") }}
                </p>
                <h4 class="mb-0">{{ statistics.overdue }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
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
    <div v-else-if="filteredReminders.length > 0" class="row">
      <div
        v-for="reminder in filteredReminders"
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
      <h4 class="text-muted">
        <template v-if="reminders.length === 0">
          {{ $t("translation.leads.no_reminders") }}
        </template>
        <template v-else>
          {{ $t("translation.leads.no_filtered_reminders") }}
        </template>
      </h4>
      <p class="text-muted">
        <template v-if="reminders.length === 0">
          {{ $t("translation.leads.no_reminders_desc") }}
        </template>
        <template v-else>
          {{ $t("translation.leads.no_filtered_reminders_desc") }}
        </template>
      </p>
    </div>
  </div>
</template>

<style scoped>
.kanban-container {
  display: flex;
  flex-direction: column;
  min-height: calc(100vh - 130px);
  width: 100%;
}

.header-tabs {
  background-color: #fff;
  padding: 13px 0;
}

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

/* Smooth button animations */
.btn {
  transition: all 0.15s ease;
}

.btn:hover {
  transform: translateY(-1px);
}

.btn:active {
  transform: translateY(0);
}

/* Mobile responsive styles */
@media (max-width: 767.98px) {
  .header-tabs .d-flex {
    flex-direction: column !important;
    align-items: flex-start !important;
  }
  
  .tab-buttons {
    margin-bottom: 0.75rem;
    width: 100%;
  }
  
  .tab-buttons .btn-group {
    width: 100%;
  }
  
  .tab-buttons .btn {
    font-size: 0.8rem;
    padding: 0.375rem 0.5rem;
  }
  
  .tab-actions {
    width: 100%;
    flex-direction: column !important;
    align-items: stretch !important;
  }
  
  .btn-group {
    width: 100%;
    margin-bottom: 0.5rem;
    margin-right: 0 !important;
  }
  
  .btn-group .btn {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
  }
  
  .tab-actions .btn-outline-primary {
    width: 100%;
    font-size: 0.9rem;
  }
  
  /* Statistics cards responsive */
  .row .col-lg-3 {
    margin-bottom: 1rem;
  }
  
  .card-body {
    padding: 1rem;
  }
  
  .avatar-sm {
    width: 2rem;
    height: 2rem;
  }
  
  .avatar-title {
    font-size: 0.8rem;
  }
  
  .fs-13 {
    font-size: 0.7rem !important;
  }
  
  h4 {
    font-size: 1.1rem;
  }
  
  /* Reminders grid responsive */
  .col-lg-6,
  .col-xl-4 {
    margin-bottom: 1rem;
  }
  
  .card {
    margin-bottom: 0.75rem;
  }
  
  .card-body {
    padding: 0.75rem;
  }
  
  .card-title {
    font-size: 0.9rem;
  }
  
  .btn-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.4rem;
  }
}

@media (max-width: 575.98px) {
  .tab-buttons .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.4rem;
  }
  
  .tab-buttons .btn span {
    display: none;
  }
  
  .tab-buttons .btn i {
    margin-right: 0 !important;
  }
  
  .btn-group .btn {
    font-size: 0.7rem;
    padding: 0.25rem 0.35rem;
  }
  
  .tab-actions .btn-outline-primary span {
    font-size: 0.8rem;
  }
  
  /* Statistics cards on small mobile */
  .col-lg-3,
  .col-md-6 {
    margin-bottom: 0.75rem;
  }
  
  .card-body {
    padding: 0.75rem;
  }
  
  .avatar-sm {
    width: 1.75rem;
    height: 1.75rem;
  }
  
  .avatar-title {
    font-size: 0.7rem;
  }
  
  .fs-13 {
    font-size: 0.65rem !important;
  }
  
  h4 {
    font-size: 1rem;
  }
  
  /* Single column layout for reminders */
  .col-lg-6,
  .col-xl-4 {
    flex: 0 0 100%;
    max-width: 100%;
  }
  
  .card-body {
    padding: 0.6rem;
  }
  
  .card-title {
    font-size: 0.85rem;
  }
  
  .btn-sm {
    font-size: 0.7rem;
    padding: 0.2rem 0.35rem;
  }
  
  .d-flex.gap-2 {
    gap: 0.25rem !important;
  }
}
</style>
