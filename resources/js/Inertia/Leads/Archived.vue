<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import axios from "axios";
import LeadsNavTabs from "./Components/LeadsNavTabs.vue";

// Reactive data
const archivedLeads = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const searchInputRef = ref(null);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

// Modal state
const showConfirmModal = ref(false);
const confirmAction = ref(null); // 'restore' or 'delete'
const selectedLead = ref(null);
const modalLoading = ref(false);

let searchTimeout = null;

// Computed
const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  // Show max 5 pages
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// Methods
const fetchArchivedLeads = async (page = 1, search = "") => {
  loading.value = true;

  try {
    const response = await axios.get("/admin/leads/archived", {
      params: {
        page,
        search: search.trim() || undefined,
        per_page: pagination.value.per_page,
      },
    });

    if (response.data.success) {
      archivedLeads.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to,
      };
    }
  } catch (error) {
    console.error("Error fetching archived leads:", error);
    if (window.notifyMessage) {
      window.notifyMessage("Failed to load archived leads", "danger");
    }
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && !loading.value) {
    fetchArchivedLeads(page, searchQuery.value);
  }
};

const debouncedSearch = (query) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    // Store focus state
    const wasSearchInputFocused = document.activeElement === searchInputRef.value;

    fetchArchivedLeads(1, query).then(() => {
      // Restore focus if it was previously focused
      if (wasSearchInputFocused && searchInputRef.value) {
        nextTick(() => {
          searchInputRef.value.focus();
          // Restore cursor position to end
          const length = searchInputRef.value.value.length;
          searchInputRef.value.setSelectionRange(length, length);
        });
      }
    });
  }, 500);
};

const clearSearch = () => {
  searchQuery.value = "";
  // Maintain focus when clearing
  nextTick(() => {
    if (searchInputRef.value) {
      searchInputRef.value.focus();
    }
  });
  fetchArchivedLeads(1, "");
};

const refreshData = () => {
  fetchArchivedLeads(pagination.value.current_page, searchQuery.value);
};

const restoreLead = (lead) => {
  selectedLead.value = lead;
  confirmAction.value = "restore";
  showConfirmModal.value = true;
};

const deleteLead = (lead) => {
  selectedLead.value = lead;
  confirmAction.value = "delete";
  showConfirmModal.value = true;
};

const handleConfirm = async () => {
  if (!selectedLead.value || !confirmAction.value) return;

  try {
    modalLoading.value = true;

    let response;
    if (confirmAction.value === "restore") {
      response = await axios.post(
        `/admin/leads/${selectedLead.value.id}/restore`,
        {},
        {
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content"),
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        }
      );
    } else if (confirmAction.value === "delete") {
      response = await axios.delete(
        `/admin/leads/${selectedLead.value.id}/force-delete`,
        {
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content"),
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        }
      );
    }

    if (response.data.success) {
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }

      // Close modal
      handleModalClose();

      // Refresh the current page
      await fetchArchivedLeads(pagination.value.current_page, searchQuery.value);
    }
  } catch (error) {
    console.error(
      `Error ${confirmAction.value === "restore" ? "restoring" : "deleting"} lead:`,
      error
    );
    if (window.notifyMessage) {
      const errorMessage =
        error.response?.data?.message ||
        `Failed to ${
          confirmAction.value === "restore" ? "restore" : "permanently delete"
        } lead`;
      window.notifyMessage(errorMessage, "danger");
    }
  } finally {
    modalLoading.value = false;
  }
};

const handleModalClose = () => {
  showConfirmModal.value = false;
  selectedLead.value = null;
  confirmAction.value = null;
  modalLoading.value = false;
};

const formatDate = (dateString) => {
  if (!dateString) return "-";

  const date = new Date(dateString);
  return date.toLocaleString("en-GB", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
};

// Watchers
watch(searchQuery, (newQuery) => {
  debouncedSearch(newQuery);
});

// Lifecycle
onMounted(() => {
  fetchArchivedLeads();
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
          <LeadsNavTabs active="archived" />
        </div>

        <!-- Right side search and refresh -->
        <div class="tab-actions d-flex align-items-center">
          <div class="me-2">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="ri-search-line text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control bg-light border-start-0 pl-0"
                :placeholder="$t('translation.leads.search')"
                v-model="searchQuery"
                :disabled="loading"
                ref="searchInputRef"
              />
              <button
                v-if="loading"
                class="btn btn-light border-start-0"
                type="button"
                disabled
              >
                <div class="spinner-border spinner-border-sm" role="status">
                  <span class="visually-hidden">Searching...</span>
                </div>
              </button>
              <button
                v-else-if="searchQuery"
                class="btn btn-light border-start-0"
                type="button"
                @click="clearSearch"
              >
                <i class="ri-close-line"></i>
              </button>
            </div>
          </div>
          <!-- Refresh -->
          <button
            class="btn btn-outline-primary"
            @click="refreshData"
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

    <!-- Archived Leads Content -->
    <div class="archived-leads-content">
      <!-- Loading State -->
      <div v-if="loading && !archivedLeads.length" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">{{ $t("translation.leads.loading_archived") }}</p>
      </div>

      <!-- Table -->
      <div v-else class="card flex-grow-1 d-flex flex-column">
        <div class="card-body p-0 flex-grow-1 overflow-hidden">
          <div class="table-responsive" style="height: 100%; overflow: auto">
            <table class="table table-hover mb-0" style="min-width: 1200px">
              <thead class="table-light">
                <tr>
                  <th style="width: 60px">ID</th>
                  <th style="min-width: 150px">{{ $t("translation.leads.name") }}</th>
                  <th style="min-width: 130px">{{ $t("translation.leads.phone") }}</th>
                  <th style="min-width: 120px">
                    {{ $t("translation.leads.collection") }}
                  </th>
                  <th style="min-width: 120px">{{ $t("translation.leads.course") }}</th>
                  <th style="min-width: 120px">{{ $t("translation.leads.teacher") }}</th>
                  <th style="min-width: 150px">
                    {{ $t("translation.leads.deleted_by") }}
                  </th>
                  <th style="min-width: 140px">
                    {{ $t("translation.leads.deleted_at") }}
                  </th>
                  <th style="width: 120px" class="text-center">
                    {{ $t("translation.actions") }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!archivedLeads.length && !loading">
                  <td colspan="9" class="text-center py-5 text-muted">
                    <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                    <p class="mb-0">{{ $t("translation.leads.no_archived_leads") }}</p>
                  </td>
                </tr>
                <tr v-for="lead in archivedLeads" :key="lead.id">
                  <td class="fw-bold">{{ lead.id }}</td>
                  <td>
                    <div>
                      <h6 class="mb-0">{{ lead.name }}</h6>
                    </div>
                  </td>
                  <td class="text-nowrap">
                    <a :href="'tel:' + lead.phone" class="text-decoration-none">
                      <i class="ri-phone-line me-1"></i>
                      {{ lead.phone }}
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark">
                      {{ lead.collection?.name || "N/A" }}
                    </span>
                  </td>
                  <td>
                    <span v-if="lead.course" class="text-muted">
                      {{ lead.course.name }}
                    </span>
                    <span v-else class="text-muted">-</span>
                  </td>
                  <td>
                    <span v-if="lead.teacher" class="text-muted">
                      {{ lead.teacher.name }}
                    </span>
                    <span v-else class="text-muted">-</span>
                  </td>
                  <td>
                    <div v-if="lead.deleted_by" class="d-flex align-items-center">
                      <div class="causer-avatar me-2">
                        <img
                          v-if="lead.deleted_by.avatar_url"
                          :src="lead.deleted_by.avatar_url"
                          :alt="lead.deleted_by.name"
                          class="causer-avatar-img"
                        />
                        <span v-else class="causer-avatar-letter">
                          {{ lead.deleted_by.name?.charAt(0) || "U" }}
                        </span>
                      </div>
                      <div>
                        <small class="text-muted">{{ lead.deleted_by.name }}</small>
                      </div>
                    </div>
                    <span v-else class="text-muted">-</span>
                  </td>
                  <td>
                    <small class="text-muted text-nowrap">
                      {{ formatDate(lead.deleted_at) }}
                    </small>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <button
                        class="btn btn-sm btn-outline-success"
                        @click="restoreLead(lead)"
                        :disabled="loading"
                        :title="$t('translation.leads.restore')"
                      >
                        <i class="ri-restart-line"></i>
                      </button>
                      <button
                        class="btn btn-sm btn-outline-danger"
                        @click="deleteLead(lead)"
                        :disabled="loading"
                        :title="$t('translation.leads.delete_permanently')"
                      >
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="pagination.total > pagination.per_page"
          class="card-footer d-flex justify-content-between"
        >
          <div>
            <small class="text-muted">
              {{ $t("translation.leads.showing") }} {{ pagination.from }} -
              {{ pagination.to }} {{ $t("translation.leads.of") }} {{ pagination.total }}
              {{ $t("translation.leads.results") }}
            </small>
          </div>
          <nav aria-label="Archived leads pagination">
            <ul class="pagination pagination-sm justify-content-end mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <button
                  class="page-link"
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1 || loading"
                >
                  <i class="ri-arrow-left-s-line"></i>
                </button>
              </li>

              <li
                v-for="page in visiblePages"
                :key="page"
                class="page-item"
                :class="{ active: page === pagination.current_page }"
              >
                <button class="page-link" @click="changePage(page)" :disabled="loading">
                  {{ page }}
                </button>
              </li>

              <li
                class="page-item"
                :class="{ disabled: pagination.current_page === pagination.last_page }"
              >
                <button
                  class="page-link"
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page || loading"
                >
                  <i class="ri-arrow-right-s-line"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div
    v-if="showConfirmModal"
    class="modal fade show"
    style="display: block"
    tabindex="-1"
    @click.self="handleModalClose"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i
              :class="
                confirmAction === 'restore'
                  ? 'ri-restart-line text-success'
                  : 'ri-delete-bin-line text-danger'
              "
              class="me-2"
            ></i>
            {{
              confirmAction === "restore"
                ? $t("translation.leads.restore_lead")
                : $t("translation.leads.delete_lead")
            }}
          </h5>
          <button
            type="button"
            class="btn-close"
            @click="handleModalClose"
            :disabled="modalLoading"
          ></button>
        </div>
        <div class="modal-body">
          <template v-if="confirmAction === 'restore'">
            <p class="mb-3">
              {{ $t("translation.leads.restore_confirmation") }}
              <strong>"{{ selectedLead?.name }}"</strong>?
            </p>
            <div class="alert alert-info">
              <i class="ri-information-line me-2"></i>
              {{ $t("translation.leads.restore_description") }}
            </div>
          </template>
          <template v-else-if="confirmAction === 'delete'">
            <p class="mb-3">
              {{ $t("translation.leads.permanent_delete_confirmation") }}
              <strong>"{{ selectedLead?.name }}"</strong>?
            </p>
            <div class="alert alert-danger">
              <i class="ri-error-warning-line me-2"></i>
              {{ $t("translation.leads.permanent_delete_warning") }}
            </div>
          </template>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-light"
            @click="handleModalClose"
            :disabled="modalLoading"
          >
            {{ $t("translation.cancel") }}
          </button>
          <button
            type="button"
            :class="confirmAction === 'restore' ? 'btn btn-success' : 'btn btn-danger'"
            @click="handleConfirm"
            :disabled="modalLoading"
          >
            <span
              v-if="modalLoading"
              class="spinner-border spinner-border-sm me-2"
              role="status"
            ></span>
            <i
              v-else
              :class="
                confirmAction === 'restore' ? 'ri-restart-line' : 'ri-delete-bin-line'
              "
              class="me-1"
            ></i>
            {{
              confirmAction === "restore"
                ? $t("translation.leads.restore")
                : $t("translation.leads.delete_permanently")
            }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Backdrop -->
  <div v-if="showConfirmModal" class="modal-backdrop fade show"></div>
</template>

<style scoped>
.header-tabs {
  background-color: #fff;
  padding: 13px 0;
}

.search-box .input-group {
  width: 250px !important;
  padding-left: 0 !important;
}

.archived-leads-content {
  flex: 1;
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.causer-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #3f5189;
  flex-shrink: 0;
}

.causer-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.causer-avatar-letter {
  color: white;
  font-weight: bold;
  font-size: 0.75rem;
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

  .tab-actions .input-group {
    width: 100% !important;
    margin-bottom: 0.5rem;
  }

  .tab-actions .btn-outline-primary {
    width: 100%;
    font-size: 0.9rem;
  }

  /* Table responsive improvements */
  .table-responsive {
    border: none;
  }

  .table {
    font-size: 0.8rem;
    min-width: 800px !important;
  }

  .table th,
  .table td {
    padding: 0.5rem 0.25rem;
    vertical-align: middle;
  }

  .table th {
    font-size: 0.75rem;
    font-weight: 600;
  }

  /* Reduce column widths for mobile */
  .table th:nth-child(1), /* ID */
  .table td:nth-child(1) {
    width: 40px;
    min-width: 40px;
  }

  .table th:nth-child(2), /* Name */
  .table td:nth-child(2) {
    min-width: 100px;
  }

  .table th:nth-child(3), /* Phone */
  .table td:nth-child(3) {
    min-width: 90px;
  }

  .table th:nth-child(4), /* Collection */
  .table td:nth-child(4) {
    min-width: 80px;
  }

  .table th:nth-child(5), /* Course */
  .table td:nth-child(5) {
    min-width: 80px;
  }

  .table th:nth-child(6), /* Teacher */
  .table td:nth-child(6) {
    min-width: 80px;
  }

  .table th:nth-child(7), /* Deleted by */
  .table td:nth-child(7) {
    min-width: 100px;
  }

  .table th:nth-child(8), /* Deleted at */
  .table td:nth-child(8) {
    min-width: 90px;
  }

  .table th:nth-child(9), /* Actions */
  .table td:nth-child(9) {
    width: 80px;
    min-width: 80px;
  }

  /* Compact avatar */
  .causer-avatar {
    width: 20px;
    height: 20px;
  }

  .causer-avatar-letter {
    font-size: 0.6rem;
  }

  /* Compact buttons */
  .btn-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.35rem;
  }

  .d-flex.gap-1 {
    gap: 0.25rem !important;
  }

  /* Badge adjustments */
  .badge {
    font-size: 0.65rem;
    padding: 0.25em 0.4em;
  }

  /* Pagination adjustments */
  .pagination-sm .page-link {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }

  .card-footer {
    padding: 0.75rem;
    flex-direction: column;
    text-align: center;
  }

  .card-footer small {
    margin-bottom: 0.5rem;
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

  .tab-actions .btn-outline-primary span {
    font-size: 0.8rem;
  }

  /* Extra compact table for small screens */
  .table {
    font-size: 0.7rem;
    min-width: 700px !important;
  }

  .table th,
  .table td {
    padding: 0.4rem 0.2rem;
  }

  .table th {
    font-size: 0.65rem;
  }

  /* Hide less important columns on very small screens */
  .table th:nth-child(5), /* Course */
  .table td:nth-child(5),
  .table th:nth-child(6), /* Teacher */
  .table td:nth-child(6) {
    display: none;
  }

  /* Adjust remaining columns */
  .table th:nth-child(4), /* Collection */
  .table td:nth-child(4) {
    min-width: 70px;
  }

  .table th:nth-child(7), /* Deleted by */
  .table td:nth-child(7) {
    min-width: 90px;
  }

  .table th:nth-child(8), /* Deleted at */
  .table td:nth-child(8) {
    min-width: 80px;
  }

  /* Extra compact avatar */
  .causer-avatar {
    width: 18px;
    height: 18px;
  }

  .causer-avatar-letter {
    font-size: 0.55rem;
  }

  /* Extra compact buttons */
  .btn-sm {
    font-size: 0.65rem;
    padding: 0.2rem 0.3rem;
  }

  /* Compact badges */
  .badge {
    font-size: 0.6rem;
    padding: 0.2em 0.35em;
  }

  /* Modal adjustments for small screens */
  .modal-dialog {
    margin: 0.5rem;
  }

  .modal-body {
    padding: 1rem;
  }

  .modal-footer {
    padding: 0.75rem;
    flex-direction: column;
  }

  .modal-footer .btn {
    width: 100%;
    margin-bottom: 0.5rem;
  }

  .modal-footer .btn:last-child {
    margin-bottom: 0;
  }
}
</style>
