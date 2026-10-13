<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
  leadId: {
    type: Number,
    required: true,
  },
});

// Calls functionality
const calls = ref([]);
const callsLoading = ref(false);
const newCall = ref({
  status: "",
  comment: "",
  duration: "",
});
const addingCall = ref(false);
const showAddForm = ref(false);
const page = usePage();

// Call status options
const callStatuses = [
  {
    value: "answered",
    label: "translation.leads.answered",
    color: "success",
    icon: "ri-phone-line",
  },
  {
    value: "not_answered",
    label: "translation.leads.not_answered",
    color: "danger",
    icon: "ri-close-circle-line me-1",
  },
];

// Fetch calls for the lead
const fetchCalls = async () => {
  try {
    callsLoading.value = true;
    const response = await axios.get(`/admin/leads/${props.leadId}/calls`);
    calls.value = response.data.calls || [];
  } catch (err) {
    console.error("Error fetching calls:", err);
  } finally {
    callsLoading.value = false;
  }
};

// Add new call
const addCall = async () => {
  if (!newCall.value.status) return;

  try {
    addingCall.value = true;
    const response = await axios.post(`/admin/leads/${props.leadId}/calls`, {
      status: newCall.value.status,
      comment: newCall.value.comment.trim() || null,
      duration: newCall.value.duration || null,
    });

    if (response.data.success) {
      // Add new call to the beginning of the list
      calls.value.unshift(response.data.call);
      newCall.value = { status: "", comment: "", duration: "" };
      showAddForm.value = false;
    }
  } catch (err) {
    console.error("Error adding call:", err);
  } finally {
    addingCall.value = false;
  }
};

// Check if user can delete call (author or admin)
const canDeleteCall = (call) => {
  const currentUser = page.props.auth?.user || page.props.user;
  if (!currentUser) return false;

  // Admin can delete any call - check roles array
  const isAdmin = currentUser.roles?.some((role) => role.name === "Admin");
  if (isAdmin) return true;

  // User can delete their own call
  return call.causer_id === currentUser.id;
};

// Delete call directly
const deleteCall = async (callId) => {
  try {
    const response = await axios.delete(`/admin/leads/${props.leadId}/calls/${callId}`);

    if (response.data.success) {
      // Remove call from the list
      calls.value = calls.value.filter((call) => call.id !== callId);
    }
  } catch (err) {
    console.error("Error deleting call:", err);
  }
};

// Get status color class
const getStatusColor = (status) => {
  const statusObj = callStatuses.find((s) => s.value === status);
  return statusObj ? statusObj.color : "secondary";
};

// Format date as dd.mm.yyyy h:i
const formatDate = (dateString) => {
  if (!dateString) return "";

  const date = new Date(dateString);

  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");

  return `${day}.${month}.${year} ${hours}:${minutes}`;
};

const formatDuration = (call) => {
  if (call.formatted_duration) return call.formatted_duration;
  if (!call.duration) return "";

  const duration = Number(call.duration);
  const hours = Math.floor(duration / 3600);
  const minutes = Math.floor((duration % 3600) / 60);
  const seconds = duration % 60;

  if (hours > 0) {
    return `${hours}h ${String(minutes).padStart(2, "0")}m ${String(seconds).padStart(
      2,
      "0"
    )}s`;
  }

  return `${minutes}m ${String(seconds).padStart(2, "0")}s`;
};

// Load calls when component mounts
onMounted(() => {
  fetchCalls();
});

// Expose fetchCalls for parent component
defineExpose({
  fetchCalls,
});
</script>

<template>
  <div>
    <!-- Add Call Button -->
    <div class="mb-3 d-flex justify-content-end">
      <button
        v-if="!showAddForm"
        @click="showAddForm = true"
        class="btn btn-outline-primary"
      >
        <i class="ri-add-line me-1"></i>
        {{ $t("translation.leads.add_call") }}
      </button>
    </div>

    <!-- Compact Add Call Form -->
    <div v-if="showAddForm" class="add-call-form mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-3 pt-0">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ $t("translation.leads.add_call") }}</h6>
          </div>

          <!-- Compact Status Buttons -->
          <div class="mb-3">
            <div class="btn-group w-100" role="group">
              <button
                v-for="status in callStatuses"
                :key="status.value"
                type="button"
                class="btn"
                :class="{
                  [`btn-${status.color}`]: newCall.status === status.value,
                  'btn-outline-primary': newCall.status !== status.value,
                }"
                :disabled="addingCall"
                @click="newCall.status = status.value"
              >
                <i :class="status.icon" class="me-1"></i>
                {{ $t(status.label) }}
              </button>
            </div>
          </div>

          <!-- Comment Input -->
          <div class="mb-3">
            <textarea
              v-model="newCall.comment"
              class="form-control"
              rows="2"
              :placeholder="$t('translation.leads.call_comment_placeholder')"
              :disabled="addingCall"
            ></textarea>
          </div>

          <!-- Duration Input -->
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text"><i class="ri-time-line"></i></span>
              <input
                v-model="newCall.duration"
                type="number"
                class="form-control"
                min="0"
                :placeholder="$t('translation.leads.call_duration_placeholder')"
                :disabled="addingCall"
              />
              <span class="input-group-text">{{ $t("translation.leads.seconds") }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex gap-2">
            <button
              @click="addCall"
              class="btn btn-primary flex-fill"
              :disabled="!newCall.status || addingCall"
            >
              <div
                v-if="addingCall"
                class="spinner-border spinner-border-sm me-1"
                role="status"
              >
                <span class="visually-hidden">
                  {{ $t("translation.leads.saving") }}
                </span>
              </div>
              <i v-else class="ri-save-line me-1"></i>
              {{ $t("translation.leads.save") }}
            </button>
            <button
              @click="
                showAddForm = false;
                newCall = { status: '', comment: '', duration: '' };
              "
              class="btn btn-outline-secondary"
              :disabled="addingCall"
            >
              {{ $t("translation.leads.cancel") }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Calls list -->
    <div v-if="callsLoading" class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t("translation.leads.loading_calls") }}</span>
      </div>
      <p class="mt-2 text-muted">
        {{ $t("translation.leads.loading_calls") }}
      </p>
    </div>

    <div v-else-if="calls.length === 0" class="text-center text-muted py-4">
      <i class="ri-phone-line fs-1 mb-2 d-block"></i>
      <p>{{ $t("translation.leads.no_calls") }}</p>
    </div>

    <div v-else class="calls-list">
      <div v-for="call in calls" :key="call.id" class="call-item p-3 rounded">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-2">
              {{ call.causer?.name?.charAt(0) || "U" }}
            </div>
            <div>
              <h6 class="mb-0">{{ call.causer?.name || "Unknown User" }}</h6>
              <small class="text-muted">{{ formatDate(call.created_at) }}</small>
              <small v-if="formatDuration(call)" class="text-muted d-block">
                {{ $t("translation.leads.talk_time") }}: {{ formatDuration(call) }}
              </small>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span
              :class="`badge bg-${getStatusColor(
                call.status
              )}-subtle text-${getStatusColor(call.status)}`"
            >
              {{ $t(`translation.leads.${call.status}`) }}
            </span>
            <button
              v-if="canDeleteCall(call)"
              @click="deleteCall(call.id)"
              class="btn btn-outline-danger btn-sm"
              :title="$t('translation.leads.delete_call')"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
        </div>
        <p v-if="call.comment" class="mb-0 text-muted">{{ call.comment }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Calls section styles  */
/* .calls-list {
  max-height: 350px;
  overflow-y: auto;
  overflow-x: hidden;
  scroll-behavior: smooth;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
  padding: 0.25rem;
  margin: -0.25rem;
} */

/* Custom scrollbar for webkit browsers */
.calls-list::-webkit-scrollbar {
  width: 6px;
}

.calls-list::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.calls-list::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
  transition: background 0.2s ease;
}

.calls-list::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.call-item {
  transition: all 0.2s ease;
  background-color: #f8f9fa;
  border: 1px solid #e9ecef !important;
  margin-bottom: 0.75rem;
}

.call-item:hover {
  background-color: #e9ecef;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-color: #dee2e6 !important;
}

.call-item:last-child {
  margin-bottom: 0;
}

.avatar-circle {
  width: 32px;
  height: 32px;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #e3e6f0;
  font-weight: bold;
  color: #4f5d73;
}

/* Add call form styles */
.add-call-form {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.add-call-form .card {
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.add-call-form .btn-group .btn {
  font-weight: 500;
  transition: all 0.2s ease;
}

.add-call-form .btn-group .btn:hover {
  transform: translateY(-1px);
}

.add-call-form .btn-group .btn.btn-success {
  background-color: #198754;
  border-color: #198754;
  color: white;
}

.add-call-form .btn-group .btn.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
}

.add-call-form .form-control-sm {
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

.add-call-form .form-control-sm:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
