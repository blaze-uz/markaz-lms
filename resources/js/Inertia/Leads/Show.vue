<script setup>
import {
  ref,
  computed,
  onMounted,
  onUnmounted,
  getCurrentInstance,
} from "vue";
import axios from "axios";
import LeadComments from "./Components/LeadComments.vue";
import LeadCalls from "./Components/LeadCalls.vue";
import LeadActivities from "./Components/LeadActivities.vue";
import LeadReminders from "./Components/LeadReminders.vue";

const props = defineProps({
  lead: {
    type: Object,
    required: true,
  },
  initialTab: {
    type: String,
    default: "details",
  },
});

const loading = ref(true);
const leadData = ref(null);
const error = ref(null);

const tabs = [
  { key: "details", label: "translation.leads.details" },
  { key: "comments", label: "translation.leads.comments" },
  { key: "calls", label: "translation.leads.calls" },
  { key: "reminders", label: "translation.leads.reminders" },
  { key: "history", label: "translation.leads.history" },
];
const activeTab = ref(props.initialTab);

// Fetch lead details with optimized data loading
const fetchLeadDetails = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/admin/leads/${props.lead.id}/details`, {
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content"),
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });
    leadData.value = response.data;
  } catch (err) {
    error.value = err.message;
    console.error("Error fetching lead details:", err);
  } finally {
    loading.value = false;
  }
};

// Handle ESC key press to close modal
const handleEscKey = (event) => {
  if (event.key === "Escape") {
    emit("close");
  }
};

// Define emit function
const emit = defineEmits(["close", "delete", "edit", "convert"]);

// Handle convert to student
const handleConvertToStudent = () => {
  // Emit convert event to parent and close show modal
  emit("convert", leadData.value);
  emit("close");
};

// Load lead data when component mounts
onMounted(() => {
  fetchLeadDetails();
  // Add ESC key listener
  document.addEventListener("keydown", handleEscKey);
});

// Remove ESC key listener when component unmounts
onUnmounted(() => {
  document.removeEventListener("keydown", handleEscKey);
});

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

// Format chosen days for better display
const formatChosenDays = (chosenDays) => {
  if (!chosenDays) return "N/A";

  const { proxy } = getCurrentInstance();

  // Handle predefined patterns
  if (chosenDays === "odd") {
    return proxy.$t("translation.leads.odd_days"); // Monday, Wednesday, Friday
  }
  if (chosenDays === "even") {
    return proxy.$t("translation.leads.even_days"); // Tuesday, Thursday, Saturday
  }
  if (chosenDays === "daily") {
    return proxy.$t("translation.leads.daily");
  }
  if (chosenDays === "weekend") {
    return proxy.$t("translation.leads.weekend");
  }

  // Handle comma-separated day numbers (1=Monday, 2=Tuesday, etc.)
  if (chosenDays.includes(",")) {
    const dayNumbers = chosenDays.split(",").map((d) => d.trim());
    const dayNames = dayNumbers.map((dayNum) => {
      switch (dayNum) {
        case "1":
          return proxy.$t("translation.leads.monday");
        case "2":
          return proxy.$t("translation.leads.tuesday");
        case "3":
          return proxy.$t("translation.leads.wednesday");
        case "4":
          return proxy.$t("translation.leads.thursday");
        case "5":
          return proxy.$t("translation.leads.friday");
        case "6":
          return proxy.$t("translation.leads.saturday");
        case "7":
          return proxy.$t("translation.leads.sunday");
        default:
          return dayNum;
      }
    });
    return dayNames.join(", ");
  }

  // Handle single day number
  switch (chosenDays) {
    case "1":
      return proxy.$t("translation.leads.monday");
    case "2":
      return proxy.$t("translation.leads.tuesday");
    case "3":
      return proxy.$t("translation.leads.wednesday");
    case "4":
      return proxy.$t("translation.leads.thursday");
    case "5":
      return proxy.$t("translation.leads.friday");
    case "6":
      return proxy.$t("translation.leads.saturday");
    case "7":
      return proxy.$t("translation.leads.sunday");
    default:
      return chosenDays; // Return as-is if no pattern matches
  }
};

const dynamicFieldDefinitions = computed(
  () => leadData.value?.dynamic_field_definitions ?? []
);

const rawCustomAttribute = (key) => {
  const attrs = leadData.value?.custom_attributes;
  if (!attrs || typeof attrs !== "object") {
    return null;
  }
  return Object.prototype.hasOwnProperty.call(attrs, key) ? attrs[key] : null;
};

const isEmptyDynamicValue = (val) => val === null || val === undefined || val === "";

const formatDynamicFieldValue = (field) => {
  const val = rawCustomAttribute(field.key);
  if (isEmptyDynamicValue(val)) {
    return "N/A";
  }
  return String(val);
};
</script>

<template>
  <div>
    <div
      class="modal fade show"
      tabindex="-1"
      style="display: block"
      @click.self="$emit('close')"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
            <div>
              <template v-if="loading">
                <span class="skeleton-avatar me-2"></span>
                <span class="skeleton-title align-middle"></span>
              </template>
              <template v-else>
                <span class="avatar-circle me-2">{{
                  leadData?.name?.charAt(0) || ""
                }}</span>
                <span class="modal-title">{{ leadData?.name || "" }}</span>
                <span class="badge bg-primary ms-2">ID: #{{ leadData?.id || "" }}</span>
                <span class="badge bg-info ms-2">{{
                  leadData?.collection?.name || ""
                }}</span>
              </template>
            </div>
            <button class="btn btn-sm btn-outline-primary" @click="$emit('close')">
              <i class="ri-close-line"></i>
            </button>
          </div>
          <!-- Tabs (always real) -->
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item" v-for="tab in tabs" :key="tab.key">
              <a
                class="nav-link"
                :class="{ active: activeTab === tab.key }"
                href="#"
                @click.prevent="activeTab = tab.key"
              >
                {{ $t(tab.label) }}
              </a>
            </li>
          </ul>
          <!-- Modal Body: skeleton only in content area -->
          <div class="modal-body">
            <div v-if="loading">
              <div class="row g-4">
                <div class="col-md-6" v-for="i in 7" :key="i">
                  <div class="detail-label mb-2" :class="{ 'col-md-12': i === 7 }">
                    <i class="ri-phone-line me-2" v-if="i === 1"></i>
                    <i class="ri-user-3-line me-2" v-else-if="i === 2"></i>
                    <i class="ri-book-line me-2" v-else-if="i === 3"></i>
                    <i class="ri-user-line me-2" v-else-if="i === 4"></i>
                    <i class="ri-dashboard-line me-2" v-else-if="i === 5"></i>
                    <i class="ri-calendar-2-line me-2" v-else-if="i === 6"></i>
                    <i class="ri-calendar-line me-2" v-else-if="i === 7"></i>
                    <span class="skeleton-label-text"></span>
                  </div>
                  <div class="detail-value">
                    <span class="skeleton-value-text"></span>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="detail-label mb-2">
                    <i class="ri-chat-1-line me-2"></i>
                    <span class="skeleton-label-text"></span>
                  </div>
                  <div class="detail-value">
                    <span class="skeleton-value-text"></span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else-if="error" class="alert alert-danger m-3">
              {{ error }}
            </div>
            <div v-else-if="leadData">
              <div v-if="activeTab === 'details'" class="details-compact">
                <div class="details-grid">
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="ri-phone-line me-1"></i
                      >{{ $t("translation.leads.phone") }}
                    </div>
                    <div class="detail-value">
                      <a v-if="leadData.phone" :href="'tel:' + leadData.phone">{{
                        leadData.phone
                      }}</a>
                      <br v-if="leadData.phone && leadData.phone2" />
                      <a v-if="leadData.phone2" :href="'tel:' + leadData.phone2">{{
                        leadData.phone2
                      }}</a>
                      <span v-if="!leadData.phone && !leadData.phone2" class="text-muted">N/A</span>
                    </div>
                  </div>
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="ri-map-pin-line me-1"></i
                      >{{ $t("translation.leads.address") }}
                    </div>
                    <div class="detail-value" :class="{ 'text-muted': !leadData.address }">
                      {{ leadData.address || "N/A" }}
                    </div>
                  </div>
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="ri-user-3-line me-1"></i
                      >{{ $t("translation.leads.creator") }}
                    </div>
                    <div
                      class="detail-value"
                      :class="{ 'text-muted': !leadData.causer?.name }"
                    >
                      {{ leadData.causer?.name || "N/A" }}
                    </div>
                  </div>
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="ri-book-line me-1"></i
                      >{{ $t("translation.leads.course") }}
                    </div>
                    <div
                      class="detail-value"
                      :class="{ 'text-muted': !leadData.course?.name }"
                    >
                      {{ leadData.course?.name || "N/A" }}
                    </div>
                  </div>
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="ri-user-line me-1"></i
                      >{{ $t("translation.leads.teacher") }}
                    </div>
                    <div
                      class="detail-value"
                      :class="{ 'text-muted': !leadData.teacher?.name }"
                    >
                      {{ leadData.teacher?.name || "N/A" }}
                    </div>
                  </div>
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="ri-dashboard-line me-1"></i
                      >{{ $t("translation.leads.board") }}
                    </div>
                    <div
                      class="detail-value"
                      :class="{ 'text-muted': !leadData.board?.name }"
                    >
                      {{ leadData.board?.name || "N/A" }}
                    </div>
                  </div>
                  <div class="detail-item detail-schedule">
                    <div class="detail-label">
                      <i class="ri-calendar-2-line me-1"></i
                      >{{ $t("translation.leads.chosen_days") }} &
                      {{ $t("translation.leads.chosen_time") }}
                    </div>
                    <div class="detail-value schedule-value">
                      <span
                        :class="{
                          'text-muted': formatChosenDays(leadData.chosen_days) === 'N/A',
                        }"
                      >
                        {{ formatChosenDays(leadData.chosen_days) }}
                      </span>
                      <span class="mx-2">•</span>
                      <span :class="{ 'text-muted': !leadData.chosen_time }">
                        {{ leadData.chosen_time || "N/A" }}
                      </span>
                    </div>
                  </div>
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="ri-calendar-line me-1"></i
                      >{{ $t("translation.leads.created") }}
                    </div>
                    <div class="detail-value">
                      {{ formatDate(leadData.created_at) }}
                    </div>
                  </div>
                  <div
                    v-for="field in dynamicFieldDefinitions"
                    :key="field.key"
                    class="detail-item"
                  >
                    <div class="detail-label">
                      <i class="ri-list-settings-line me-1"></i>{{ field.label }}
                    </div>
                    <div
                      class="detail-value"
                      :class="{
                        'text-muted': isEmptyDynamicValue(rawCustomAttribute(field.key)),
                        'detail-value-textarea': field.type === 'textarea',
                      }"
                    >
                      {{ formatDynamicFieldValue(field) }}
                    </div>
                  </div>
                </div>
                <div class="detail-item detail-comments">
                  <div class="detail-label">
                    <i class="ri-chat-1-line me-1"></i
                    >{{ $t("translation.leads.comments") }}
                  </div>
                  <div class="detail-value" :class="{ 'text-muted': !leadData.comment }">
                    <span>{{
                      leadData.comment || $t("translation.leads.no_notes_available")
                    }}</span>
                  </div>
                </div>
              </div>
              <div v-else-if="activeTab === 'comments'">
                <LeadComments :lead-id="leadData?.id" />
              </div>
              <div v-else-if="activeTab === 'calls'">
                <LeadCalls :lead-id="leadData?.id" />
              </div>
              <div v-else-if="activeTab === 'reminders'">
                <LeadReminders :lead-id="leadData?.id" />
              </div>
              <div v-else-if="activeTab === 'history'">
                <LeadActivities :lead-id="leadData?.id" />
              </div>
            </div>
          </div>
          <!-- Modal Footer (always real) -->
          <div class="modal-footer">
            <button class="btn btn-outline-danger" @click="$emit('delete', leadData)">
              <i class="ri-delete-bin-line me-1"></i> {{ $t("translation.leads.delete") }}
            </button>
            <button class="btn btn-outline-success" @click="handleConvertToStudent">
              <i class="ri-user-add-line me-1"></i>
              {{ $t("translation.leads.convert_to_student") }}
            </button>
            <button class="btn btn-primary" @click="$emit('edit', leadData)">
              <i class="ri-edit-line me-1"></i> {{ $t("translation.leads.edit") }}
            </button>
            <button class="btn btn-light ms-auto" @click="$emit('close')">
              {{ $t("translation.leads.close") }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  </div>
</template>

<style scoped>
.modal {
  display: flex !important;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 0 !important;
}
.modal-dialog {
  margin: 0 auto;
  width: 900px;
  min-width: 900px;
  max-width: 900px;
  height: 70vh;
  min-height: 70vh;
  max-height: 70vh;
  display: flex;
  align-items: center;
}

.modal-content {
  height: 100%;
  min-height: 100%;
  width: 100%;
  min-width: 100%;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  border-radius: 12px;
  box-shadow: 0 4px 32px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  padding: 0;
}

.modal-body {
  flex: 1 1 auto;
  overflow: auto;
  padding: 1.5rem;
}
.avatar-circle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #e3e6f0;
  font-weight: bold;
  font-size: 1.2rem;
  color: #4f5d73;
  margin-bottom: 20px;
}
.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
}
.nav-tabs {
  padding-left: 1.5rem;
  padding-right: 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}
/* Compact details styling */
.details-compact {
  padding: 0;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem 1rem;
  margin-bottom: 1rem;
}

.detail-item {
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.detail-comments {
  grid-column: 1 / -1;
  margin-bottom: 0;
}

.detail-label {
  font-size: 0.8rem;
  color: #6c757d;
  margin-bottom: 0.2rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.detail-value {
  font-size: 0.9rem;
  color: #212529;
  margin-bottom: 0;
  font-weight: 500;
}

.detail-value a {
  color: #0d6efd;
  text-decoration: none;
}

.detail-value a:hover {
  text-decoration: underline;
}

.detail-value-textarea {
  white-space: pre-wrap;
  word-break: break-word;
}

.detail-schedule .schedule-value {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
}

/* Responsive adjustments for compact view */
@media (max-width: 768px) {
  .details-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }
}

@media (max-width: 576px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
}
.modal-footer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1rem 1rem 1rem !important;
  border-top: 1px solid #f0f0f0;
  background: #fafbfc;
}
.skeleton-loader {
  animation: none;
}
.skeleton-header {
  display: flex;
  align-items: center;
  margin-bottom: 2rem;
}
.skeleton-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
}
.skeleton-title {
  width: 180px;
  height: 24px;
  border-radius: 6px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
  margin-bottom: 8px;
  display: inline-block;
}
.skeleton-badge {
  width: 80px;
  height: 18px;
  border-radius: 8px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
  margin-bottom: 4px;
  display: inline-block;
  margin-right: 8px;
}
.skeleton-label {
  width: 90px;
  height: 14px;
  border-radius: 4px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
}
.skeleton-value {
  width: 100%;
  height: 18px;
  border-radius: 4px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
}
@keyframes skeleton-loading {
  0% {
    background-position: -200px 0;
  }
  100% {
    background-position: calc(200px + 100%) 0;
  }
}

.skeleton-close-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
  display: inline-block;
}
.skeleton-tab {
  display: inline-block;
  width: 110px;
  height: 32px;
  border-radius: 8px 8px 0 0;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
  margin-right: 8px;
  margin-bottom: -1px;
}
.skeleton-tab-text {
  width: 90px;
  height: 18px;
  border-radius: 6px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
  display: inline-block;
}
.skeleton-label-text {
  width: 70px;
  height: 14px;
  border-radius: 4px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
  display: inline-block;
}
.skeleton-value-text {
  width: 100%;
  height: 18px;
  border-radius: 4px;
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  animation: skeleton-loading 1.2s infinite linear;
  display: inline-block;
}

/* Mobile responsive styles */
@media (max-width: 991.98px) {
  .modal-dialog {
    width: 95%;
    min-width: 95%;
    max-width: 95%;
    height: 85vh;
    min-height: 85vh;
    max-height: 85vh;
    margin: 0 auto;
  }
}

@media (max-width: 767.98px) {
  .modal {
    padding: 0.5rem !important;
  }

  .modal-dialog {
    width: 100%;
    min-width: 100%;
    max-width: 100%;
    height: 95vh;
    min-height: 95vh;
    max-height: 95vh;
    margin: 0;
  }

  .modal-content {
    border-radius: 8px;
  }

  .modal-header {
    padding: 1rem;
    flex-wrap: wrap;
  }

  .modal-header > div {
    flex: 1;
    min-width: 0;
  }

  .modal-title {
    font-size: 1.1rem;
    word-break: break-word;
  }

  .badge {
    font-size: 0.75rem;
    margin: 0.25rem 0.25rem 0.25rem 0;
  }

  .nav-tabs {
    padding-left: 1rem;
    padding-right: 1rem;
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
  }

  .nav-tabs .nav-item {
    flex-shrink: 0;
  }

  .nav-tabs .nav-link {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
  }

  .modal-body {
    padding: 1rem;
  }

  .details-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .detail-item {
    padding: 0.75rem;
  }

  .detail-label {
    font-size: 0.75rem;
    margin-bottom: 0.5rem;
  }

  .detail-value {
    font-size: 0.85rem;
    line-height: 1.4;
  }

  .modal-footer {
    padding: 1rem !important;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .modal-footer .btn {
    flex: 1;
    min-width: calc(50% - 0.25rem);
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
  }

  .modal-footer .btn.ms-auto {
    margin-left: 0 !important;
    flex: 1 1 100%;
    margin-top: 0.5rem;
  }
}

@media (max-width: 575.98px) {
  .modal {
    padding: 0 !important;
  }

  .modal-dialog {
    height: 100vh;
    min-height: 100vh;
    max-height: 100vh;
  }

  .modal-content {
    border-radius: 0;
    height: 100vh;
  }

  .modal-header {
    padding: 0.75rem;
  }

  .modal-title {
    font-size: 1rem;
  }

  .avatar-circle {
    width: 32px;
    height: 32px;
    font-size: 1rem;
  }

  .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.4rem;
  }

  .nav-tabs .nav-link {
    font-size: 0.8rem;
    padding: 0.4rem 0.6rem;
  }

  .modal-body {
    padding: 0.75rem;
  }

  .detail-item {
    padding: 0.6rem;
  }

  .detail-label {
    font-size: 0.7rem;
  }

  .detail-value {
    font-size: 0.8rem;
  }

  .modal-footer {
    padding: 0.75rem !important;
  }

  .modal-footer .btn {
    font-size: 0.8rem;
    padding: 0.45rem 0.6rem;
  }
}
</style>
