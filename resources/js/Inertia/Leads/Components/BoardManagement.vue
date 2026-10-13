<script setup>
import { reactive, ref, onMounted, getCurrentInstance } from "vue";
import axios from "axios";

const props = defineProps({
  collections: Array,
  groups: Array,
});

const emit = defineEmits(["close", "updated"]);

const { proxy } = getCurrentInstance();

// Reactive data
const boards = ref([]);
const loading = ref(false);
const showCreateForm = ref(false);
const editingBoard = ref(null);
const selectedBoard = ref(null);

// Form data
const form = reactive({
  name: "",
  lead_collection_id: "",
  group_id: "",
});

const errors = reactive({});

// Load boards on mount
onMounted(() => {
  loadBoards();
});

// Methods
const loadBoards = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/admin/lead-boards");
    if (response.data.success) {
      boards.value = response.data.boards;
    }
  } catch (error) {
    console.error("Error loading boards:", error);
    if (window.notifyMessage) {
      window.notifyMessage("Failed to load boards", "danger");
    }
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.name = "";
  form.lead_collection_id = "";
  form.group_id = "";
  Object.keys(errors).forEach((key) => delete errors[key]);
};

const handleCreate = () => {
  resetForm();
  showCreateForm.value = true;
};

const handleEdit = (board) => {
  resetForm();
  editingBoard.value = board;
  form.name = board.name;
  form.lead_collection_id = board.lead_collection_id;
  form.group_id = board.group_id || "";
};

const cancelEdit = () => {
  editingBoard.value = null;
  resetForm();
};

const handleDelete = async (board) => {
  if (
    confirm(
      `Are you sure you want to delete "${board.name}"? This will remove the board association from all leads.`
    )
  ) {
    selectedBoard.value = board;
    await confirmDelete();
  }
};

const submitCreate = async () => {
  loading.value = true;
  Object.keys(errors).forEach((key) => delete errors[key]);

  try {
    const response = await axios.post("/admin/lead-boards", form);
    if (response.data.success) {
      boards.value.push(response.data.board);
      showCreateForm.value = false;
      resetForm();
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors);
    } else {
      if (window.notifyMessage) {
        window.notifyMessage("Failed to create board", "danger");
      }
    }
  } finally {
    loading.value = false;
  }
};

const submitEdit = async () => {
  loading.value = true;
  Object.keys(errors).forEach((key) => delete errors[key]);

  try {
    const response = await axios.put(`/admin/lead-boards/${editingBoard.value.id}`, form);
    if (response.data.success) {
      const index = boards.value.findIndex((b) => b.id === editingBoard.value.id);
      if (index !== -1) {
        boards.value[index] = response.data.board;
      }
      editingBoard.value = null;
      resetForm();
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors);
    } else {
      if (window.notifyMessage) {
        window.notifyMessage("Failed to update board", "danger");
      }
    }
  } finally {
    loading.value = false;
  }
};

const confirmDelete = async () => {
  loading.value = true;

  try {
    const response = await axios.delete(`/admin/lead-boards/${selectedBoard.value.id}`);
    if (response.data.success) {
      boards.value = boards.value.filter((b) => b.id !== selectedBoard.value.id);
      selectedBoard.value = null;
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }
    }
  } catch (error) {
    if (window.notifyMessage) {
      window.notifyMessage("Failed to delete board", "danger");
    }
  } finally {
    loading.value = false;
  }
};

const generateUrl = async (board) => {
  try {
    const response = await axios.post(`/admin/lead-boards/${board.id}/generate-url`);
    if (response.data.success) {
      const index = boards.value.findIndex((b) => b.id === board.id);
      if (index !== -1) {
        boards.value[index].url = response.data.url;
      }
      if (window.notifyMessage) {
        window.notifyMessage("URL generated successfully", "success");
      }
    }
  } catch (error) {
    if (window.notifyMessage) {
      window.notifyMessage("Failed to generate URL", "danger");
    }
  }
};

const removeUrl = async (board) => {
  try {
    const response = await axios.delete(`/admin/lead-boards/${board.id}/remove-url`);
    if (response.data.success) {
      const index = boards.value.findIndex((b) => b.id === board.id);
      if (index !== -1) {
        boards.value[index].url = null;
      }
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }
    }
  } catch (error) {
    if (window.notifyMessage) {
      window.notifyMessage("Failed to remove URL", "danger");
    }
  }
};

const copyUrl = (board) => {
  const fullUrl = `${window.location.origin}/lead/${board.url}`;
  navigator.clipboard.writeText(fullUrl).then(() => {
    if (window.notifyMessage) {
      window.notifyMessage("URL copied to clipboard", "success");
    }
  });
};
</script>

<template>
  <div>
    <!-- Header with Create Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h6 class="mb-0">{{ $t("translation.leads.boards") }}</h6>
      <button class="btn btn-primary" @click="handleCreate" :disabled="loading">
        <i class="ri-add-line me-1"></i>
        {{ $t("translation.leads.create_board") }}
      </button>
    </div>

    <!-- Create Form -->
    <div v-if="showCreateForm" class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="ri-add-line me-1"></i>
          {{ $t("translation.leads.create_board") }}
        </h6>
      </div>
      <div class="card-body">
        <form @submit.prevent="submitCreate">
          <div class="row g-3">
            <!-- Name -->
            <div class="col-12">
              <label class="form-label">
                {{ $t("translation.leads.name") }}
                <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                v-model="form.name"
                :placeholder="$t('translation.leads.board_name')"
                :disabled="loading"
                required
              />
              <div v-if="errors.name" class="invalid-feedback">
                {{ errors.name[0] }}
              </div>
            </div>

            <!-- Collection -->
            <div class="col-md-6">
              <label class="form-label">
                {{ $t("translation.leads.collection") }}
                <span class="text-danger">*</span>
              </label>
              <select
                class="form-select"
                :class="{ 'is-invalid': errors.lead_collection_id }"
                v-model="form.lead_collection_id"
                :disabled="loading"
                required
              >
                <option value="">
                  {{ $t("translation.leads.collection") }}
                </option>
                <option
                  v-for="collection in collections"
                  :key="collection.id"
                  :value="collection.id"
                >
                  {{ collection.name }}
                </option>
              </select>
              <div v-if="errors.lead_collection_id" class="invalid-feedback">
                {{ errors.lead_collection_id[0] }}
              </div>
            </div>

            <!-- Group -->
            <div class="col-md-6">
              <label class="form-label">
                {{ $t("translation.groups.group") }}
              </label>
              <select
                class="form-select"
                :class="{ 'is-invalid': errors.group_id }"
                v-model="form.group_id"
                :disabled="loading"
              >
                <option value="">{{ $t("translation.leads.select_group") }}</option>
                <option v-for="group in groups" :key="group.id" :value="group.id">
                  {{ group.name }}
                </option>
              </select>
              <div v-if="errors.group_id" class="invalid-feedback">
                {{ errors.group_id[0] }}
              </div>
            </div>

            <!-- Buttons -->
            <div class="col-12">
              <button
                type="button"
                class="btn btn-secondary me-2"
                @click="showCreateForm = false"
                :disabled="loading"
              >
                {{ $t("translation.cancel") }}
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span
                  v-if="loading"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                ></span>
                {{ $t("translation.save") }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && boards.length === 0" class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Boards Table -->
    <div v-else-if="boards.length > 0" class="table-responsive">
      <table class="table table-hover">
        <thead class="table-light">
          <tr>
            <th>{{ $t("translation.leads.name") }}</th>
            <th>{{ $t("translation.leads.collection") }}</th>
            <th>{{ $t("translation.groups.group") }}</th>
            <th>{{ $t("translation.leads.board_url") }}</th>
            <th width="200">{{ $t("translation.actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="board in boards" :key="board.id">
            <!-- Name -->
            <td>
              <div v-if="editingBoard?.id === board.id">
                <input
                  type="text"
                  class="form-control form-control-sm"
                  :class="{ 'is-invalid': errors.name }"
                  v-model="form.name"
                  :disabled="loading"
                  required
                />
                <div v-if="errors.name" class="invalid-feedback">
                  {{ errors.name[0] }}
                </div>
              </div>
              <strong v-else>{{ board.name }}</strong>
            </td>

            <!-- Collection -->
            <td>
              <div v-if="editingBoard?.id === board.id">
                <select
                  class="form-select form-select-sm"
                  :class="{ 'is-invalid': errors.lead_collection_id }"
                  v-model="form.lead_collection_id"
                  :disabled="loading"
                  required
                >
                  <option value="">Select Collection</option>
                  <option
                    v-for="collection in collections"
                    :key="collection.id"
                    :value="collection.id"
                  >
                    {{ collection.name }}
                  </option>
                </select>
                <div v-if="errors.lead_collection_id" class="invalid-feedback">
                  {{ errors.lead_collection_id[0] }}
                </div>
              </div>
              <span v-else class="badge bg-primary">
                {{ board.lead_collection?.name || "N/A" }}
              </span>
            </td>

            <!-- Group -->
            <td>
              <div v-if="editingBoard?.id === board.id">
                <select
                  class="form-select form-select-sm"
                  :class="{ 'is-invalid': errors.group_id }"
                  v-model="form.group_id"
                  :disabled="loading"
                >
                  <option value="">Select Group</option>
                  <option v-for="group in groups" :key="group.id" :value="group.id">
                    {{ group.name }}
                  </option>
                </select>
                <div v-if="errors.group_id" class="invalid-feedback">
                  {{ errors.group_id[0] }}
                </div>
              </div>
              <div v-else>
                <span v-if="board.group" class="badge bg-success">
                  {{ board.group.name }}
                </span>
                <span v-else class="text-muted">-</span>
              </div>
            </td>

            <!-- URL -->
            <td>
              <div v-if="board.url" class="d-flex align-items-center gap-2">
                <code class="small">{{ board.url }}</code>
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="copyUrl(board)"
                  title="Copy URL"
                >
                  <i class="ri-file-copy-line"></i>
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="removeUrl(board)"
                  title="Remove URL"
                >
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
              <button
                v-else
                class="btn btn-sm btn-outline-success"
                @click="generateUrl(board)"
              >
                <i class="ri-link me-1"></i>
                Generate URL
              </button>
            </td>

            <!-- Actions -->
            <td>
              <div v-if="editingBoard?.id === board.id" class="btn-group btn-group-sm">
                <button class="btn btn-success" @click="submitEdit" :disabled="loading">
                  <i class="ri-check-line"></i>
                </button>
                <button class="btn btn-secondary" @click="cancelEdit" :disabled="loading">
                  <i class="ri-close-line"></i>
                </button>
              </div>
              <div v-else class="btn-group btn-group-sm">
                <button
                  class="btn btn-outline-primary"
                  @click="handleEdit(board)"
                  :disabled="loading"
                >
                  <i class="ri-edit-line"></i>
                </button>
                <button
                  class="btn btn-outline-danger"
                  @click="handleDelete(board)"
                  :disabled="loading"
                >
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-5">
      <i class="ri-dashboard-line fs-1 text-muted d-block mb-3"></i>
      <h5 class="text-muted">{{ $t("translation.leads.no_boards") }}</h5>
      <p class="text-muted">{{ $t("translation.leads.create_first_board") }}</p>
      <button class="btn btn-primary" @click="handleCreate">
        <i class="ri-add-line me-1"></i>
        {{ $t("translation.leads.create_board") }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.table th {
  font-weight: 600;
  font-size: 0.875rem;
}

.badge {
  font-size: 0.75rem;
}

code {
  background-color: #f8f9fa;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.8rem;
}

.btn-group-sm .btn {
  padding: 0.25rem 0.5rem;
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.alert {
  font-size: 0.875rem;
}
</style>
