<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { watchDebounced } from "@vueuse/core";

const props = defineProps({
  translations: Object,
  filters: Object,
  summary: Object,
  records: Object,
  teachers: {
    type: Array,
    default: () => [],
  },
  groups: {
    type: Array,
    default: () => [],
  },
});

const t = (key) => props.translations?.[key] ?? key;

const filterForm = ref({
  from: props.filters?.from ?? "",
  to: props.filters?.to ?? "",
  search: props.filters?.search ?? "",
  group_id: props.filters?.group_id ?? "",
  teacher_id: props.filters?.teacher_id ?? "",
});

const syncingFromServer = ref(false);
const daterangeApplyingFromPicker = ref(false);

const MOMENT_CDN = "https://cdn.jsdelivr.net/momentjs/latest/moment.min.js";
const DATERANGEPICKER_JS_CDN = "https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js";
const DATERANGEPICKER_CSS_CDN = "https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css";

const DATERANGE_BTN_ID = "attendance-detail-daterange";
const DATERANGE_LABEL_ID = "attendance-detail-daterange-label";

const PARTIAL_KEYS = ["summary", "records", "filters"];

const filteredGroups = computed(() => {
  const tid = filterForm.value.teacher_id;
  if (tid === "" || tid === null || tid === undefined) {
    return props.groups;
  }
  return props.groups.filter((g) => Number(g.teacher_id) === Number(tid));
});

function pushFilters() {
  if (syncingFromServer.value) {
    return;
  }
  const tid = filterForm.value.teacher_id;
  const gid = filterForm.value.group_id;
  router.get(
    route("admin.reports.educational.attendance-detail"),
    {
      from: filterForm.value.from || undefined,
      to: filterForm.value.to || undefined,
      search: filterForm.value.search || undefined,
      teacher_id: tid === "" || tid == null ? undefined : Number(tid),
      group_id: gid === "" || gid == null ? undefined : Number(gid),
      page: 1,
    },
    { preserveState: true, replace: true, only: PARTIAL_KEYS }
  );
}

watch(
  () => props.filters,
  (f) => {
    syncingFromServer.value = true;
    filterForm.value = {
      from: f?.from ?? "",
      to: f?.to ?? "",
      search: f?.search ?? "",
      group_id: f?.group_id ?? "",
      teacher_id: f?.teacher_id ?? "",
    };
    nextTick(() => {
      syncingFromServer.value = false;
      syncDaterangePickerFromForm();
    });
  },
  { deep: true }
);

watchDebounced(
  () => filterForm.value.search,
  () => {
    if (syncingFromServer.value) {
      return;
    }
    pushFilters();
  },
  { debounce: 400 }
);

function onTeacherChange() {
  const tid = filterForm.value.teacher_id;
  const gid = filterForm.value.group_id;
  if (gid !== "" && gid != null) {
    const g = props.groups.find((x) => x.id === Number(gid));
    if (!g || (tid !== "" && tid != null && Number(g.teacher_id) !== Number(tid))) {
      filterForm.value.group_id = "";
    }
  }
  pushFilters();
}

const goToPage = (url) => {
  if (!url) return;
  router.visit(url, { preserveState: true, only: PARTIAL_KEYS });
};

function loadStylesheetOnce(href) {
  if (document.querySelector(`link[href="${href}"]`)) {
    return;
  }
  const link = document.createElement("link");
  link.rel = "stylesheet";
  link.href = href;
  document.head.appendChild(link);
}

function loadScriptOnce(src) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`script[src="${src}"]`)) {
      resolve();
      return;
    }
    const script = document.createElement("script");
    script.src = src;
    script.async = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.body.appendChild(script);
  });
}

async function ensureDaterangeAssets() {
  loadStylesheetOnce(DATERANGEPICKER_CSS_CDN);
  await loadScriptOnce(MOMENT_CDN);
  const lang = document.documentElement.lang || "";
  const m = window.moment;
  if (m) {
    m.locale(lang.toLowerCase().startsWith("ru") ? "ru" : "uz-latn");
  }
  await loadScriptOnce(DATERANGEPICKER_JS_CDN);
}

function momentsFromForm() {
  const m = window.moment;
  if (!m) {
    return { start: null, end: null };
  }
  let start = filterForm.value.from ? m(filterForm.value.from, "YYYY-MM-DD", true) : null;
  let end = filterForm.value.to ? m(filterForm.value.to, "YYYY-MM-DD", true) : null;
  if (!start?.isValid() || !end?.isValid()) {
    start = m().subtract(29, "days");
    end = m();
  }
  return { start, end };
}

function setDaterangeLabel(startM, endM) {
  const el = document.getElementById(DATERANGE_LABEL_ID);
  if (!el || !startM?.isValid?.() || !endM?.isValid?.()) {
    return;
  }
  el.textContent = `${startM.format("YYYY.MM.DD")} - ${endM.format("YYYY.MM.DD")}`;
}

function destroyDaterangePicker() {
  const $ = window.jQuery;
  if (!$) {
    return;
  }
  const $btn = $(`#${DATERANGE_BTN_ID}`);
  if ($btn.length && $btn.data("daterangepicker")) {
    $btn.data("daterangepicker").remove();
  }
}

function syncDaterangePickerFromForm() {
  if (daterangeApplyingFromPicker.value) {
    return;
  }
  const $ = window.jQuery;
  const m = window.moment;
  if (!$ || !m || !$(`#${DATERANGE_BTN_ID}`).length) {
    return;
  }
  const $btn = $(`#${DATERANGE_BTN_ID}`);
  const drp = $btn.data("daterangepicker");
  if (!drp) {
    return;
  }
  const { start, end } = momentsFromForm();
  if (!start.isSame(drp.startDate, "day")) {
    drp.setStartDate(start);
  }
  if (!end.isSame(drp.endDate, "day")) {
    drp.setEndDate(end);
  }
  setDaterangeLabel(start, end);
}

function applyDateRangeAndFilter(start, end) {
  daterangeApplyingFromPicker.value = true;
  filterForm.value.from = start.format("YYYY-MM-DD");
  filterForm.value.to = end.format("YYYY-MM-DD");
  setDaterangeLabel(start, end);
  nextTick(() => {
    daterangeApplyingFromPicker.value = false;
    pushFilters();
  });
}

async function initDaterangePicker() {
  await nextTick();
  await ensureDaterangeAssets();
  const $ = window.jQuery;
  const m = window.moment;
  if (!$?.fn?.daterangepicker || !m) {
    return;
  }

  const $btn = $(`#${DATERANGE_BTN_ID}`);
  if (!$btn.length) {
    return;
  }

  destroyDaterangePicker();

  const { start: initialStart, end: initialEnd } = momentsFromForm();
  if (!filterForm.value.from || !filterForm.value.to) {
    filterForm.value.from = initialStart.format("YYYY-MM-DD");
    filterForm.value.to = initialEnd.format("YYYY-MM-DD");
  }

  const ranges = {};
  ranges[t("attr_today")] = [m(), m()];
  ranges[t("attr_yesterday")] = [m().subtract(1, "days"), m().subtract(1, "days")];
  ranges[t("attr_last7days")] = [m().subtract(6, "days"), m()];
  ranges[t("attr_last30days")] = [m().subtract(29, "days"), m()];
  ranges[t("attr_thisMonth")] = [m().startOf("month"), m().endOf("month")];
  ranges[t("attr_lastMonth")] = [m().subtract(1, "month").startOf("month"), m().subtract(1, "month").endOf("month")];
  ranges[t("attr_thisYear")] = [m().startOf("year"), m().endOf("year")];
  ranges[t("attr_lastYear")] = [m().subtract(1, "year").startOf("year"), m().subtract(1, "year").endOf("year")];

  const pickStart = filterForm.value.from ? m(filterForm.value.from, "YYYY-MM-DD") : initialStart;
  const pickEnd = filterForm.value.to ? m(filterForm.value.to, "YYYY-MM-DD") : initialEnd;

  $btn.daterangepicker(
    {
      ranges,
      startDate: pickStart,
      endDate: pickEnd,
      locale: {
        format: "DD.MM.YYYY",
        separator: " - ",
        customRangeLabel: t("attr_optional_date"),
        applyLabel: t("attr_save"),
        cancelLabel: t("attr_cancel"),
      },
    },
    (start, end) => applyDateRangeAndFilter(start, end)
  );

  setDaterangeLabel(pickStart, pickEnd);
}

watch(
  () => [filterForm.value.from, filterForm.value.to],
  () => {
    syncDaterangePickerFromForm();
  }
);

watch(
  () => [props.filters?.from, props.filters?.to],
  () => {
    nextTick(() => syncDaterangePickerFromForm());
  }
);

onMounted(() => {
  initDaterangePicker();
});

onBeforeUnmount(() => {
  destroyDaterangePicker();
});
</script>

<template>
  <Head :title="t('reports_attendance_detail')" />

  <div class="attendance-detail-page">
    <div class="mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
      <Link
        :href="
          route('admin.reports.educational', {
            ...(props.filters?.from && { from: props.filters.from }),
            ...(props.filters?.to && { to: props.filters.to }),
          })
        "
        class="btn btn-outline-secondary btn-sm"
      >
        <i class="fas fa-arrow-left me-1"></i>
        {{ t("reports_back_to_educational") }}
      </Link>
    </div>

    <h4 class="mb-3">{{ t("reports_attendance_detail") }}</h4>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card card-animate mb-0 rounded-4 border-0 shadow-sm">
          <div class="card-body">
            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("total") }}</p>
            <h4 class="mb-0">{{ summary?.total ?? 0 }}</h4>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-animate mb-0 rounded-4 border-0 shadow-sm">
          <div class="card-body">
            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("comers") }}</p>
            <h4 class="mb-0 text-success">{{ summary?.present ?? 0 }}</h4>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-animate mb-0 rounded-4 border-0 shadow-sm">
          <div class="card-body">
            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("not_comers") }}</p>
            <h4 class="mb-0 text-warning">{{ summary?.absent ?? 0 }}</h4>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-animate mb-0 rounded-4 border-0 shadow-sm">
          <div class="card-body">
            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("percentage") }}</p>
            <h4 class="mb-0">{{ summary?.percentage ?? 0 }}%</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="card rounded-4 border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="attendance-filter-grid">
          <div class="attendance-filter-item">
            <label class="form-label small mb-1 text-muted">{{ t("users_select_teacher") }}</label>
            <select
              v-model="filterForm.teacher_id"
              name="teacher_id"
              class="form-select form-select-sm"
              @change="onTeacherChange"
            >
              <option value="">{{ t("all_teachers") }}</option>
              <option v-for="u in teachers" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div class="attendance-filter-item">
            <label class="form-label small mb-1 text-muted">{{ t("groups_select_group") }}</label>
            <select
              v-model="filterForm.group_id"
              name="group_id"
              class="form-select form-select-sm"
              @change="pushFilters"
            >
              <option value="">{{ t("all_groups") }}</option>
              <option v-for="g in filteredGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>
          <div class="attendance-filter-item">
            <label class="form-label small mb-1 text-muted">{{ t("search") }}</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
              <input
                v-model="filterForm.search"
                type="text"
                class="form-control border-start-0"
                name="search"
                :placeholder="t('search')"
              />
            </div>
          </div>
          <div class="attendance-filter-item">
            <label class="form-label small mb-1 text-muted">{{ t("attr_date") }}</label>
            <button
              :id="DATERANGE_BTN_ID"
              type="button"
              class="btn bg-white border btn-sm w-100 text-start d-flex align-items-center justify-content-between"
            >
              <span class="d-inline-flex align-items-center gap-1 text-truncate">
                <i class="far fa-calendar-alt text-muted flex-shrink-0"></i>
                <span :id="DATERANGE_LABEL_ID" class="text-truncate"></span>
              </span>
              <i class="fas fa-caret-down text-muted flex-shrink-0 ms-1"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-centered table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="fw-semibold">{{ t("students_full_name") }}</th>
                <th class="fw-semibold">{{ t("groups_group") }}</th>
                <th class="fw-semibold">{{ t("students_phone") }}</th>
                <th class="text-center fw-semibold">{{ t("comers") }}</th>
                <th class="text-center fw-semibold">{{ t("not_comers") }}</th>
                <th class="text-center fw-semibold">{{ t("total") }}</th>
                <th class="text-center fw-semibold">{{ t("rate") }}</th>
                <th class="text-center fw-semibold">{{ t("actions") }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in records?.data ?? []" :key="`${row.student_id}-${row.group_id}`">
                <td>
                  <Link :href="route('admin.students.show', row.student_id)" class="text-dark text-decoration-none">
                    {{ row.student_name || "—" }}
                  </Link>
                </td>
                <td>{{ row.group_name }}</td>
                <td>{{ row.student_phone || "—" }}</td>
                <td class="text-center">
                  <span
                    v-if="(row.present_count ?? 0) > 0"
                    class="badge rounded-3 px-2 py-1 bg-warning-subtle text-warning"
                  >
                    {{ row.present_count }}
                  </span>
                  <span v-else class="badge rounded-3 px-2 py-1 bg-light text-muted">0</span>
                </td>
                <td class="text-center">
                  <span
                    v-if="(row.absent_count ?? 0) > 0"
                    class="badge rounded-3 px-2 py-1 bg-danger-subtle text-danger"
                  >
                    {{ row.absent_count }}
                  </span>
                  <span v-else class="badge rounded-3 px-2 py-1 bg-light text-muted">0</span>
                </td>
                <td class="text-center">
                  <span class="badge rounded-3 px-2 py-1 bg-primary-subtle text-primary">{{ row.total_count }}</span>
                </td>
                <td class="text-center" style="min-width: 120px">
                  <div class="progress mx-auto" style="height: 20px; max-width: 140px">
                    <div
                      :class="[
                        'progress-bar',
                        (row.rate ?? 0) >= 75 ? 'bg-success' : (row.rate ?? 0) >= 50 ? 'bg-warning' : 'bg-danger',
                      ]"
                      :style="{ width: (row.rate ?? 0) + '%' }"
                    >
                      {{ row.rate ?? 0 }}%
                    </div>
                  </div>
                </td>
                <td class="text-center">
                  <Link
                    :href="route('admin.students.show', row.student_id)"
                    class="btn btn-sm btn-primary rounded-3 px-2"
                    :title="t('show')"
                  >
                    <i class="fas fa-eye"></i>
                  </Link>
                </td>
              </tr>
              <tr v-if="!(records?.data?.length > 0)">
                <td colspan="8" class="text-center py-5 text-muted">{{ t("no_data") }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div
        v-if="records?.links?.length"
        class="card-footer bg-white border-top-0 d-flex flex-wrap justify-content-center align-items-center gap-2 py-3"
      >
        <template v-for="(link, i) in records.links" :key="i">
          <button
            v-if="link.url"
            type="button"
            class="btn btn-sm flex-shrink-0 rounded-pill px-3"
            :class="link.active ? 'btn-primary' : 'btn-outline-secondary'"
            :disabled="!link.url"
            v-html="link.label"
            @click="goToPage(link.url)"
          />
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.attendance-detail-page {
  padding-bottom: 1.5rem;
}

.attendance-filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.75rem;
  align-items: end;
}
</style>
