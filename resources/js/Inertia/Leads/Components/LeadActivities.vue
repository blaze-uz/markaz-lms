<script setup>
import { ref, onMounted, getCurrentInstance } from "vue";
import axios from "axios";

const { proxy } = getCurrentInstance();

const props = defineProps({
  leadId: {
    type: Number,
    required: true,
  },
});

// Activities functionality
const activities = ref([]);
const activitiesLoading = ref(false);
const collections = ref({});

// Fetch activities for the lead
const fetchActivities = async () => {
  try {
    activitiesLoading.value = true;
    const response = await axios.get(`/admin/leads/${props.leadId}/activities`);
    activities.value = response.data.activities || [];
    collections.value = response.data.collections || {};
  } catch (err) {
    console.error("Error fetching activities:", err);
  } finally {
    activitiesLoading.value = false;
  }
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

// Get activity icon based on log name and event
const getActivityIcon = (activity) => {
  if (activity.log_name === "lead_call") {
    if (activity.event === "created") return "ri-phone-line";
    if (activity.event === "deleted") return "ri-phone-off-line";
    return "ri-phone-line";
  }

  if (activity.log_name === "lead_comment") {
    if (activity.event === "created") return "ri-chat-new-line";
    if (activity.event === "deleted") return "ri-chat-delete-line";
    return "ri-chat-1-line";
  }

  if (activity.log_name === "lead") {
    if (activity.event === "created") return "ri-user-add-line";
    if (activity.event === "updated") {
      const attributes = activity.properties?.attributes || {};
      const changes = Object.keys(attributes);

      // Use move icon for position/collection changes
      if (changes.includes("collection_id") || changes.includes("order")) {
        return "ri-drag-move-line";
      }

      return "ri-edit-line";
    }
    if (activity.event === "deleted") return "ri-user-unfollow-line";
    return "ri-user-line";
  }

  return "ri-information-line";
};

// Get activity color based on log name and event
const getActivityColor = (activity) => {
  if (activity.log_name === "lead_call") {
    return "primary";
  }

  if (activity.log_name === "lead_comment") {
    return "info";
  }

  if (activity.log_name === "lead") {
    if (activity.event === "created") return "success";
    if (activity.event === "updated") {
      const attributes = activity.properties?.attributes || {};
      const changes = Object.keys(attributes);

      // Use purple/primary color for position/collection changes
      if (changes.includes("collection_id") || changes.includes("order")) {
        return "primary";
      }

      return "warning";
    }
    if (activity.event === "deleted") return "danger";
    return "secondary";
  }

  return "secondary";
};

// Get activity description
const getActivityDescription = (activity) => {
  const causer = activity.causer?.name || "Unknown User";

  if (activity.log_name === "lead_call") {
    if (activity.event === "created") {
      const status = activity.properties?.attributes?.status || "unknown";
      let statusText;
      try {
        statusText = proxy.$t(`translation.leads.${status}`);
      } catch (e) {
        statusText = status; // fallback to raw status if translation not found
      }
      return `${causer} ${proxy.$t("translation.leads.made_call")} (${statusText})`;
    }
    if (activity.event === "deleted")
      return `${causer} ${proxy.$t("translation.leads.deleted_call")}`;
    return `${causer} ${activity.event} ${proxy.$t("translation.leads.call")}`;
  }

  if (activity.log_name === "lead_comment") {
    if (activity.event === "created")
      return `${causer} ${proxy.$t("translation.leads.added_comment")}`;
    if (activity.event === "deleted")
      return `${causer} ${proxy.$t("translation.leads.deleted_comment")}`;
    return `${causer} ${activity.event} ${proxy.$t("translation.leads.comment")}`;
  }

  if (activity.log_name === "lead") {
    if (activity.event === "created")
      return `${causer} ${proxy.$t("translation.leads.created_lead")}`;
    if (activity.event === "updated") {
      const attributes = activity.properties?.attributes || {};
      const oldAttributes = activity.properties?.old || {};
      const changes = Object.keys(attributes);

      if (changes.length > 0) {
        // Handle position/collection changes specifically
        if (changes.includes("collection_id") || changes.includes("order")) {
          let description = `${causer} ${proxy.$t("translation.leads.moved_lead")}`;

          // Add collection change info if available
          if (changes.includes("collection_id")) {
            const newCollectionId = attributes.collection_id;
            const oldCollectionId = oldAttributes.collection_id;
            if (newCollectionId !== oldCollectionId) {
              const fromCollection =
                collections.value[oldCollectionId] || `ID: ${oldCollectionId}`;
              const toCollection =
                collections.value[newCollectionId] || `ID: ${newCollectionId}`;
              description += ` ${proxy.$t(
                "translation.leads.from"
              )} "${fromCollection}" ${proxy.$t(
                "translation.leads.to"
              )} "${toCollection}"`;
            }
          }

          // Add position change info if available
          if (changes.includes("order")) {
            const newOrder = attributes.order;
            const oldOrder = oldAttributes.order;
            if (newOrder !== oldOrder && !changes.includes("collection_id")) {
              description += ` (${proxy.$t(
                "translation.leads.position_changed"
              )} ${oldOrder} → ${newOrder})`;
            }
          }

          return description;
        }

        // Handle other field changes
        const fieldNames = changes.filter(
          (field) => !["collection_id", "order"].includes(field)
        );
        if (fieldNames.length > 0) {
          return `${causer} ${proxy.$t(
            "translation.leads.updated_fields"
          )}: ${fieldNames.join(", ")}`;
        }
      }
      return `${causer} ${proxy.$t("translation.leads.updated_lead")}`;
    }
    if (activity.event === "deleted")
      return `${causer} ${proxy.$t("translation.leads.deleted_lead")}`;
    return `${causer} ${activity.event} ${proxy.$t("translation.leads.lead")}`;
  }

  return (
    activity.description || `${causer} ${proxy.$t("translation.leads.performed_action")}`
  );
};

// Load activities when component mounts
onMounted(() => {
  fetchActivities();
});

// Expose fetchActivities for parent component
defineExpose({
  fetchActivities,
});
</script>

<template>
  <div>
    <!-- Activities list -->
    <div v-if="activitiesLoading" class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{
          $t("translation.leads.loading_activities")
        }}</span>
      </div>
      <p class="mt-2 text-muted">
        {{ $t("translation.leads.loading_activities") }}
      </p>
    </div>

    <div v-else-if="activities.length === 0" class="text-center text-muted py-4">
      <i class="ri-history-line fs-1 mb-2 d-block"></i>
      <p>{{ $t("translation.leads.no_activities") }}</p>
    </div>

    <div v-else class="activities-list">
      <div
        v-for="activity in activities"
        :key="activity.id"
        class="activity-item d-flex mb-3"
      >
        <div class="activity-icon me-3">
          <div
            :class="`badge bg-${getActivityColor(
              activity
            )}-subtle text-${getActivityColor(activity)} rounded-circle p-2`"
          >
            <i :class="getActivityIcon(activity)"></i>
          </div>
        </div>
        <div class="activity-content flex-grow-1">
          <div class="activity-description mb-1">
            {{ getActivityDescription(activity) }}
          </div>
          <small class="text-muted">
            {{ formatDate(activity.created_at) }}
          </small>
          <div v-if="activity.properties?.attributes" class="activity-details mt-2">
            <div
              v-for="(value, key) in activity.properties.attributes"
              :key="key"
              class="small text-muted"
            >
              <strong>{{ key }}:</strong> {{ value }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.activities-list {
  padding: 0.25rem;
  margin: -0.25rem;
}

/* Custom scrollbar for webkit browsers */
.activities-list::-webkit-scrollbar {
  width: 6px;
}

.activities-list::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.activities-list::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
  transition: background 0.2s ease;
}

.activities-list::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.activity-item {
  transition: all 0.2s ease;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #f0f0f0;
  background-color: #fafafa;
}

.activity-item:hover {
  background-color: #f5f5f5;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.activity-icon {
  flex-shrink: 0;
}

.activity-description {
  font-weight: 500;
  color: #374151;
}

.activity-details {
  background-color: #f8f9fa;
  padding: 0.5rem;
  border-radius: 4px;
  border-left: 3px solid #e9ecef;
}
</style>
