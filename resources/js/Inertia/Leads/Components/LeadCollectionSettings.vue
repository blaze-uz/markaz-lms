<template>
  <div class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5)">
    <div class="modal-dialog" :class="activeTab === 'boards' ? 'modal-xl' : 'modal-lg'">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-settings-3-line me-2"></i>
            <span v-if="activeTab === 'collections'">
              {{ $t("translation.leads.collections") }} {{ $t("translation.settings") }}
            </span>
            <span v-else-if="activeTab === 'boards'">
              {{ $t("translation.leads.boards") }}
            </span>
            <span v-else-if="activeTab === 'table_columns'">
              {{ $t("translation.leads.table_column_settings") }}
            </span>
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <!-- Tabs -->
          <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                :class="{ active: activeTab === 'collections' }"
                @click="activeTab = 'collections'"
                type="button"
              >
                <i class="ri-folder-line me-1"></i>
                {{ $t("translation.leads.collections") }}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                :class="{ active: activeTab === 'boards' }"
                @click="activeTab = 'boards'"
                type="button"
              >
                <i class="ri-dashboard-line me-1"></i>
                {{ $t("translation.leads.boards") }}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                :class="{ active: activeTab === 'table_columns' }"
                @click="activeTab = 'table_columns'"
                type="button"
              >
                <i class="ri-layout-column-line me-1"></i>
                {{ $t("translation.leads.table_columns_tab") }}
              </button>
            </li>
          </ul>

          <!-- Collections Tab -->
          <div v-show="activeTab === 'collections'">
            <!-- Add New Collection -->
            <div class="card mb-3">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="ri-add-line me-1"></i>
                  {{ $t("translation.leads.add_collection") }}
                </h6>
              </div>
              <div class="card-body">
                <form @submit.prevent="createCollection">
                  <div class="row">
                    <div class="col-md-8">
                      <input
                        v-model="newCollectionName"
                        type="text"
                        class="form-control"
                        :placeholder="$t('translation.leads.collection_name')"
                        required
                        maxlength="255"
                      />
                    </div>
                    <div class="col-md-4">
                      <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="loading || !newCollectionName.trim()"
                      >
                        <i class="ri-add-line me-1"></i>
                        {{ $t("translation.add") }}
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Collections List -->
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                  <i class="ri-list-check-3 me-1"></i>
                  {{ $t("translation.leads.collections") }}
                </h6>
                <small class="text-muted">
                  {{ $t("translation.leads.drag_to_reorder") }}
                </small>
              </div>
              <div class="card-body p-0">
                <div v-if="loading" class="text-center p-4">
                  <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <div v-else-if="!collections.length" class="text-center p-4 text-muted">
                  {{ $t("translation.no_data") }}
                </div>
                <div v-else>
                  <Draggable
                    v-model="collections"
                    item-key="id"
                    :animation="200"
                    ghost-class="sortable-ghost"
                    chosen-class="sortable-chosen"
                    drag-class="sortable-drag"
                    @end="updateOrder"
                  >
                    <template #item="{ element: collection }">
                      <div
                        class="collection-item d-flex align-items-center p-3 border-bottom"
                        :class="{ editing: editingId === collection.id }"
                      >
                        <!-- Drag Handle -->
                        <div class="drag-handle me-3">
                          <i class="ri-drag-move-2-line text-muted"></i>
                        </div>

                        <!-- Collection Info -->
                        <div class="flex-grow-1">
                          <div v-if="editingId !== collection.id">
                            <div class="d-flex align-items-center">
                              <strong>{{ collection.name }}</strong>
                              <span
                                v-if="collection.board_url"
                                class="badge bg-success ms-2"
                              >
                                <i class="ri-link me-1"></i>
                                {{ $t("translation.leads.has_link") }}
                              </span>
                            </div>
                            <div class="small text-muted">
                              {{ $t("translation.order") }}: {{ collection.order }}
                            </div>
                          </div>
                          <div v-else>
                            <input
                              v-model="editingName"
                              type="text"
                              class="form-control"
                              maxlength="255"
                              @keyup.enter="saveEdit"
                              @keyup.escape="cancelEdit"
                              ref="editInput"
                            />
                          </div>
                        </div>

                        <!-- Actions -->
                        <div class="actions">
                          <div v-if="editingId !== collection.id" class="btn-group">
                            <!-- Generate Link Button (when no link exists) -->
                            <button
                              v-if="!collection.board_url"
                              class="btn btn-sm btn-outline-info"
                              @click="generateLink(collection)"
                              :disabled="loading"
                              :title="$t('translation.leads.generate_link')"
                            >
                              <i class="ri-link"></i>
                            </button>
                            <!-- Get Link Button (when link exists) -->
                            <button
                              v-else
                              class="btn btn-sm btn-outline-success"
                              @click="getLink(collection)"
                              :disabled="loading"
                              :title="$t('translation.leads.get_link')"
                            >
                              <i class="ri-eye-line"></i>
                            </button>
                            <!-- Unlink Button (when link exists) -->
                            <button
                              v-if="collection.board_url"
                              class="btn btn-sm btn-outline-warning"
                              @click="removeLink(collection)"
                              :disabled="loading"
                              :title="$t('translation.leads.remove_link')"
                            >
                              <i class="ri-link-unlink"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-primary"
                              @click="startEdit(collection)"
                              :disabled="loading"
                            >
                              <i class="ri-edit-line"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-danger"
                              @click="deleteCollection(collection)"
                              :disabled="loading"
                            >
                              <i class="ri-delete-bin-line"></i>
                            </button>
                          </div>
                          <div v-else class="btn-group">
                            <button
                              class="btn btn-sm btn-success"
                              @click="saveEdit"
                              :disabled="loading || !editingName.trim()"
                            >
                              <i class="ri-check-line"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-secondary"
                              @click="cancelEdit"
                              :disabled="loading"
                            >
                              <i class="ri-close-line"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </Draggable>
                </div>
              </div>
            </div>
          </div>

          <!-- Boards Tab -->
          <div v-show="activeTab === 'boards'">
            <BoardManagement
              :collections="collections"
              :groups="groups"
              @close="() => {}"
              @updated="() => {}"
            />
          </div>

          <!-- Table columns Tab -->
          <div v-show="activeTab === 'table_columns'" class="table-columns-tab">
            <p class="text-muted small">{{ $t("translation.leads.table_columns_hint") }}</p>
            <div class="d-flex justify-content-end mb-3">
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                @click="resetTableColumns"
              >
                <i class="ri-refresh-line me-1"></i>
                {{ $t("translation.leads.table_columns_reset") }}
              </button>
            </div>
            <div class="table-columns-scroll border rounded p-3 bg-light bg-opacity-25">
              <p class="small text-muted mb-2">
                <i class="ri-drag-move-2-line me-1"></i>
                {{ $t("translation.leads.table_columns_order_hint") }}
              </p>
              <Draggable
                v-model="tableColumnSettingRows"
                item-key="key"
                handle=".table-column-drag-handle"
                :animation="200"
                ghost-class="sortghost-column"
                chosen-class="sortchosen-column"
                drag-class="sortdrag-column"
                @end="onTableColumnsDragEnd"
              >
                <template #item="{ element }">
                  <div
                    class="d-flex align-items-center gap-2 table-column-setting-row border-bottom py-2"
                  >
                    <div
                      class="table-column-drag-handle text-muted flex-shrink-0"
                      :title="$t('translation.leads.drag_to_reorder')"
                    >
                      <i class="ri-drag-move-2-line"></i>
                    </div>
                    <div class="form-check form-switch flex-grow-1 mb-0">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        :id="'lead-col-' + element.key"
                        :checked="!!visibilityState[element.key]"
                        @change="
                          setLeadsTableColumnVisible(element.key, $event.target.checked)
                        "
                      />
                      <label class="form-check-label" :for="'lead-col-' + element.key">
                        {{ element.label }}
                        <span
                          v-if="element.key.startsWith('custom:')"
                          class="badge bg-secondary-subtle text-secondary ms-1 small"
                          >{{ $t("translation.leads.table_columns_badge_custom") }}</span
                        >
                      </label>
                    </div>
                  </div>
                </template>
              </Draggable>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            {{ $t("translation.close") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, getCurrentInstance } from "vue";
import { router } from "@inertiajs/vue3";
import Draggable from "vuedraggable";
import Swal from "sweetalert2";
import BoardManagement from "./BoardManagement.vue";
import { useLeadsTableColumnVisibility } from "../composables/useLeadsTableColumnVisibility";

const { proxy } = getCurrentInstance() ?? { proxy: null };

// Props
const props = defineProps({
  collections: {
    type: Array,
    default: () => [],
  },
  groups: {
    type: Array,
    default: () => [],
  },
  leadDynamicFields: {
    type: Array,
    default: () => [],
  },
});

const {
  visibilityState,
  leadsTableColumnOrder,
  hydrateLeadsTableColumnVisibility,
  setLeadsTableColumnVisible,
  setLeadsTableColumnOrder,
  resetLeadsTableColumnVisibilityToDefaults,
} = useLeadsTableColumnVisibility();

/** @type {import('vue').Ref<Array<{ key: string, label: string }>>} */
const tableColumnSettingRows = ref([]);

hydrateLeadsTableColumnVisibility(props.leadDynamicFields);

watch(
  () => props.leadDynamicFields,
  () => hydrateLeadsTableColumnVisibility(props.leadDynamicFields),
  { deep: true },
);

const coreColumnLabel = (key) => {
  if (!proxy) {
    return key;
  }
  const t /** @type {(k: string) => string} */ = proxy.$t.bind(proxy);
  const keys = {
    id: "translation.leads.column_id",
    name: "translation.leads.name",
    phone: "translation.leads.phone",
    address: "translation.leads.address",
    chosen_time: "translation.leads.chosen_time",
    teacher: "translation.leads.teacher",
    board: "translation.leads.board",
    course: "translation.courses.course",
    attend: "translation.leads.attend",
    date: "translation.leads.date",
  };
  const i18nKey = keys[key];
  return i18nKey ? t(i18nKey) : key;
};

const orderKeyLabel = (orderKey) => {
  if (orderKey.startsWith("custom:")) {
    const raw = orderKey.slice("custom:".length);
    const field = props.leadDynamicFields.find((f) => f.key === raw);
    return field?.label ?? orderKey;
  }
  return coreColumnLabel(orderKey);
};

const syncTableColumnSettingRows = () => {
  tableColumnSettingRows.value = leadsTableColumnOrder.value.map((key) => ({
    key,
    label: orderKeyLabel(key),
  }));
};

const onTableColumnsDragEnd = () => {
  setLeadsTableColumnOrder(
    tableColumnSettingRows.value.map((row) => row.key),
    props.leadDynamicFields,
  );
};

const resetTableColumns = () => {
  resetLeadsTableColumnVisibilityToDefaults(props.leadDynamicFields);
  syncTableColumnSettingRows();
};

// Emits
const emit = defineEmits(["close", "updated"]);

// Reactive data
const collections = ref([...props.collections]);
const loading = ref(false);
const newCollectionName = ref("");
const editingId = ref(null);
const editingName = ref("");
const editInput = ref(null);
const activeTab = ref("collections");

watch(activeTab, (tab) => {
  if (tab === "table_columns") {
    syncTableColumnSettingRows();
  }
});

watch(
  leadsTableColumnOrder,
  () => {
    if (activeTab.value === "table_columns") {
      syncTableColumnSettingRows();
    }
  },
  { deep: true },
);

// Methods
const createCollection = async () => {
  if (!newCollectionName.value.trim()) return;

  loading.value = true;
  try {
    const response = await fetch(route("admin.lead-collections.store"), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content"),
      },
      body: JSON.stringify({
        name: newCollectionName.value.trim(),
      }),
    });

    const data = await response.json();

    if (data.success) {
      collections.value.push(data.collection);
      newCollectionName.value = "";

      // Emit updated collections to parent
      emit("updated", collections.value);

      Swal.fire({
        icon: "success",
        title: data.message,
        timer: 2000,
        showConfirmButton: false,
      });
    } else {
      throw new Error(data.message || "Failed to create collection");
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message,
    });
  } finally {
    loading.value = false;
  }
};

const startEdit = (collection) => {
  editingId.value = collection.id;
  editingName.value = collection.name;
  nextTick(() => {
    if (editInput.value) {
      editInput.value.focus();
      editInput.value.select();
    }
  });
};

const cancelEdit = () => {
  editingId.value = null;
  editingName.value = "";
};

const saveEdit = async () => {
  if (!editingName.value.trim()) return;

  loading.value = true;
  try {
    const response = await fetch(
      route("admin.lead-collections.update", editingId.value),
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
        body: JSON.stringify({
          name: editingName.value.trim(),
        }),
      }
    );

    const data = await response.json();

    if (data.success) {
      const collection = collections.value.find((c) => c.id === editingId.value);
      if (collection) {
        collection.name = editingName.value.trim();
      }

      cancelEdit();

      // Emit updated collections to parent
      emit("updated", collections.value);

      Swal.fire({
        icon: "success",
        title: data.message,
        timer: 2000,
        showConfirmButton: false,
      });
    } else {
      throw new Error(data.message || "Failed to update collection");
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message,
    });
  } finally {
    loading.value = false;
  }
};

const deleteCollection = async (collection) => {
  const result = await Swal.fire({
    title: "Are you sure?",
    text: `Delete collection "${collection.name}"?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!",
  });

  if (!result.isConfirmed) return;

  loading.value = true;
  try {
    const response = await fetch(route("admin.lead-collections.delete", collection.id), {
      method: "DELETE",
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content"),
      },
    });

    const data = await response.json();

    if (data.success) {
      collections.value = collections.value.filter((c) => c.id !== collection.id);

      // Emit updated collections to parent
      emit("updated", collections.value);

      Swal.fire({
        icon: "success",
        title: data.message,
        timer: 2000,
        showConfirmButton: false,
      });
    } else {
      throw new Error(data.message || "Failed to delete collection");
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message,
    });
  } finally {
    loading.value = false;
  }
};

const generateLink = async (collection) => {
  loading.value = true;
  try {
    const response = await fetch(
      route("admin.lead-collections.generate-link", collection.id),
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
      }
    );

    const data = await response.json();

    if (data.success) {
      const leadFormUrl = `${window.location.origin}/lead/${data.board_url}`;

      // Update the collection with the new board_url
      const collectionIndex = collections.value.findIndex((c) => c.id === collection.id);
      if (collectionIndex !== -1) {
        collections.value[collectionIndex].board_url = data.board_url;
      }

      // Copy to clipboard
      await navigator.clipboard.writeText(leadFormUrl);

      // Show success message with the link
      Swal.fire({
        icon: "success",
        title: "Link Generated!",
        html: `
          <p>Lead form link has been generated and copied to clipboard:</p>
          <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; margin: 10px 0;">
            <strong>${leadFormUrl}</strong>
          </div>
          <small class="text-muted">This link can be shared to collect leads for "${collection.name}"</small>
        `,
        showConfirmButton: true,
        confirmButtonText: "OK",
      });
    } else {
      throw new Error(data.message || "Failed to generate link");
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message,
    });
  } finally {
    loading.value = false;
  }
};

const getLink = async (collection) => {
  if (!collection.board_url) {
    Swal.fire({
      icon: "error",
      title: "No Link Found",
      text: "This collection doesn't have a generated link yet.",
    });
    return;
  }

  const leadFormUrl = `${window.location.origin}/lead/${collection.board_url}`;

  try {
    // Copy to clipboard
    await navigator.clipboard.writeText(leadFormUrl);

    // Show the link
    Swal.fire({
      icon: "success",
      title: "Link Retrieved!",
      html: `
        <p>Lead form link has been copied to clipboard:</p>
        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; margin: 10px 0;">
          <strong>${leadFormUrl}</strong>
        </div>
        <small class="text-muted">This link is for collection "${collection.name}"</small>
      `,
      showConfirmButton: true,
      confirmButtonText: "OK",
    });
  } catch (error) {
    // Fallback if clipboard API fails
    Swal.fire({
      icon: "info",
      title: "Lead Form Link",
      html: `
        <p>Here's the link for collection "${collection.name}":</p>
        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; margin: 10px 0;">
          <strong>${leadFormUrl}</strong>
        </div>
        <small class="text-muted">Click to select and copy manually</small>
      `,
      showConfirmButton: true,
      confirmButtonText: "OK",
    });
  }
};

const removeLink = async (collection) => {
  const result = await Swal.fire({
    title: "Remove Link?",
    text: `Are you sure you want to remove the link for collection "${collection.name}"?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#f39c12",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, remove it!",
    cancelButtonText: "Cancel",
  });

  if (!result.isConfirmed) return;

  loading.value = true;
  try {
    const response = await fetch(
      route("admin.lead-collections.remove-link", collection.id),
      {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
      }
    );

    const data = await response.json();

    if (data.success) {
      // Update the collection to remove the board_url
      const collectionIndex = collections.value.findIndex((c) => c.id === collection.id);
      if (collectionIndex !== -1) {
        collections.value[collectionIndex].board_url = null;
      }

      // Emit updated collections to parent
      emit("updated", collections.value);

      Swal.fire({
        icon: "success",
        title: "Link Removed!",
        text: data.message,
        timer: 2000,
        showConfirmButton: false,
      });
    } else {
      throw new Error(data.message || "Failed to remove link");
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message,
    });
  } finally {
    loading.value = false;
  }
};

const updateOrder = async () => {
  // Update order values
  collections.value.forEach((collection, index) => {
    collection.order = index + 1;
  });

  loading.value = true;
  try {
    const response = await fetch(route("admin.lead-collections.update-order"), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content"),
      },
      body: JSON.stringify({
        collections: collections.value.map((collection, index) => ({
          id: collection.id,
          order: index + 1,
        })),
      }),
    });

    const data = await response.json();

    if (!data.success) {
      throw new Error(data.message || "Failed to update order");
    }

    // Emit updated collections to parent
    emit("updated", collections.value);
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message,
    });

    // Refresh page to restore original order
    router.reload();
  } finally {
    loading.value = false;
  }
};

// Initialize
onMounted(() => {
  // Sort collections by order
  collections.value.sort((a, b) => a.order - b.order);
});
</script>

<style scoped>
.collection-item {
  cursor: move;
  transition: all 0.3s ease;
}

.collection-item:hover {
  background-color: #f8f9fa;
}

.collection-item.editing {
  background-color: #e3f2fd;
}

.drag-handle {
  cursor: grab;
}

.drag-handle:active {
  cursor: grabbing;
}

.sortable-ghost {
  opacity: 0.5;
  background-color: #e3f2fd;
}

.sortable-chosen {
  background-color: #bbdefb;
}

.sortable-drag {
  background-color: #ffffff;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: rotate(5deg);
}

.table-column-drag-handle {
  cursor: grab;
}

.table-column-drag-handle:active {
  cursor: grabbing;
}

.sortghost-column {
  opacity: 0.55;
  background-color: #e3f2fd;
}

.sortchosen-column {
  background-color: #e3f2fd;
}

.sortdrag-column {
  opacity: 0.95;
}

.actions .btn-group .btn {
  padding: 0.25rem 0.5rem;
}

.table-columns-scroll {
  max-height: 55vh;
  overflow-y: auto;
}
</style>
