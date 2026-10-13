<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
  leadId: {
    type: Number,
    required: true,
  },
});

// Comments functionality
const comments = ref([]);
const commentsLoading = ref(false);
const newComment = ref("");
const addingComment = ref(false);
const page = usePage();

// Fetch comments for the lead
const fetchComments = async () => {
  try {
    commentsLoading.value = true;
    const response = await axios.get(`/admin/leads/${props.leadId}/comments`);
    comments.value = response.data.comments || [];
  } catch (err) {
    console.error("Error fetching comments:", err);
  } finally {
    commentsLoading.value = false;
  }
};

// Add new comment
const addComment = async () => {
  if (!newComment.value.trim()) return;

  try {
    addingComment.value = true;
    const response = await axios.post(`/admin/leads/${props.leadId}/comments`, {
      comment: newComment.value.trim(),
    });

    if (response.data.success) {
      // Add new comment to the beginning of the list
      comments.value.unshift(response.data.comment);
      newComment.value = "";
    }
  } catch (err) {
    console.error("Error adding comment:", err);
  } finally {
    addingComment.value = false;
  }
};

// Check if user can delete comment (author or admin)
const canDeleteComment = (comment) => {
  const currentUser = page.props.auth?.user || page.props.user;
  if (!currentUser) return false;

  // Admin can delete any comment - check roles array
  const isAdmin = currentUser.roles?.some((role) => role.name === "Admin");
  if (isAdmin) return true;

  // User can delete their own comment
  return comment.user_id === currentUser.id;
};

// Delete comment directly
const deleteComment = async (commentId) => {
  try {
    const response = await axios.delete(
      `/admin/leads/${props.leadId}/comments/${commentId}`
    );

    if (response.data.success) {
      // Remove comment from the list
      comments.value = comments.value.filter((comment) => comment.id !== commentId);
    }
  } catch (err) {
    console.error("Error deleting comment:", err);
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

// Load comments when component mounts
onMounted(() => {
  fetchComments();
});

// Expose fetchComments for parent component
defineExpose({
  fetchComments,
});
</script>

<template>
  <div>
    <!-- Add new comment form -->
    <div class="mb-4">
      <div class="d-flex gap-2">
        <textarea
          v-model="newComment"
          class="form-control"
          rows="3"
          :placeholder="$t('translation.leads.add_comment_placeholder')"
          :disabled="addingComment"
        ></textarea>
        <button
          @click="addComment"
          class="btn btn-primary"
          :disabled="!newComment.trim() || addingComment"
          style="min-width: 100px"
        >
          <div
            v-if="addingComment"
            class="spinner-border spinner-border-sm me-1"
            role="status"
          >
            <span class="visually-hidden">
              {{ $t("translation.leads.saving") }}
            </span>
          </div>
          <i v-else class="ri-send-plane-line me-1"></i>
          {{ $t("translation.leads.save") }}
        </button>
      </div>
    </div>

    <!-- Comments list -->
    <div v-if="commentsLoading" class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{
          $t("translation.leads.loading_comments")
        }}</span>
      </div>
      <p class="mt-2 text-muted">
        {{ $t("translation.leads.loading_comments") }}
      </p>
    </div>

    <div v-else-if="comments.length === 0" class="text-center text-muted py-4">
      <i class="ri-chat-1-line fs-1 mb-2 d-block"></i>
      <p>{{ $t("translation.leads.no_comments") }}</p>
    </div>

    <div v-else class="comments-list">
      <div
        v-for="comment in comments"
        :key="comment.id"
        class="comment-item mb-3 p-3 border rounded"
      >
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-2">
              {{ comment.user?.name?.charAt(0) || "U" }}
            </div>
            <div>
              <h6 class="mb-0">{{ comment.user?.name || "Unknown User" }}</h6>
              <small class="text-muted">{{ formatDate(comment.created_at) }}</small>
            </div>
          </div>
          <button
            v-if="canDeleteComment(comment)"
            @click="deleteComment(comment.id)"
            class="btn btn-sm btn-outline-danger"
            :title="$t('translation.leads.delete_comment')"
          >
            <i class="ri-delete-bin-line"></i>
          </button>
        </div>
        <p class="mb-0">{{ comment.comment }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Comments section styles */
.comments-list {
  max-height: 400px;
  overflow-y: auto;
}

.comment-item {
  transition: all 0.2s ease;
  background-color: #f8f9fa;
}

.comment-item:hover {
  background-color: #e9ecef;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
</style>
