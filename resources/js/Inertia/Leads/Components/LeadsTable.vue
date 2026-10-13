<template>
  <div class="leads-table-container">
    <!-- Filters Section -->
    <div class="card mb-4 leads-filters-card">
      <div class="card-body py-2 px-3">
        <div
          class="d-flex flex-nowrap align-items-center gap-2 leads-filters-toolbar w-100"
        >
          <div class="leads-filter-search">
            <input
              type="text"
              class="form-control"
              :placeholder="$t('translation.search')"
              v-model="filters.search"
              @input="debouncedSearch"
            />
          </div>
          <button
            type="button"
            class="btn btn-sm btn-light border text-nowrap flex-shrink-0"
            @click="openDatePicker"
          >
            <i class="far fa-calendar-alt me-1"></i>
            <span class="d-none d-sm-inline">{{ filters.date }}</span>
            <span class="d-sm-none">{{ filters.date.split(" - ")[0] }}…</span>
          </button>

          <div class="leads-inline-filter-select">
            <SearchableSelect
              v-model="filters.selectedChosenDays"
              :options="chosenDaysOptions"
              :placeholder="$t('translation.leads.chosen_days')"
              :allow-clear="true"
            />
          </div>
          <div class="leads-inline-filter-select">
            <SearchableSelect
              v-model="filters.selectedTeacher"
              :options="teacherOptions"
              :placeholder="$t('translation.leads.teacher')"
              :allow-clear="true"
            />
          </div>
          <div class="leads-inline-filter-select">
            <SearchableSelect
              v-model="filters.selectedCourse"
              :options="courseOptions"
              :placeholder="$t('translation.courses.course')"
              :allow-clear="true"
            />
          </div>

          <div class="dropdown flex-shrink-0 leads-more-filters-dropdown">
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1"
              data-bs-toggle="dropdown"
              data-bs-auto-close="outside"
              data-bs-popper-config='{"strategy":"fixed"}'
              aria-expanded="false"
              id="leadsMoreFiltersToggle"
            >
              <i class="ri-filter-3-line"></i>
              <span class="d-none d-sm-inline">{{ $t("translation.leads.more_filters") }}</span>
              <span
                v-if="moreFiltersMenuBadgeCount > 0"
                class="badge bg-primary rounded-pill align-middle"
                >{{ moreFiltersMenuBadgeCount }}</span
              >
            </button>
            <div
              class="dropdown-menu dropdown-menu-end p-3 shadow leads-more-filters-menu"
              aria-labelledby="leadsMoreFiltersToggle"
            >
              <div class="small text-muted fw-semibold mb-2 pb-2 border-bottom text-uppercase">
                {{ $t("translation.leads.more_filters") }}
              </div>
              <div class="d-grid gap-2">
                <SearchableSelect
                  v-model="filters.selectedBoard"
                  :options="boardOptions"
                  :placeholder="$t('translation.leads.board')"
                  :allow-clear="true"
                />
                <SearchableSelect
                  v-model="filters.selectedCauser"
                  :options="causerOptions"
                  :placeholder="$t('translation.leads.causer')"
                  :allow-clear="true"
                />
                <SearchableSelect
                  v-model="filters.selectedAttend"
                  :options="attendOptions"
                  :placeholder="$t('translation.leads.attend')"
                  :allow-clear="true"
                />
                <label class="form-check d-flex align-items-center gap-2 mb-0 py-1">
                  <input
                    v-model="filters.includeDeleted"
                    class="form-check-input mt-0"
                    type="checkbox"
                  />
                  <span class="form-check-label">
                    {{ $t("translation.leads.include_deleted") }}
                  </span>
                </label>
              </div>
              <button
                v-if="secondaryFiltersActiveCount > 0"
                type="button"
                class="btn btn-link btn-sm text-decoration-none px-0 mt-2"
                @click="clearSecondaryFilters"
              >
                {{ $t("translation.clear_filters") }}
              </button>

              <template v-if="leadDynamicFields.length">
                <hr class="dropdown-divider my-3" />
                <div
                  class="small text-muted fw-semibold mb-3 pb-2 border-bottom d-flex align-items-center gap-2"
                >
                  <i class="ri-list-settings-line"></i>
                  <span>{{ $t("translation.leads.additional_fields_filter") }}</span>
                  <span
                    v-if="activeCustomFilterCount > 0"
                    class="badge bg-secondary rounded-pill ms-auto"
                    >{{ activeCustomFilterCount }}</span
                  >
                </div>
                <div
                  v-for="field in leadDynamicFields"
                  :key="'dyn-filter-' + field.key"
                  class="mb-3"
                >
                  <label class="form-label small mb-1 text-body">{{ field.label }}</label>
                  <SearchableSelect
                    v-if="field.type === 'select'"
                    v-model="filters.customAttributeFilters[field.key]"
                    :options="
                      (field.options || []).map((opt) => ({
                        value: opt,
                        label: String(opt),
                      }))
                    "
                    :placeholder="$t('translation.leads.all')"
                    :allow-clear="true"
                  />
                  <input
                    v-else-if="field.type === 'number'"
                    v-model="filters.customAttributeFilters[field.key]"
                    type="number"
                    class="form-control form-control-sm"
                    step="any"
                    :placeholder="field.label"
                  />
                  <input
                    v-else
                    v-model="filters.customAttributeFilters[field.key]"
                    type="text"
                    class="form-control form-control-sm"
                    :placeholder="field.label"
                  />
                </div>
              </template>
            </div>
          </div>

          <!-- <Link :href="route('admin.leads.settings')" class="btn btn-info">
            <i class="fas fa-gear"></i> {{ $t("translation.leads.settings") }}
          </Link> -->
          <!-- <div class="dropdown">
            <button
              class="btn btn-success"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="fas fa-plus"></i> {{ $t("translation.leads.create_lead") }}
            </button>
            <ul class="dropdown-menu">
              <li>
                <Link :href="route('admin.leads.create')" class="dropdown-item">
                  <i class="fas fa-plus"></i> {{ $t("translation.btn.add") }}
                </Link>
              </li>
              <li>
                <Link :href="route('admin.leads.import')" class="dropdown-item">
                  <i class="fa-regular fa-file-excel"></i>
                  {{ $t("translation.leads.import_from_excel") }}
                </Link>
              </li>
            </ul>
          </div> -->
        </div>
      </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div v-if="selectedLeadsCount > 0" class="card mb-3">
      <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <span class="fw-medium text-primary">
              <template v-if="selectedAllFiltered">
                Barcha {{ selectedLeadsCount }} ta lead tanlandi
              </template>
              <template v-else>
                {{ selectedLeadsCount }} {{ $t("translation.selected") }}
              </template>
            </span>
            <button @click="clearSelection" class="btn btn-sm btn-outline-secondary">
              {{ $t("translation.clear") }}
            </button>
          </div>
          <div class="d-flex gap-2">
            <button
              v-if="canSelectAllFiltered"
              type="button"
              @click="selectAllFilteredLeads"
              class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
            >
              <i class="ri-checkbox-multiple-line me-1"></i>
              Barchasini tanlash ({{ totalSelectableLeadsCount }})
            </button>
            <button
              @click="redirectToSendSms"
              :disabled="preparingSmsSelection"
              class="btn btn-sm btn-primary d-flex align-items-center gap-1"
            >
              <span
                v-if="preparingSmsSelection"
                class="spinner-border spinner-border-sm me-1"
              ></span>
              <i v-else class="ri-message-2-line me-1"></i>
              {{ preparingSmsSelection ? "Tayyorlanmoqda..." : $t("translation.sms.send") }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40px">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      :indeterminate.prop="isIndeterminate"
                    />
                  </div>
                </th>
                <th
                  v-for="col in orderedVisibleTableColumns"
                  :key="'th-' + (col.type === 'core' ? col.orderKey : col.field.key)"
                >
                  <template v-if="col.type === 'core'">{{
                    coreColumnHeader(col.orderKey)
                  }}</template>
                  <template v-else>{{ col.field.label }}</template>
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading" v-for="n in 10" :key="n">
                <td v-for="m in tableTotalColumnCount" :key="m">
                  <div class="placeholder-glow">
                    <span class="placeholder col-12"></span>
                  </div>
                </td>
              </tr>
              <tr
                v-else-if="leads.data && leads.data.length > 0"
                v-for="leadItem in leads.data"
                :key="leadItem.id"
                :class="{ 'lead-row-trashed': leadItem.deleted_at }"
              >
                <td>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="
                        selectedAllFiltered || selectedLeads.includes(leadItem.id)
                      "
                      @change="toggleSelectLead(leadItem.id)"
                    />
                  </div>
                </td>
                <td
                  v-for="col in orderedVisibleTableColumns"
                  :key="
                    leadItem.id +
                    '-' +
                    (col.type === 'core' ? col.orderKey : col.field.key)
                  "
                  :class="tdClassForLeadColumn(col)"
                  :title="
                    col.type === 'custom'
                      ? dynamicCellDisplay(leadItem, col.field).title || undefined
                      : undefined
                  "
                >
                  <template v-if="col.type === 'custom'">
                    {{ dynamicCellDisplay(leadItem, col.field).text }}
                  </template>
                  <template v-else-if="col.orderKey === 'id'">
                    {{ leadItem.id }}
                  </template>
                  <template v-else-if="col.orderKey === 'name'">
                    {{ leadItem.name }}
                    <span
                      v-if="leadItem.deleted_at"
                      class="badge bg-danger-subtle text-danger ms-2"
                    >
                      {{ $t("translation.deleted") }}
                    </span>
                    <span
                      v-if="leadItem.reminders && leadItem.reminders.length > 0"
                      class="badge bg-warning-subtle text-warning ms-1"
                      :title="$t('translation.leads.has_reminder')"
                    >
                      <i class="fa-solid fa-bell"></i>
                    </span>
                  </template>
                  <template v-else-if="col.orderKey === 'phone'">
                    <div class="d-flex flex-column align-items-start gap-1">
                      <a :href="'tel:' + leadItem.phone">{{ leadItem.phone }}</a>
                      <a
                        v-if="leadItem.phone2"
                        :href="'tel:' + leadItem.phone2"
                        class="small text-muted"
                      >
                        {{ leadItem.phone2 }}
                      </a>
                    </div>
                  </template>
                  <template v-else-if="col.orderKey === 'address'">
                    {{ leadItem.address || "-" }}
                  </template>
                  <template v-else-if="col.orderKey === 'chosen_time'">
                    <span class="badge bg-success-subtle text-success">
                      {{
                        leadItem.chosen_days
                          ? $t("translation.leads." + leadItem.chosen_days)
                          : $t("translation.leads.other")
                      }} </span
                    ><br />
                    <span class="badge bg-success-subtle text-success">
                      {{ leadItem.chosen_time ? formatTime(leadItem.chosen_time) : "-" }}
                    </span>
                  </template>
                  <template v-else-if="col.orderKey === 'teacher'">
                    <span v-if="leadItem.teacher">
                      {{ leadItem.teacher.name }}
                    </span>
                    <span v-else>-</span>
                  </template>
                  <template v-else-if="col.orderKey === 'board'">
                    <span v-if="leadItem.board">
                      {{ leadItem.board.name }}
                    </span>
                    <span v-else>-</span>
                  </template>
                  <template v-else-if="col.orderKey === 'course'">
                    <a
                      v-if="leadItem.course"
                      :href="route('admin.courses.show', leadItem.course.id)"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="link-primary text-decoration-none"
                    >
                      {{ leadItem.course.name }}
                    </a>
                    <span v-else>-</span>
                  </template>
                  <template v-else-if="col.orderKey === 'attend'">
                    <div class="form-check form-switch">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        :id="'attendSwitch' + leadItem.id"
                        :checked="leadItem.attend"
                        :disabled="Boolean(leadItem.deleted_at)"
                        @change="handleAttendToggle(leadItem)"
                      />
                    </div>
                  </template>
                  <template v-else-if="col.orderKey === 'date'">
                    <div
                      class="cursor-pointer d-flex flex-column"
                      :title="formatDate(leadItem.created_at, 'd.m.Y H:i')"
                    >
                      <span>{{ formatDate(leadItem.created_at) }}</span>
                      <small class="text-muted">{{
                        leadItem.causer?.name || "Causer not found"
                      }}</small>
                    </div>
                  </template>
                </td>
                <td class="text-nowrap">
                  <div class="dropdown">
                    <button
                      class="btn btn-light btn-sm"
                      type="button"
                      :id="'dropdownMenuButton' + leadItem.id"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                    >
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <ul
                      class="dropdown-menu dropdown-menu-end"
                      :aria-labelledby="'dropdownMenuButton' + leadItem.id"
                    >
                      <li v-if="!leadItem.deleted_at">
                        <a
                          class="dropdown-item"
                          href="#"
                          @click.prevent="handleShowLeadCalls(leadItem)"
                        >
                          <i class="fa-solid fa-phone me-2"></i>
                          {{ $t("translation.leads.add_new_call") }}
                        </a>
                      </li>
                      <li v-if="!leadItem.deleted_at">
                        <a
                          class="dropdown-item"
                          href="#"
                          @click.prevent="handleShowLeadComments(leadItem)"
                        >
                          <i class="fa-solid fa-comment me-2"></i>
                          {{ $t("translation.leads.add_new_comment") }}
                        </a>
                      </li>
                      <li>
                        <a
                          class="dropdown-item"
                          href="#"
                          @click.prevent="handleShowLead(leadItem)"
                        >
                          <i class="fa-solid fa-eye me-2"></i>
                          {{ $t("translation.show") }}
                        </a>
                      </li>
                      <li v-if="!leadItem.deleted_at && leadItem.teacher_id">
                        <a
                          class="dropdown-item"
                          href="#"
                          @click.prevent="handleSendTelegramToTeacher(leadItem)"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24" style="vertical-align: middle;">
                            <path d="M9.973 16.891l-.426 4.175c.608 0 .869-.262 1.183-.576l2.836-2.758 5.883 4.251c1.08.598 1.849.283 2.123-.998l3.853-18.081.001-.001c.314-1.455-.525-2.021-1.565-1.662L1.086 9.714c-1.439.53-1.423 1.291-.237 1.632l4.844 1.513 11.246-7.087c.531-.344 1.015-.154.617.219"/>
                          </svg>
                          {{ $t("translation.leads.send_telegram_message") }}
                        </a>
                      </li>
                      <li v-if="!leadItem.deleted_at"><hr class="dropdown-divider" /></li>
                      <li v-if="!leadItem.deleted_at">
                        <a
                          class="dropdown-item"
                          href="#"
                          @click.prevent="handleConvertToStudent(leadItem)"
                        >
                          <i class="fa-solid fa-user-graduate me-2"></i>
                          {{ $t("translation.leads.convert_to_student") }}
                        </a>
                      </li>
                      <li v-if="!leadItem.deleted_at">
                        <a
                          class="dropdown-item text-danger"
                          href="#"
                          @click.prevent="handleDeleteLead(leadItem)"
                        >
                          <i class="fa-solid fa-trash me-2"></i>
                          {{ $t("translation.delete") }}
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
              <tr v-else>
                <td :colspan="tableTotalColumnCount" class="text-center">
                  {{ $t("translation.no_data") }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="p-3 border-top">
          <nav v-if="leads.last_page > 1" aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item" :class="{ disabled: leads.current_page === 1 }">
                <button
                  class="page-link"
                  @click="changePage(leads.current_page - 1)"
                  :disabled="leads.current_page === 1"
                >
                  {{ $t("translation.previous") }}
                </button>
              </li>
              <li
                v-for="page in getPaginationPages()"
                :key="page"
                class="page-item"
                :class="{ active: page === leads.current_page }"
              >
                <button class="page-link" @click="changePage(page)">{{ page }}</button>
              </li>
              <li
                class="page-item"
                :class="{ disabled: leads.current_page === leads.last_page }"
              >
                <button
                  class="page-link"
                  @click="changePage(leads.current_page + 1)"
                  :disabled="leads.current_page === leads.last_page"
                >
                  {{ $t("translation.next") }}
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <!-- Loading Overlay -->
      <div v-if="loading" class="card-preloader">
        <div class="card-status">
          <div class="spinner-border text-success">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Lead Show Modal -->
    <LeadShow
      v-if="showLeadDetails.visible"
      :lead="showLeadDetails.lead"
      :initial-tab="showLeadDetails.activeTab"
      @close="handleCloseLead"
      @edit="handleEditLead"
      @delete="handleDeleteLead"
      @convert="handleConvertToStudent"
    />

    <!-- Edit Lead Modal -->
    <EditLead
      v-if="showEditLead"
      :lead="editingLead"
      :collections="props.collections || []"
      :teachers="teachers"
      :boards="boards"
      :courses="props.courses || []"
      :lead-dynamic-fields="props.leadDynamicFields || []"
      @close="handleCloseEditLead"
      @updated="handleLeadUpdated"
    />

    <!-- Delete Confirmation Modal -->
    <DeleteConfirmModal
      :visible="showDeleteConfirm"
      :lead="deletingLead"
      ref="deleteModalRef"
      @confirm="handleDeleteConfirm"
      @close="handleDeleteCancel"
    />

    <!-- Convert to Student Modal -->
    <ConvertToStudent
      :visible="showConvertModal"
      :lead="convertingLead"
      @close="handleCloseConvertModal"
      @converted="handleConvertSuccess"
    />

    <!-- Date Picker Modal -->
    <div
      v-if="showDatePicker"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t("translation.leads.select_date_range") }}</h5>
            <button
              type="button"
              class="btn-close"
              @click="showDatePicker = false"
            ></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <label class="form-label">{{ $t("translation.leads.start_date") }}</label>
                <input
                  type="date"
                  class="form-control"
                  v-model="tempDateRange.start"
                  :max="tempDateRange.end"
                />
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ $t("translation.leads.end_date") }}</label>
                <input
                  type="date"
                  class="form-control"
                  v-model="tempDateRange.end"
                  :min="tempDateRange.start"
                />
              </div>
            </div>
            <div class="mt-3">
              <div class="d-flex gap-2 flex-wrap">
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="setQuickDate('today')"
                >
                  {{ $t("translation.leads.today") }}
                </button>
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="setQuickDate('yesterday')"
                >
                  {{ $t("translation.leads.yesterday") }}
                </button>
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="setQuickDate('this_week')"
                >
                  {{ $t("translation.leads.this_week") }}
                </button>
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="setQuickDate('last_week')"
                >
                  {{ $t("translation.leads.last_week") }}
                </button>
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="setQuickDate('this_month')"
                >
                  {{ $t("translation.leads.this_month") }}
                </button>
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="setQuickDate('last_month')"
                >
                  {{ $t("translation.leads.last_month") }}
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="showDatePicker = false"
            >
              {{ $t("translation.cancel") }}
            </button>
            <button type="button" class="btn btn-primary" @click="applyDateRange">
              {{ $t("translation.apply") }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ref,
  onMounted,
  watch,
  computed,
  reactive,
  nextTick,
  defineAsyncComponent,
  getCurrentInstance,
} from "vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import axios from "axios";
import SearchableSelect from "./SearchableSelect.vue";
import { useLeadsTableColumnVisibility, LEADS_TABLE_CORE_COLUMN_KEYS } from "../composables/useLeadsTableColumnVisibility";

// Use the same components as Index.vue
const LeadShow = defineAsyncComponent(() => import("../Show.vue"));
const EditLead = defineAsyncComponent(() => import("./EditLead.vue"));
const DeleteConfirmModal = defineAsyncComponent(() => import("./DeleteConfirmModal.vue"));
const ConvertToStudent = defineAsyncComponent(() => import("./ConvertToStudent.vue"));

// Get current instance for accessing $t
const { proxy } = getCurrentInstance();

// Props
const props = defineProps({
  initialLeads: Object,
  collections: Array,
  teachers: Array,
  boards: Array,
  causers: Array,
  courses: Array,
  initialSearch: String,
  leadDynamicFields: {
    type: Array,
    default: () => [],
  },
});

const {
  hydrateLeadsTableColumnVisibility,
  isLeadsTableColumnVisible,
  leadsTableColumnOrder,
} = useLeadsTableColumnVisibility();

const dynamicFieldByKey = computed(() =>
  Object.fromEntries((props.leadDynamicFields || []).map((f) => [f.key, f])),
);

const orderedVisibleTableColumns = computed(() => {
  const dynMap = dynamicFieldByKey.value;
  /** @type {Array<{ type: 'core', orderKey: string } | { type: 'custom', orderKey: string, field: object }>} */
  const out = [];
  for (const orderKey of leadsTableColumnOrder.value) {
    if (orderKey.startsWith("custom:")) {
      const raw = orderKey.slice("custom:".length);
      const field = dynMap[raw];
      if (field && isLeadsTableColumnVisible(orderKey)) {
        out.push({ type: "custom", orderKey, field });
      }
    } else if (
      LEADS_TABLE_CORE_COLUMN_KEYS.includes(orderKey) &&
      isLeadsTableColumnVisible(orderKey)
    ) {
      out.push({ type: "core", orderKey });
    }
  }
  return out;
});

const visibleDataColumnCount = computed(() => orderedVisibleTableColumns.value.length);

const coreColumnHeader = (key) => {
  const t = proxy.$t.bind(proxy);
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

/**
 * @param {{ type: string, orderKey?: string, field?: { key: string } }} col
 */
const tdClassForLeadColumn = (col) => {
  if (col.type === "custom") {
    return "text-break";
  }
  if (["phone", "teacher", "board"].includes(col.orderKey)) {
    return "text-nowrap";
  }
  return "";
};

const tableTotalColumnCount = computed(() => 1 + visibleDataColumnCount.value + 1);

/**
 * @param {Record<string, unknown>} leadItem
 * @param {{ key: string, type: string }} field
 */
const dynamicCellDisplay = (leadItem, field) => {
  const raw = leadItem.custom_attributes?.[field.key];
  if (raw === null || raw === undefined || raw === "") {
    return { text: "-", title: "" };
  }
  const s = String(raw);
  if (field.type === "textarea") {
    const max = 100;
    const truncated = s.length > max ? `${s.slice(0, max)}…` : s;
    return { text: truncated, title: s };
  }
  return { text: s, title: "" };
};

watch(
  () => props.leadDynamicFields,
  () => hydrateLeadsTableColumnVisibility(props.leadDynamicFields),
  { deep: true },
);

hydrateLeadsTableColumnVisibility(props.leadDynamicFields);

// Reactive data
const leads = ref(props.initialLeads || {});
const loading = ref(false);
const showDatePicker = ref(false);
const selectedLeads = ref([]);
const selectedAllFiltered = ref(false);
const preparingSmsSelection = ref(false);

// Modal states matching Index.vue
const showLeadDetails = reactive({
  visible: false,
  lead: null,
  activeTab: "details",
});

const showEditLead = ref(false);
const editingLead = ref(null);

const showDeleteConfirm = ref(false);
const deletingLead = ref(null);
const deleteModalRef = ref(null);

const showConvertModal = ref(false);
const convertingLead = ref(null);

// Temporary date range for the picker
const tempDateRange = ref({
  start: new Date().toISOString().slice(0, 10),
  end: new Date().toISOString().slice(0, 10),
});

// Helper function to format date as Y.m.d
const formatDateForBackend = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}.${month}.${day}`;
};

// Get today's date in the correct format
const todayFormatted = formatDateForBackend(new Date());

// Filters
const filters = ref({
  search: props.initialSearch || "",
  selectedChosenDays: "",
  selectedTeacher: "",
  selectedBoard: "",
  selectedCourse: "",
  selectedCauser: "",
  selectedAttend: "",
  includeDeleted: false,
  date: `${todayFormatted} - ${todayFormatted}`,
  page: 1,
  customAttributeFilters: {},
});

const isHydratingCustomFilters = ref(false);

const syncCustomAttributeFilterKeys = () => {
  const cur = filters.value.customAttributeFilters || {};
  const next = { ...cur };
  let changed = false;
  for (const f of props.leadDynamicFields || []) {
    if (!(f.key in next)) {
      next[f.key] = "";
      changed = true;
    }
  }
  if (changed) {
    filters.value.customAttributeFilters = next;
  }
};

const buildTableRequestParams = () => {
  const f = filters.value;
  const params = {
    search: f.search,
    selectedChosenDays: f.selectedChosenDays,
    selectedTeacher: f.selectedTeacher,
    selectedBoard: f.selectedBoard,
    selectedCourse: f.selectedCourse,
    selectedCauser: f.selectedCauser,
    selectedAttend: f.selectedAttend,
    includeDeleted: f.includeDeleted ? 1 : 0,
    date: f.date,
    page: f.page,
  };
  Object.entries(f.customAttributeFilters || {}).forEach(([key, val]) => {
    if (val !== "" && val !== null && val !== undefined) {
      params[`customAttributeFilters[${key}]`] = val;
    }
  });

  return params;
};

// Computed
const teachers = computed(() => props.teachers || []);
const boards = computed(() => props.boards || []);
const courses = computed(() => props.courses || []);
const causers = computed(() => props.causers || []);

// Computed options for searchable selects
const chosenDaysOptions = computed(() => [
  { value: "", label: proxy.$t("translation.leads.all") },
  { value: "odd", label: proxy.$t("translation.leads.odd") },
  { value: "even", label: proxy.$t("translation.leads.even") },
]);

const teacherOptions = computed(() => [
  {
    value: "",
    label:
      proxy.$t("translation.leads.all") + " " + proxy.$t("translation.leads.teacher"),
  },
  ...teachers.value.map((teacher) => ({
    value: teacher.id,
    label: teacher.name,
  })),
]);

const boardOptions = computed(() => [
  {
    value: "",
    label: proxy.$t("translation.leads.all") + " " + proxy.$t("translation.leads.board"),
  },
  ...boards.value.map((board) => ({
    value: board.id,
    label: board.name,
  })),
]);

const courseOptions = computed(() => [
  {
    value: "",
    label:
      proxy.$t("translation.leads.all") + " " + proxy.$t("translation.courses.course"),
  },
  ...courses.value.map((course) => ({
    value: course.id,
    label: course.name,
  })),
]);

const causerOptions = computed(() => [
  {
    value: "",
    label: proxy.$t("translation.leads.all") + " " + proxy.$t("translation.leads.causer"),
  },
  ...causers.value.map((causer) => ({
    value: causer.id,
    label: causer.name,
  })),
]);

const attendOptions = computed(() => [
  { value: "", label: proxy.$t("translation.leads.all") },
  { value: "1", label: proxy.$t("translation.leads.attend_yes") },
  { value: "0", label: proxy.$t("translation.leads.attend_no") },
]);

// Selection computed properties
const currentPageSelectableLeadIds = computed(() =>
  (leads.value.data || []).map((lead) => lead.id)
);

const totalSelectableLeadsCount = computed(() =>
  Number(leads.value.selectable_total ?? leads.value.total ?? 0)
);

const isAllSelected = computed(() => {
  if (selectedAllFiltered.value) {
    return currentPageSelectableLeadIds.value.length > 0;
  }

  const availableLeadIds = currentPageSelectableLeadIds.value;
  return (
    availableLeadIds.length > 0 &&
    availableLeadIds.every((id) => selectedLeads.value.includes(id))
  );
});

const isIndeterminate = computed(() => {
  if (selectedAllFiltered.value) {
    return false;
  }

  const availableLeadIds = currentPageSelectableLeadIds.value;
  const selectedCount = availableLeadIds.filter((id) => selectedLeads.value.includes(id))
    .length;
  return selectedCount > 0 && selectedCount < availableLeadIds.length;
});

const selectedLeadsCount = computed(() =>
  selectedAllFiltered.value ? totalSelectableLeadsCount.value : selectedLeads.value.length
);

const canSelectAllFiltered = computed(
  () =>
    !selectedAllFiltered.value &&
    selectedLeads.value.length > 0 &&
    totalSelectableLeadsCount.value > selectedLeads.value.length
);

const activeCustomFilterCount = computed(() => {
  let n = 0;
  Object.values(filters.value.customAttributeFilters || {}).forEach((v) => {
    if (v !== "" && v !== null && v !== undefined) {
      n++;
    }
  });

  return n;
});

const secondaryFiltersActiveCount = computed(() => {
  const f = filters.value;
  let n = 0;
  if (f.selectedBoard) {
    n++;
  }
  if (f.selectedCauser) {
    n++;
  }
  if (f.selectedAttend) {
    n++;
  }
  if (f.includeDeleted) {
    n++;
  }

  return n;
});

const moreFiltersMenuBadgeCount = computed(
  () => secondaryFiltersActiveCount.value + activeCustomFilterCount.value
);

const clearSecondaryFilters = () => {
  resetSelectionForFilterChange();
  filters.value.selectedBoard = "";
  filters.value.selectedCauser = "";
  filters.value.selectedAttend = "";
  filters.value.includeDeleted = false;
  filters.value.page = 1;
};

const selectedLeadsData = computed(() => {
  return (leads.value.data || []).filter((lead) => selectedLeads.value.includes(lead.id));
});

// Methods
const debouncedSearch = debounce(() => {
  refreshAfterFilterChange();
}, 500);

const fetchLeads = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/admin/leads/table-data", {
      params: buildTableRequestParams(),
    });
    leads.value = response.data;
  } catch (error) {
    console.error("Error fetching leads:", error);
  } finally {
    loading.value = false;
  }
};

const refreshLeads = () => {
  fetchLeads();
};

const changePage = (page) => {
  if (page >= 1 && page <= leads.value.last_page) {
    filters.value.page = page;
    fetchLeads();
  }
};

const getPaginationPages = () => {
  const current = leads.value.current_page;
  const last = leads.value.last_page;
  const pages = [];

  // Show first page
  if (current > 3) pages.push(1);
  if (current > 4) pages.push("...");

  // Show pages around current
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i);
  }

  // Show last page
  if (current < last - 3) pages.push("...");
  if (current < last - 2) pages.push(last);

  return pages;
};

// Selection methods
const toggleSelectLead = (leadId) => {
  if (!currentPageSelectableLeadIds.value.includes(leadId)) {
    return;
  }

  selectedAllFiltered.value = false;

  const index = selectedLeads.value.indexOf(leadId);
  if (index > -1) {
    selectedLeads.value.splice(index, 1);
  } else {
    selectedLeads.value.push(leadId);
  }
};

const toggleSelectAll = () => {
  const availableLeadIds = currentPageSelectableLeadIds.value;

  if (selectedAllFiltered.value || isAllSelected.value) {
    clearSelection();

    return;
  }

  selectedLeads.value = [];
  selectedAllFiltered.value = availableLeadIds.length > 0;
};

const clearSelection = () => {
  selectedLeads.value = [];
  selectedAllFiltered.value = false;
};

const resetSelectionForFilterChange = () => {
  if (selectedLeads.value.length > 0 || selectedAllFiltered.value) {
    clearSelection();
  }
};

const refreshAfterFilterChange = () => {
  resetSelectionForFilterChange();
  filters.value.page = 1;
  fetchLeads();
};

const selectAllFilteredLeads = () => {
  selectedLeads.value = [];
  selectedAllFiltered.value = true;
};

// SMS redirect method
const redirectToSendSms = async () => {
  console.log("redirectToSendSms called!");
  console.log("Selected leads count:", selectedLeadsCount.value);

  if (selectedLeadsCount.value === 0) {
    console.log("No leads selected, returning");
    return;
  }

  if (selectedAllFiltered.value) {
    preparingSmsSelection.value = true;

    try {
      const response = await axios.post(
        "/admin/leads/prepare-sms-selection",
        buildTableRequestParams(),
        {
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content"),
            Accept: "application/json",
          },
        }
      );

      window.location.href = response.data.url;
    } catch (error) {
      console.error("Error preparing lead SMS selection:", error);
      if (window.notifyMessage) {
        const errorMessage =
          error.response?.data?.message || "Leadlar bo'yicha SMS ro'yxatini tayyorlab bo'lmadi";
        window.notifyMessage(errorMessage, "danger");
      }
    } finally {
      preparingSmsSelection.value = false;
    }

    return;
  }

  const sendSmsIds = JSON.stringify(selectedLeads.value);
  console.log("Selected leads:", selectedLeads.value);
  console.log("sendSmsIds:", sendSmsIds);

  // Build URL with query parameters
  const params = new URLSearchParams({
    sendSmsIds: sendSmsIds,
    sendSmsType: "leads",
  });

  const smsUrl = `/admin/sms/create?${params.toString()}`;
  console.log("Final SMS URL:", smsUrl);

  // Use window.location for direct navigation (more reliable)
  window.location.href = smsUrl;
};

// Action handlers matching Index.vue
const handleShowLead = (lead, activeTab = "details") => {
  showLeadDetails.lead = lead;
  showLeadDetails.visible = true;
  showLeadDetails.activeTab = activeTab;
};

const handleCloseLead = () => {
  showLeadDetails.visible = false;
  showLeadDetails.lead = null;
  showLeadDetails.activeTab = "details";
};

const handleShowLeadCalls = (lead) => {
  handleShowLead(lead, "calls");
};

const handleShowLeadComments = (lead) => {
  handleShowLead(lead, "comments");
};

const handleEditLead = (lead) => {
  editingLead.value = lead;
  showEditLead.value = true;
};

const handleCloseEditLead = () => {
  showEditLead.value = false;
  editingLead.value = null;
};

const handleLeadUpdated = (updatedLead) => {
  // Refresh the table data after update
  refreshLeads();
  handleCloseEditLead();
};

const handleDeleteLead = (lead) => {
  deletingLead.value = lead;
  showDeleteConfirm.value = true;
  // Close lead details modal if open
  if (showLeadDetails.visible) {
    handleCloseLead();
  }
};

const handleDeleteConfirm = async (lead) => {
  if (!lead) return;

  try {
    if (deleteModalRef.value) {
      deleteModalRef.value.setLoading(true);
    }

    const response = await axios.delete(`/admin/leads/${lead.id}`, {
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content"),
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });

    if (response.data.success) {
      // Show success message
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }

      // Refresh table data
      refreshLeads();
      handleDeleteCancel();
    }
  } catch (error) {
    console.error("Error deleting lead:", error);
    if (window.notifyMessage) {
      const errorMessage = error.response?.data?.message || "Failed to delete lead";
      window.notifyMessage(errorMessage, "danger");
    }
  } finally {
    if (deleteModalRef.value) {
      deleteModalRef.value.setLoading(false);
    }
  }
};

const handleDeleteCancel = () => {
  showDeleteConfirm.value = false;
  deletingLead.value = null;
};

const handleSendTelegramToTeacher = (lead) => {
  if (!lead.teacher_id) {
    if (window.notifyMessage) {
      window.notifyMessage(
        proxy.$t("translation.leads.no_teacher_assigned"),
        "warning"
      );
    }
    return;
  }

  router.post(
    `/admin/leads/${lead.id}/send-telegram-to-teacher`,
    {},
    {
      preserveState: true,
      preserveScroll: true,
      only: [],
      onSuccess: (page) => {
        if (window.notifyMessage) {
          const message = page.props?.flash?.success || 
                         proxy.$t("translation.alert_messages.sent");
          window.notifyMessage(message, "success");
        }
      },
      onError: (errors) => {
        console.error("Error sending telegram to teacher:", errors);
        if (window.notifyMessage) {
          const errorMessage = errors?.message || 
                              errors?.error || 
                              proxy.$t("translation.alert_messages.error");
          window.notifyMessage(errorMessage, "danger");
        }
      },
    }
  );
};

const handleConvertToStudent = (lead) => {
  convertingLead.value = lead;
  showConvertModal.value = true;
};

const handleCloseConvertModal = () => {
  showConvertModal.value = false;
  convertingLead.value = null;
};

const handleConvertSuccess = (lead) => {
  // Refresh table data after conversion
  refreshLeads();
  handleCloseConvertModal();
};

const handleAttendToggle = async (lead) => {
  try {
    const response = await axios.post(
      `/admin/leads/${lead.id}/toggle-attend`,
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

    if (response.data.success) {
      // Update the local lead object to reflect the new attend status
      lead.attend = response.data.attend;

      // Show success message
      if (window.notifyMessage) {
        const message = response.data.attend
          ? proxy.$t("translation.leads.attend_enabled")
          : proxy.$t("translation.leads.attend_disabled");
        window.notifyMessage(message, "success");
      }
    }
  } catch (error) {
    console.error("Error toggling attend status:", error);
    if (window.notifyMessage) {
      const errorMessage =
        error.response?.data?.message || "Failed to toggle attend status";
      window.notifyMessage(errorMessage, "danger");
    }

    // Revert the checkbox state by refreshing the data
    refreshLeads();
  }
};

const exportLeads = async () => {
  try {
    const response = await axios.get("/admin/leads/export", {
      params: buildTableRequestParams(),
      responseType: "blob",
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "leads.xlsx");
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error("Error exporting leads:", error);
  }
};

const formatDate = (date, format = "d.m.Y") => {
  // Simple date formatting - you can use a library like moment.js or date-fns
  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, "0");
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, "0");
  const minutes = String(d.getMinutes()).padStart(2, "0");

  if (format === "d.m.Y H:i") {
    return `${day}.${month}.${year} ${hours}:${minutes}`;
  }
  return `${day}.${month}.${year}`;
};

const formatTime = (time) => {
  // Format time (assuming time is in HH:mm format)
  return time || "-";
};

// Date picker methods
const setQuickDate = (type) => {
  const today = new Date();
  const formatDate = (date) => date.toISOString().slice(0, 10);

  switch (type) {
    case "today":
      tempDateRange.value.start = formatDate(today);
      tempDateRange.value.end = formatDate(today);
      break;
    case "yesterday":
      const yesterday = new Date(today);
      yesterday.setDate(yesterday.getDate() - 1);
      tempDateRange.value.start = formatDate(yesterday);
      tempDateRange.value.end = formatDate(yesterday);
      break;
    case "this_week":
      const startOfWeek = new Date(today);
      startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Monday
      tempDateRange.value.start = formatDate(startOfWeek);
      tempDateRange.value.end = formatDate(today);
      break;
    case "last_week":
      const lastWeekStart = new Date(today);
      lastWeekStart.setDate(today.getDate() - today.getDay() - 6); // Last Monday
      const lastWeekEnd = new Date(today);
      lastWeekEnd.setDate(today.getDate() - today.getDay()); // Last Sunday
      tempDateRange.value.start = formatDate(lastWeekStart);
      tempDateRange.value.end = formatDate(lastWeekEnd);
      break;
    case "this_month":
      const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
      tempDateRange.value.start = formatDate(startOfMonth);
      tempDateRange.value.end = formatDate(today);
      break;
    case "last_month":
      const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
      tempDateRange.value.start = formatDate(lastMonthStart);
      tempDateRange.value.end = formatDate(lastMonthEnd);
      break;
  }
};

const applyDateRange = () => {
  // Convert YYYY-MM-DD to YYYY.MM.DD format (backend expects Y.m.d)
  const start = tempDateRange.value.start.replace(/-/g, ".");
  const end = tempDateRange.value.end.replace(/-/g, ".");

  filters.value.date = `${start} - ${end}`;
  showDatePicker.value = false;
  refreshAfterFilterChange();
};

// Initialize temp date range when opening picker
const openDatePicker = () => {
  // Parse current date range back to YYYY-MM-DD format
  const currentRange = filters.value.date.split(" - ");
  if (currentRange.length === 2) {
    // Convert YYYY.MM.DD to YYYY-MM-DD for date inputs
    tempDateRange.value.start = currentRange[0].replace(/\./g, "-");
    tempDateRange.value.end = currentRange[1].replace(/\./g, "-");
  }
  showDatePicker.value = true;
};

// Watch for filter changes
watch(
  () => filters.value.selectedChosenDays,
  () => {
    refreshAfterFilterChange();
  }
);

watch(
  () => filters.value.selectedTeacher,
  () => {
    refreshAfterFilterChange();
  }
);

watch(
  () => filters.value.selectedBoard,
  () => {
    refreshAfterFilterChange();
  }
);

watch(
  () => filters.value.selectedCourse,
  () => {
    refreshAfterFilterChange();
  }
);

watch(
  () => filters.value.selectedCauser,
  () => {
    refreshAfterFilterChange();
  }
);

watch(
  () => filters.value.selectedAttend,
  () => {
    refreshAfterFilterChange();
  }
);

watch(
  () => filters.value.includeDeleted,
  () => {
    refreshAfterFilterChange();
  }
);

watch(
  () => props.leadDynamicFields,
  async () => {
    isHydratingCustomFilters.value = true;
    syncCustomAttributeFilterKeys();
    await nextTick();
    isHydratingCustomFilters.value = false;
  },
  { immediate: true, deep: true }
);

const debouncedCustomAttributeFetch = debounce(() => {
  refreshAfterFilterChange();
}, 500);

watch(
  () => filters.value.customAttributeFilters,
  () => {
    if (isHydratingCustomFilters.value) {
      return;
    }
    debouncedCustomAttributeFetch();
  },
  { deep: true }
);

// Initialize on mount
onMounted(() => {
  if (!props.initialLeads) {
    fetchLeads();
  }
});

defineExpose({
  exportLeads,
});
</script>

<style scoped>
.leads-table-container {
  padding: 0;
}

.leads-filters-card {
  overflow: visible;
}

.leads-filters-toolbar {
  overflow: visible;
}

.lead-row-trashed > td {
  background-color: rgba(220, 53, 69, 0.04);
}

@media (max-width: 991.98px) {
  .leads-filters-toolbar {
    flex-wrap: wrap;
  }
}

.leads-filter-search {
  flex: 1.15 1 10rem;
  min-width: 0;
}

.leads-filter-search .form-control {
  width: 100%;
}

.leads-inline-filter-select {
  flex: 1 1 0;
  min-width: 7.5rem;
}

.leads-inline-filter-select :deep(.searchable-select) {
  width: 100%;
}

:deep(.leads-more-filters-menu) {
  min-width: min(100vw - 2rem, 20rem);
  max-width: min(100vw - 2rem, 24rem);
  max-height: min(85vh, 36rem);
  overflow-y: auto;
  overflow-x: hidden;
  z-index: 2000 !important;
}

/* Compact searchable selects inside more-filters panel */
:deep(.leads-more-filters-menu .searchable-select .form-control) {
  min-height: 34px;
  height: 34px;
  padding: 0.25rem 0.5rem;
  font-size: 0.8125rem;
}

:deep(.leads-more-filters-menu .searchable-select .dropdown-menu) {
  z-index: 1060;
}

.leads-inline-filter-select :deep(.searchable-select .form-control) {
  min-height: 34px;
  height: 34px;
  padding: 0.25rem 0.5rem;
  font-size: 0.8125rem;
}

.leads-inline-filter-select :deep(.searchable-select .dropdown-menu) {
  z-index: 1060;
}

.leads-filters-card :deep(.card-body) {
  overflow: visible;
}

.card-preloader {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.card-status {
  display: flex;
  align-items: center;
  justify-content: center;
}

.cursor-pointer {
  cursor: pointer;
}

.table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

/* Fix dropdown positioning */
.table-responsive {
  overflow: visible !important;
}

.dropdown-menu {
  z-index: 1050 !important;
  position: absolute !important;
}

/* Ensure dropdowns are not clipped by table containers */
.card-body {
  overflow: visible !important;
}

.table-container {
  overflow: visible !important;
}

/* Fix dropdown positioning for last column */
.table td:last-child .dropdown-menu {
  right: 0;
  left: auto;
}

/* Ensure dropdown appears above table content */
.dropdown.show .dropdown-menu {
  display: block !important;
  z-index: 1050 !important;
}

/* Simple table styling */
.table th {
  background-color: #f8f9fa;
  font-weight: 600;
}
</style>
