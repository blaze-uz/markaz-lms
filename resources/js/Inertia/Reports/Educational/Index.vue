<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  translations: Object,
  filters: Object,
});

const t = (key) => props.translations?.[key] ?? key;

const filterForm = ref({
  from: props.filters?.from ?? "",
  to: props.filters?.to ?? "",
});

// --- Cards ---
const cards = ref(null);
const cardsLoading = ref(true);

// --- Charts ---
const attendanceChart = ref(null);
const homeworkChart = ref(null);
const studentsChart = ref(null);
const studentsAgeChart = ref(null);
const studentsGenderChart = ref(null);
const chartsLoading = ref(true);

// --- Telegram ---
const telegramStats = ref(null);
const telegramLoading = ref(true);
const telegramExpanded = ref(false);
const telegramSortField = ref("connection_percentage");
const telegramSortDirection = ref("desc");

// --- Group Attendance ---
const groupAttendanceDate = ref(new Date().toISOString().slice(0, 10));
const groupAttendanceSearch = ref("");
const groupAttendanceLoading = ref(false);
const groupAttendanceData = ref({ groups: [], groupsNotExist: [] });

// --- Homework ---
const homeworkSearch = ref("");
const homeworkGroupId = ref("");
const homeworkTeacherId = ref("");
const homeworkDateFrom = ref(props.filters?.from ?? "");
const homeworkDateTo = ref(props.filters?.to ?? "");
const homeworkLoading = ref(true);
const homeworkData = ref({ stats: {}, students: { data: [] }, groups: [], teachers: [] });
const homeworkDaterangeBootstrapping = ref(false);
const homeworkDaterangeApplyingFromPicker = ref(false);

const MOMENT_CDN = "https://cdn.jsdelivr.net/momentjs/latest/moment.min.js";
const DATERANGEPICKER_JS_CDN = "https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js";
const DATERANGEPICKER_CSS_CDN = "https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css";

const HOMEWORK_DATERANGE_BTN_ID = "educational-homework-daterange";
const HOMEWORK_DATERANGE_LABEL_ID = "educational-homework-daterange-label";

// --- User Calls ---
const userCallsSearch = ref("");
const userCallsLoading = ref(true);
const userCallsData = ref([]);

// --- Call Breakdown ---
const callBreakdownModal = ref({ show: false, row: null });
const showCallBreakdown = (row) => {
  callBreakdownModal.value = { show: true, row };
};

const telegramStudentsModal = ref({
  show: false,
  loading: false,
  groupName: "",
  students: [],
});

const closeTelegramStudentsModal = () => {
  telegramStudentsModal.value = {
    show: false,
    loading: false,
    groupName: "",
    students: [],
  };
};

const openTelegramStudentsModal = async (groupId, groupName) => {
  telegramStudentsModal.value = {
    show: true,
    loading: true,
    groupName: groupName || "",
    students: [],
  };
  try {
    const { data } = await axios.get(route("admin.reports.educational.telegram-group-students"), {
      params: { group_id: groupId },
    });
    telegramStudentsModal.value.groupName = data?.group?.name || groupName || "";
    telegramStudentsModal.value.students = Array.isArray(data?.students) ? data.students : [];
  } catch {
    telegramStudentsModal.value.students = [];
  } finally {
    telegramStudentsModal.value.loading = false;
  }
};

const filterParams = computed(() => ({ from: props.filters?.from, to: props.filters?.to }));

watch(
  () => [props.filters?.from, props.filters?.to],
  ([from, to]) => {
    filterForm.value = { from: from ?? "", to: to ?? "" };
  }
);

const applyFilters = () => {
  router.get(route("admin.reports.educational"), filterForm.value, { preserveState: true });
};

const exportAttendance = () => {
  window.location.href = route("admin.reports.educational.export-attendance") + "?" + new URLSearchParams(props.filters).toString();
};

const exportStudents = () => {
  window.location.href = route("admin.reports.educational.export-students") + "?" + new URLSearchParams(props.filters).toString();
};

const loadCards = async () => {
  cardsLoading.value = true;
  try {
    const { data } = await axios.get(route("admin.reports.educational.cards"), { params: filterParams.value });
    cards.value = data;
  } finally {
    cardsLoading.value = false;
  }
};

const loadCharts = async () => {
  chartsLoading.value = true;
  try {
    const { data } = await axios.get(route("admin.reports.educational.charts"), { params: filterParams.value });
    attendanceChart.value = data.attendanceChart;
    homeworkChart.value = data.homeworkChart;
    studentsChart.value = data.studentsChart;
    studentsAgeChart.value = data.studentsAgeChart;
    studentsGenderChart.value = data.studentsGenderChart;
  } finally {
    chartsLoading.value = false;
  }
};

const loadTelegramStats = async () => {
  telegramLoading.value = true;
  try {
    const { data } = await axios.get(route("admin.reports.educational.telegram-stats"));
    telegramStats.value = data;
  } finally {
    telegramLoading.value = false;
  }
};

const loadGroupAttendance = async () => {
  groupAttendanceLoading.value = true;
  try {
    const { data } = await axios.get(route("admin.reports.educational.group-attendance"), {
      params: { date: groupAttendanceDate.value, search: groupAttendanceSearch.value },
    });
    groupAttendanceData.value = {
      groups: Array.isArray(data?.groups) ? data.groups : [],
      groupsNotExist: Array.isArray(data?.groupsNotExist) ? data.groupsNotExist : [],
    };
  } catch (error) {
    console.error("Error loading group attendance:", error);
    groupAttendanceData.value = { groups: [], groupsNotExist: [] };
  } finally {
    groupAttendanceLoading.value = false;
  }
};

const loadHomeworkReport = async (page = 1) => {
  homeworkLoading.value = true;
  try {
    const { data } = await axios.get(route("admin.reports.educational.homework-report"), {
      params: {
        ...filterParams.value,
        homework_from: homeworkDateFrom.value || undefined,
        homework_to: homeworkDateTo.value || undefined,
        search: homeworkSearch.value,
        group_id: homeworkGroupId.value || undefined,
        teacher_id: homeworkTeacherId.value || undefined,
        page,
      },
    });
    homeworkData.value = {
      stats: data?.stats || {},
      students: {
        data: Array.isArray(data?.students?.data) ? data.students.data : [],
        links: Array.isArray(data?.students?.links) ? data.students.links : [],
      },
      groups: Array.isArray(data?.groups) ? data.groups : [],
      teachers: Array.isArray(data?.teachers) ? data.teachers : [],
    };
  } catch (error) {
    console.error("Error loading homework report:", error);
    homeworkData.value = { stats: {}, students: { data: [], links: [] }, groups: [], teachers: [] };
  } finally {
    homeworkLoading.value = false;
  }
};

const loadUserCallsReport = async () => {
  userCallsLoading.value = true;
  try {
    const { data } = await axios.get(route("admin.reports.educational.user-calls-report"), {
      params: {
        ...filterParams.value,
        search: userCallsSearch.value,
      },
    });
    userCallsData.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error("Error loading user calls report:", error);
    userCallsData.value = [];
  } finally {
    userCallsLoading.value = false;
  }
};

const homeworkPage = (url) => {
  if (!url) return;
  const match = url.match(/page=(\d+)/);
  if (match) loadHomeworkReport(parseInt(match[1]));
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

async function ensureHomeworkDaterangeAssets() {
  loadStylesheetOnce(DATERANGEPICKER_CSS_CDN);
  await loadScriptOnce(MOMENT_CDN);
  const lang = document.documentElement.lang || "";
  const m = window.moment;
  if (m) {
    m.locale(lang.toLowerCase().startsWith("ru") ? "ru" : "en");
  }
  await loadScriptOnce(DATERANGEPICKER_JS_CDN);
}

function homeworkMomentsFromRefs() {
  const m = window.moment;
  if (!m) {
    return { start: null, end: null };
  }
  let start = homeworkDateFrom.value ? m(homeworkDateFrom.value, "YYYY-MM-DD", true) : null;
  let end = homeworkDateTo.value ? m(homeworkDateTo.value, "YYYY-MM-DD", true) : null;
  if (!start?.isValid()) {
    start = props.filters?.from ? m(props.filters.from, "YYYY-MM-DD", true) : null;
  }
  if (!end?.isValid()) {
    end = props.filters?.to ? m(props.filters.to, "YYYY-MM-DD", true) : null;
  }
  if (!start?.isValid() || !end?.isValid()) {
    start = m().subtract(29, "days");
    end = m();
  }
  return { start, end };
}

function setHomeworkDaterangeLabelHtml(startM, endM) {
  const el = document.getElementById(HOMEWORK_DATERANGE_LABEL_ID);
  if (!el || !startM?.isValid?.() || !endM?.isValid?.()) {
    return;
  }
  el.textContent = `${startM.format("DD.MM.YYYY")} - ${endM.format("DD.MM.YYYY")}`;
}

function destroyHomeworkDaterangePicker() {
  const $ = window.jQuery;
  if (!$) {
    return;
  }
  const $btn = $(`#${HOMEWORK_DATERANGE_BTN_ID}`);
  if ($btn.length && $btn.data("daterangepicker")) {
    $btn.data("daterangepicker").remove();
  }
}

function syncHomeworkDaterangePickerFromVue() {
  if (homeworkDaterangeApplyingFromPicker.value) {
    return;
  }
  const $ = window.jQuery;
  const m = window.moment;
  if (!$ || !m || !$(`#${HOMEWORK_DATERANGE_BTN_ID}`).length) {
    return;
  }
  const $btn = $(`#${HOMEWORK_DATERANGE_BTN_ID}`);
  const drp = $btn.data("daterangepicker");
  if (!drp) {
    return;
  }
  const { start, end } = homeworkMomentsFromRefs();
  if (!start.isSame(drp.startDate, "day")) {
    drp.setStartDate(start);
  }
  if (!end.isSame(drp.endDate, "day")) {
    drp.setEndDate(end);
  }
  setHomeworkDaterangeLabelHtml(start, end);
}

async function initHomeworkDaterangePicker() {
  homeworkDaterangeBootstrapping.value = true;
  try {
    await nextTick();
    await ensureHomeworkDaterangeAssets();
    const $ = window.jQuery;
    const m = window.moment;
    if (!$?.fn?.daterangepicker || !m) {
      return;
    }

    const $btn = $(`#${HOMEWORK_DATERANGE_BTN_ID}`);
    if (!$btn.length) {
      return;
    }

    destroyHomeworkDaterangePicker();

    const { start: initialStart, end: initialEnd } = homeworkMomentsFromRefs();
    if (!homeworkDateFrom.value || !homeworkDateTo.value) {
      homeworkDateFrom.value = initialStart.format("YYYY-MM-DD");
      homeworkDateTo.value = initialEnd.format("YYYY-MM-DD");
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

    const pickStart = homeworkDateFrom.value ? m(homeworkDateFrom.value, "YYYY-MM-DD") : initialStart;
    const pickEnd = homeworkDateTo.value ? m(homeworkDateTo.value, "YYYY-MM-DD") : initialEnd;

    $btn.daterangepicker(
      {
        ranges,
        startDate: pickStart,
        endDate: pickEnd,
        locale: {
          format: "DD.MM.YYYY",
          separator: " - ",
        },
      },
      (start, end) => {
        homeworkDaterangeApplyingFromPicker.value = true;
        homeworkDateFrom.value = start.format("YYYY-MM-DD");
        homeworkDateTo.value = end.format("YYYY-MM-DD");
        setHomeworkDaterangeLabelHtml(start, end);
        nextTick(() => {
          homeworkDaterangeApplyingFromPicker.value = false;
        });
      }
    );

    setHomeworkDaterangeLabelHtml(pickStart, pickEnd);
  } finally {
    homeworkDaterangeBootstrapping.value = false;
  }
}

const telegramSortBy = (field) => {
  if (telegramSortField.value === field) {
    telegramSortDirection.value = telegramSortDirection.value === "asc" ? "desc" : "asc";
  } else {
    telegramSortField.value = field;
    telegramSortDirection.value = "desc";
  }
};

const sortedTelegramGroups = computed(() => {
  const groups = [...(telegramStats.value?.group_stats ?? [])];
  const dir = telegramSortDirection.value === "asc" ? 1 : -1;
  return groups.sort((a, b) => {
    const va = a[telegramSortField.value] ?? 0;
    const vb = b[telegramSortField.value] ?? 0;
    return (va < vb ? -1 : va > vb ? 1 : 0) * dir;
  });
});

let chartInstances = {};

const destroyChart = (id) => {
  if (chartInstances[id]) {
    chartInstances[id].destroy();
    chartInstances[id] = null;
  }
};

const renderChart = (id, options) => {
  const ApexCharts = window.ApexCharts;
  if (!ApexCharts) return;
  destroyChart(id);
  const el = document.querySelector(`#${id}`);
  if (!el) return;
  chartInstances[id] = new ApexCharts(el, options);
  chartInstances[id].render();
};

const renderAllCharts = () => {
  if (attendanceChart.value?.labels?.length) {
    renderChart("attendanceChart", {
      series: [
        { name: t("attr_present"), data: attendanceChart.value.series.present, color: "#09b29c" },
        { name: t("attr_absent"), data: attendanceChart.value.series.absent, color: "#ef6547" },
      ],
      chart: { type: "bar", height: 350, stacked: true, toolbar: { show: false }, zoom: { enabled: true } },
      plotOptions: { bar: { horizontal: false, borderRadius: 10, dataLabels: { total: { enabled: true } } } },
      xaxis: { type: "datetime", categories: attendanceChart.value.labels },
      legend: { position: "right", offsetY: 40 },
      fill: { opacity: 1 },
    });
  }

  if (homeworkChart.value?.labels?.length) {
    renderChart("homeworkChart", {
      series: [
        { name: t("groups_homework") + " - " + t("attr_completed"), data: homeworkChart.value.series.completed, color: "#09b29c" },
        { name: t("groups_homework") + " - " + t("attr_not_completed"), data: homeworkChart.value.series.not_completed, color: "#ffc107" },
      ],
      chart: { type: "bar", height: 350, stacked: true, toolbar: { show: false }, zoom: { enabled: true } },
      plotOptions: { bar: { horizontal: false, borderRadius: 10, dataLabels: { total: { enabled: true } } } },
      xaxis: { type: "datetime", categories: homeworkChart.value.labels },
      legend: { position: "right", offsetY: 40 },
      fill: { opacity: 1 },
    });
  }

  if (studentsChart.value?.labels?.length) {
    renderChart("studentsChart", {
      series: [
        { name: t("students_students"), data: studentsChart.value.series.students, color: "#09b29c" },
        { name: t("students_deleted_students"), data: studentsChart.value.series.deleted_students, color: "#f4b53f" },
      ],
      chart: { height: 350, type: "area", toolbar: { show: false }, zoom: { enabled: true } },
      dataLabels: { enabled: false },
      stroke: { curve: "smooth" },
      xaxis: { type: "datetime", categories: studentsChart.value.labels },
      tooltip: { x: { format: "dd/MM/yy" } },
    });
  }

  if (studentsAgeChart.value?.series?.length) {
    renderChart("studentsAgeChart", {
      chart: { type: "pie", height: 400 },
      series: studentsAgeChart.value.series,
      labels: studentsAgeChart.value.labels,
      colors: ["#40a0fc", "#f3b53e", "#1abc9c", "#f1556c", "#e3e7ed"],
      dataLabels: { enabled: true, offset: -30 },
      legend: { position: "bottom" },
      title: { text: t("reports_students_chart_by_age"), align: "center" },
    });
  }

  if (studentsGenderChart.value?.series?.length) {
    renderChart("genderChart", {
      chart: { type: "donut", height: 300 },
      series: studentsGenderChart.value.series,
      labels: studentsGenderChart.value.labels,
      colors: ["#40a0fc", "#f3b53e"],
      dataLabels: { enabled: true },
      title: { text: t("students_chart_by_gender"), align: "center" },
      legend: { position: "bottom" },
    });
  }
};

const renderTelegramChart = () => {
  if (!telegramExpanded.value || !telegramStats.value?.chart_data?.series?.length) return;
  renderChart("telegramPieChart", {
    series: telegramStats.value.chart_data.series,
    chart: { type: "donut", height: 300 },
    labels: telegramStats.value.chart_data.labels,
    colors: ["#28a745", "#dc3545"],
    legend: { position: "bottom" },
    dataLabels: { enabled: true, formatter: (val) => Math.round(val) + "%" },
    plotOptions: {
      pie: {
        donut: {
          size: "65%",
          labels: {
            show: true,
            total: { show: true, label: t("total"), formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0) },
          },
        },
      },
    },
  });
};

onMounted(() => {
  loadCards();
  loadGroupAttendance();
  loadTelegramStats();
  loadUserCallsReport();
  loadCharts().then(() => renderAllCharts());
  (async () => {
    await initHomeworkDaterangePicker();
    await loadHomeworkReport();
  })();
});

onBeforeUnmount(() => {
  destroyHomeworkDaterangePicker();
});

watch(chartsLoading, (loading) => {
  if (!loading) renderAllCharts();
});

watch(telegramExpanded, (val) => {
  if (val) setTimeout(renderTelegramChart, 100);
  else closeTelegramStudentsModal();
});

watch(filterParams, (fp) => {
  const nextFrom = fp.from ?? "";
  const nextTo = fp.to ?? "";
  const homeworkDatesUnchanged = homeworkDateFrom.value === nextFrom && homeworkDateTo.value === nextTo;
  homeworkDateFrom.value = nextFrom;
  homeworkDateTo.value = nextTo;
  loadCards();
  loadCharts().then(() => renderAllCharts());
  loadUserCallsReport();
  if (homeworkDatesUnchanged) {
    loadHomeworkReport(1);
  }
});

watch(
  () => [groupAttendanceDate.value, groupAttendanceSearch.value],
  () => loadGroupAttendance()
);

watch(
  () => [homeworkSearch.value, homeworkGroupId.value, homeworkTeacherId.value, homeworkDateFrom.value, homeworkDateTo.value],
  () => {
    if (homeworkDaterangeBootstrapping.value) {
      return;
    }
    loadHomeworkReport(1);
  }
);

watch(
  () => [homeworkDateFrom.value, homeworkDateTo.value],
  () => {
    nextTick(() => syncHomeworkDaterangePickerFromVue());
  }
);

watch(homeworkTeacherId, () => {
  const tid = homeworkTeacherId.value;
  const gid = homeworkGroupId.value;
  if (!gid) {
    return;
  }
  const group = homeworkData.value.groups?.find((g) => String(g.id) === String(gid));
  if (!group || (tid && String(group.teacher_id) !== String(tid))) {
    homeworkGroupId.value = "";
  }
});

watch(
  () => userCallsSearch.value,
  () => loadUserCallsReport()
);
</script>

<template>
  <Head :title="t('reports_education')" />

  <div class="educational-report-page">
    <form @submit.prevent="applyFilters">
      <div class="mb-3 d-flex justify-content-end">
        <div class="gap-2 d-flex">
          <div class="input-group">
            <input v-model="filterForm.from" name="from" type="date" class="form-control" :placeholder="t('attr_from')" />
          </div>
          <div class="input-group">
            <input v-model="filterForm.to" name="to" type="date" class="form-control" :placeholder="t('attr_to')" />
          </div>
          <div>
            <button type="submit" class="gap-2 btn btn-primary d-flex justify-content-between align-items-center">
              <i class="fas fa-filter"></i>
              {{ t("Filter") }}
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Cards -->
    <div class="row h-100 mb-4">
      <div class="col-lg-3 col-md-6">
        <div class="card card-animate">
          <div class="card-body">
            <div v-if="cardsLoading" class="placeholder-glow"><span class="placeholder col-12" style="height:60px"></span></div>
            <div v-else class="d-flex align-items-center">
              <div class="avatar-sm flex-shrink-0">
                <span
                  :class="[
                    'avatar-title rounded-circle fs-4',
                    cards?.attendance_percentage < 70 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success',
                  ]"
                >
                  <i class="fa-solid fa-chart-simple"></i>
                </span>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("reports_attendance") }}</p>
                <h4 :class="['mb-0', cards?.attendance_percentage >= 70 ? 'text-success' : 'text-danger']">
                  {{ cards?.attendance_percentage ?? 0 }} %
                </h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card card-animate">
          <div class="card-body">
            <div v-if="cardsLoading" class="placeholder-glow"><span class="placeholder col-12" style="height:60px"></span></div>
            <div v-else class="d-flex align-items-center">
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                  <i class="fas fa-users"></i>
                </span>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("groups_students_count") }}</p>
                <h4 class="mb-0 text-success">{{ cards?.students_count ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card card-animate">
          <div class="card-body">
            <div v-if="cardsLoading" class="placeholder-glow"><span class="placeholder col-12" style="height:60px"></span></div>
            <div v-else class="d-flex align-items-center">
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                  <i class="fas fa-user-graduate"></i>
                </span>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("groups_teachers_count") }}</p>
                <h4 class="mb-0 text-success">{{ cards?.teachers_count ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card card-animate">
          <div class="card-body">
            <div v-if="cardsLoading" class="placeholder-glow"><span class="placeholder col-12" style="height:60px"></span></div>
            <div v-else class="d-flex align-items-center">
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                  <i class="fa-solid fa-person-walking-arrow-right"></i>
                </span>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ t("students_deleted_students_count") }}</p>
                <h4 class="mb-0 text-success">{{ cards?.deleted_students_count ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Chart -->
    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span>{{ t("reports_attendance_chart") }}</span>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
          <Link
            :href="
              route('admin.reports.educational.attendance-detail', {
                ...(props.filters?.from && { from: props.filters.from }),
                ...(props.filters?.to && { to: props.filters.to }),
              })
            "
            class="btn btn-primary btn-sm"
          >
            <i class="fas fa-list-ul me-1"></i>
            {{ t('reports_detailed') }}
          </Link>
          <button type="button" class="btn btn-success btn-sm" @click="exportAttendance">
            <i class="fas fa-file-excel me-1"></i>
            {{ t("export") }}
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="chartsLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-show="!chartsLoading" id="attendanceChart"></div>
      </div>
    </div>

    <!-- Homework Chart -->
    <div class="card mb-4">
      <div class="card-header">{{ t("groups_homework") }} {{ t("reports_chart") }}</div>
      <div class="card-body">
        <div v-if="chartsLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-show="!chartsLoading" id="homeworkChart"></div>
      </div>
    </div>

    <!-- Homework Report -->
    <div class="card mb-4">
      <div class="card-header">
        <div class="row g-3 align-items-center">
          <div class="col-lg-3 col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
              <input v-model="homeworkSearch" type="text" class="form-control" :placeholder="t('search') + '...'" />
            </div>
          </div>
          <div class="col-lg-2 col-md-6">
            <select v-model="homeworkTeacherId" class="form-select">
              <option value="">{{ t("all_teachers") }}</option>
              <option v-for="teacher in homeworkData.teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.name }}
              </option>
            </select>
          </div>
          <div class="col-lg-3 col-md-6">
            <select v-model="homeworkGroupId" class="form-select">
              <option value="">{{ t("all_groups") }}</option>
              <option v-for="group in homeworkData.groups" :key="group.id" :value="group.id">
                {{ group.name }}{{ group.teacher ? " - " + group.teacher.name : "" }}
              </option>
            </select>
          </div>
          <div class="col-lg-4 col-md-12">
            <div class="d-flex flex-wrap align-items-stretch gap-2 w-100">
              <span class="text-muted text-nowrap small align-self-center">{{ t("groups_homework") }}:</span>
              <button
                type="button"
                class="bg-white border btn flex-grow-1 text-nowrap d-inline-flex align-items-center justify-content-center gap-1"
                :id="HOMEWORK_DATERANGE_BTN_ID"
              >
                <i class="far fa-calendar-alt"></i>
                <span :id="HOMEWORK_DATERANGE_LABEL_ID"></span>
                <i class="fas fa-caret-down"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="homeworkLoading" class="text-center py-4">
          <div class="spinner-border text-primary"></div>
        </div>
        <template v-else>
          <div class="row mb-3">
            <div class="col-xl-3 col-md-6">
              <div class="card card-animate">
                <div class="card-body">
                  <p class="text-uppercase fw-medium text-muted mb-1">{{ t("total_students") }}</p>
                  <h4 class="fs-22 fw-semibold mb-0">{{ homeworkData.stats?.total_students ?? 0 }}</h4>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card card-animate">
                <div class="card-body">
                  <p class="text-uppercase fw-medium text-muted mb-1">{{ t("homework") }} - {{ t("completed") }}</p>
                  <h4 class="fs-22 fw-semibold mb-0 text-success">{{ homeworkData.stats?.total_completed ?? 0 }}</h4>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card card-animate">
                <div class="card-body">
                  <p class="text-uppercase fw-medium text-muted mb-1">{{ t("homework") }} - {{ t("missing") }}</p>
                  <h4 class="fs-22 fw-semibold mb-0 text-danger">{{ homeworkData.stats?.total_not_completed ?? 0 }}</h4>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card card-animate">
                <div class="card-body">
                  <p class="text-uppercase fw-medium text-muted mb-1">{{ t("homework") }} {{ t("rate") }}</p>
                  <h4 class="fs-22 fw-semibold mb-0 text-info">{{ homeworkData.stats?.completion_rate ?? 0 }}%</h4>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-centered table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>{{ t("students_full_name") }}</th>
                  <th>{{ t("groups_group") }}</th>
                  <th>{{ t("students_phone") }}</th>
                  <th class="text-center">{{ t("completed") }}</th>
                  <th class="text-center">{{ t("missing") }}</th>
                  <th class="text-center">{{ t("total") }}</th>
                  <th class="text-center">{{ t("rate") }}</th>
                  <th class="text-center">{{ t("actions") }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="student in homeworkData.students?.data ?? []" :key="student.id">
                  <td>
                    <Link :href="route('admin.students.show', student.id)" class="text-dark">
                      {{ student.name }} {{ student.surname }}
                    </Link>
                  </td>
                  <td>{{ student.group_name }}</td>
                  <td>{{ student.phone }}</td>
                  <td class="text-center">
                    <span v-if="student.completed_count > 0" class="badge bg-success-subtle text-success">
                      {{ student.completed_count }}
                    </span>
                    <span v-else class="badge bg-light text-muted">0</span>
                  </td>
                  <td class="text-center">
                    <span v-if="student.not_completed_count > 0" class="badge bg-danger-subtle text-danger">
                      {{ student.not_completed_count }}
                    </span>
                    <span v-else class="badge bg-success-subtle text-success">0</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-info-subtle text-info">{{ student.total_homework }}</span>
                  </td>
                  <td class="text-center">
                    <div class="progress" style="height: 20px">
                      <div
                        :class="[
                          'progress-bar',
                          (student.total_homework > 0 ? (student.completed_count / student.total_homework) * 100 : 0) >= 75
                            ? 'bg-success'
                            : (student.total_homework > 0 ? (student.completed_count / student.total_homework) * 100 : 0) >= 50
                              ? 'bg-warning'
                              : 'bg-danger',
                        ]"
                        :style="{
                          width:
                            (student.total_homework > 0 ? (student.completed_count / student.total_homework) * 100 : 0) + '%',
                        }"
                      >
                        {{
                          student.total_homework > 0
                            ? Math.round((student.completed_count / student.total_homework) * 100)
                            : 0
                        }}%
                      </div>
                    </div>
                  </td>
                  <td class="text-center">
                    <Link :href="route('admin.students.show', student.id)" class="btn btn-sm btn-primary">
                      <i class="mdi mdi-eye"></i>
                    </Link>
                  </td>
                </tr>
                <tr v-if="!(homeworkData.students?.data?.length)">
                  <td colspan="8" class="text-center py-4">
                    <p class="text-muted mb-0">{{ t("no_data") }}</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="homeworkData.students?.links?.length" class="mt-3 d-flex flex-wrap justify-content-center align-items-center gap-2">
            <template v-for="(link, i) in homeworkData.students.links" :key="i">
              <button
                v-if="link.url"
                type="button"
                class="btn btn-sm flex-shrink-0"
                :class="link.active ? 'btn-primary' : 'btn-outline-secondary'"
                :disabled="!link.url"
                v-html="link.label"
                @click="homeworkPage(link.url)"
              />
            </template>
          </div>
        </template>
      </div>
    </div>

    <!-- User Calls Report -->
    <div class="card mb-4 border-0 shadow-sm rounded-4">
      <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">{{ t("user_calls_report") || "Foydalanuvchi qo'ng'iroqlar hisoboti" }}</h5>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-4">
            <input
              type="text"
              v-model="userCallsSearch"
              class="form-control bg-light border-0"
              :placeholder="t('search') + '...'"
            />
          </div>
        </div>

        <div v-if="userCallsLoading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <template v-else>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-muted">
                <tr>
                  <th class="fw-semibold">{{ t("name") }}</th>
                  <th class="text-center fw-semibold">{{ t("answered") || "Javob berildi" }}</th>
                  <th class="text-center fw-semibold">{{ t("not_answered") || "Javob berilmadi" }}</th>
                  <th class="text-center fw-semibold">{{ t("busy") || "Band" }}</th>
                  <th class="text-center fw-semibold">{{ t("no_answer") || "O'chiq" }}</th>
                  <th class="text-center fw-semibold">{{ t("total_calls") || "Umumiy qo'ng'iroqlar" }}</th>
                  <th class="text-center fw-semibold">{{ t("total_duration") || "Umumiy davomiylik" }}</th>
                </tr>
              </thead>
              <tbody class="border-top-0">
                <tr v-for="row in userCallsData" :key="row.id">
                  <td>
                    <div class="fw-medium text-dark">{{ row.name }}</div>
                  </td>
                  <td class="text-center text-success"><span class="badge bg-success-subtle text-success">{{ row.answered_calls }}</span></td>
                  <td class="text-center text-danger"><span class="badge bg-danger-subtle text-danger">{{ row.not_answered_calls }}</span></td>
                  <td class="text-center text-warning"><span class="badge bg-warning-subtle text-warning">{{ row.busy_calls }}</span></td>
                  <td class="text-center text-secondary"><span class="badge bg-secondary-subtle text-secondary">{{ row.no_answer_calls }}</span></td>
                  <td class="text-center">
                    <button
                      type="button"
                      class="btn btn-link text-dark fw-bold text-decoration-none p-0 d-flex flex-column align-items-center w-100"
                      @click="showCallBreakdown(row)"
                    >
                      <span class="fs-5">{{ row.total_calls }}</span>
                      <small class="text-primary" style="font-size: 0.7rem;">(i)</small>
                    </button>
                  </td>
                  <td class="text-center text-muted">{{ Math.floor(row.total_duration / 60) }} min {{ row.total_duration % 60 }} sec</td>
                </tr>
                <tr v-if="!userCallsData.length">
                  <td colspan="7" class="text-center py-4">
                    <p class="text-muted mb-0">{{ t("no_data") }}</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>
    </div>

    <!-- Call Breakdown Modal -->
    <div v-if="callBreakdownModal.show" class="modal show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); z-index: 1055;">
      <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-2xl rounded-5 overflow-hidden">
          <div class="modal-header border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <h6 class="text-uppercase ls-wider fw-bold text-muted mb-0" style="font-size: 0.75rem;">{{ t("call_statistics") || "Qo'ng'iroqlar statistikasi" }}</h6>
            <button type="button" class="btn-close shadow-none" @click="callBreakdownModal.show = false" style="font-size: 0.8rem;"></button>
          </div>
          
          <div class="modal-body p-4 pt-3">
            <h5 class="fw-black text-dark mb-4">{{ callBreakdownModal.row.name }}</h5>

            <div class="d-flex gap-3 mb-4">
              <div class="flex-fill bg-primary-subtle p-3 rounded-4 border border-primary-subtle shadow-sm">
                <div class="d-flex align-items-center mb-2">
                  <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                    <i class="mdi mdi-school" style="font-size: 14px;"></i>
                  </div>
                  <span class="text-primary fw-bold small text-uppercase">{{ t("students") }}</span>
                </div>
                <div class="fs-3 fw-black text-primary">{{ callBreakdownModal.row.student_calls_count }}</div>
              </div>
              <div class="flex-fill bg-success-subtle p-3 rounded-4 border border-success-subtle shadow-sm">
                <div class="d-flex align-items-center mb-2">
                  <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                    <i class="mdi mdi-flash" style="font-size: 14px;"></i>
                  </div>
                  <span class="text-success fw-bold small text-uppercase">{{ t("leads") }}</span>
                </div>
                <div class="fs-3 fw-black text-success">{{ callBreakdownModal.row.lead_calls_count }}</div>
              </div>
            </div>
            
            <div class="row g-3">
              <!-- Students Column -->
              <div class="col-6">
                <div class="bg-light rounded-4 p-3 border border-light-subtle h-100">
                  <div class="text-muted small fw-bold mb-3 border-bottom pb-2">{{ t("students") }}</div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-dark small fw-medium">{{ t("answered") }}</span>
                    <span class="fw-bold text-success" style="font-size: 0.85rem;">{{ callBreakdownModal.row.student_answered_calls }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-dark small fw-medium">{{ t("not_answered") }}</span>
                    <span class="fw-bold text-danger" style="font-size: 0.85rem;">{{ callBreakdownModal.row.student_not_answered_calls }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-dark small fw-medium">{{ t("busy") }}</span>
                    <span class="fw-bold text-warning" style="font-size: 0.85rem;">{{ callBreakdownModal.row.student_busy_calls }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-dark small fw-medium">{{ t("no_answer") }}</span>
                    <span class="fw-bold text-secondary" style="font-size: 0.85rem;">{{ callBreakdownModal.row.student_no_answer_calls }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Leads Column -->
              <div class="col-6">
                <div class="bg-light rounded-4 p-3 border border-light-subtle h-100">
                  <div class="text-muted small fw-bold mb-3 border-bottom pb-2">{{ t("leads") }}</div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-dark small fw-medium">{{ t("answered") }}</span>
                    <span class="fw-bold text-success" style="font-size: 0.85rem;">{{ callBreakdownModal.row.lead_answered_calls }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-dark small fw-medium">{{ t("not_answered") }}</span>
                    <span class="fw-bold text-danger" style="font-size: 0.85rem;">{{ callBreakdownModal.row.lead_not_answered_calls }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-dark small fw-medium">{{ t("busy") }}</span>
                    <span class="fw-bold text-warning" style="font-size: 0.85rem;">{{ callBreakdownModal.row.lead_busy_calls }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-dark small fw-medium">{{ t("no_answer") }}</span>
                    <span class="fw-bold text-secondary" style="font-size: 0.85rem;">{{ callBreakdownModal.row.lead_no_answer_calls }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
            <button type="button" class="btn btn-dark w-100 py-2 fw-bold rounded-3 shadow-sm border-0" @click="callBreakdownModal.show = false">
              {{ t("close") }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Students Chart -->
    <div class="card card-height-100 mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ t("reports_students_chart") }}</span>
        <button type="button" class="btn btn-success btn-sm" @click="exportStudents">
          <i class="fas fa-file-excel me-1"></i>
          {{ t("export") }}
        </button>
      </div>
      <div class="card-body">
        <div v-if="chartsLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-show="!chartsLoading" id="studentsChart"></div>
      </div>
    </div>

    <!-- Group Attendance -->
    <div class="row">
      <div class="col-md-6 col-sm-12 mb-4">
        <div class="card card-height-100">
          <div class="card-header position-sticky top-0 bg-white d-flex justify-content-between align-items-center">
            <span>{{ t("group_attendances") }} ({{ groupAttendanceData.groups?.length ?? 0 }})</span>
            <div class="d-flex gap-2">
              <input
                v-model="groupAttendanceSearch"
                type="text"
                class="form-control form-control-sm"
                :placeholder="t('search')"
              />
              <input v-model="groupAttendanceDate" type="date" class="form-control form-control-sm" />
            </div>
          </div>
          <div class="overflow-auto card-body" style="max-height: 400px">
            <div v-if="groupAttendanceLoading" class="text-center py-4">
              <div class="spinner-border text-success"></div>
            </div>
            <div v-else class="table-responsive">
              <table class="table">
                <tr>
                  <th>ID</th>
                  <th>{{ t("groups_name") }}</th>
                  <th>{{ t("total_students_count") }}</th>
                  <th>{{ t("comers") }}</th>
                  <th>{{ t("not_comers") }}</th>
                  <th>{{ t("percentage") }}</th>
                </tr>
                <tr v-for="g in groupAttendanceData.groups" :key="g.id">
                  <td>{{ g.id }}</td>
                  <td>
                    <Link :href="route('admin.groups.show', g.id)" class="fw-semibold">{{ g.name }}</Link>
                    <div><i class="ri-user-line text-primary"></i> {{ g.teacher_name }}</div>
                  </td>
                  <td><span class="badge bg-primary-subtle text-primary">{{ g.students_count }}</span></td>
                  <td>{{ g.comers }}</td>
                  <td>{{ (g.absent_count || 0) + (g.not_comers || 0) }}</td>
                  <td>
                    {{
                      g.students_count > 0 && g.comers + (g.absent_count || 0) + (g.not_comers || 0) > 0
                        ? Math.round((g.comers / (g.comers + (g.absent_count || 0) + (g.not_comers || 0))) * 100)
                        : 0
                    }}%
                  </td>
                </tr>
                <tr v-if="!groupAttendanceData.groups?.length && !groupAttendanceLoading">
                  <td colspan="6" class="text-center py-4">{{ t("no_data") }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-sm-12 mb-4">
        <div class="card card-height-100">
          <div class="card-header position-sticky top-0 bg-white d-flex justify-content-between align-items-center">
            <span>{{ t("reports_today_group_attendance_not_exist") }} ({{ groupAttendanceData.groupsNotExist?.length ?? 0 }})</span>
            <input v-model="groupAttendanceDate" type="date" class="form-control form-control-sm" />
          </div>
          <div class="overflow-auto card-body" style="max-height: 400px">
            <div v-if="groupAttendanceLoading" class="text-center py-4">
              <div class="spinner-border text-success"></div>
            </div>
            <div v-else class="table-responsive">
              <table class="table">
                <tr>
                  <th>ID</th>
                  <th>{{ t("groups_name") }}</th>
                  <th>{{ t("groups_teacher") }}</th>
                  <th>{{ t("groups_students_count") }}</th>
                  <th></th>
                </tr>
                <tr v-for="g in groupAttendanceData.groupsNotExist" :key="g.id">
                  <td>{{ g.id }}</td>
                  <td>
                    <Link :href="route('admin.groups.show', g.id)" class="fw-semibold">{{ g.name }}</Link>
                  </td>
                  <td>{{ g.teacher_name }}</td>
                  <td><span class="badge bg-primary-subtle text-primary">{{ g.students_count }}</span></td>
                  <td>
                    <a
                      :href="route('admin.attendance', { group: g.id }) + '?date=' + groupAttendanceDate"
                      class="btn btn-primary btn-sm"
                    >
                      <i class="mdi mdi-clipboard-plus"></i>
                    </a>
                  </td>
                </tr>
                <tr v-if="!groupAttendanceData.groupsNotExist?.length && !groupAttendanceLoading">
                  <td colspan="5" class="text-center py-4">{{ t("no_data") }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Students Gender & Age Charts -->
    <div class="row">
      <div class="col-md-5 col-sm-12 mb-4">
        <div class="card card-height-100 rounded-4">
          <div class="p-2 card-body">
            <div v-if="chartsLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div v-show="!chartsLoading" id="genderChart"></div>
          </div>
        </div>
      </div>
      <div class="col-md-7 col-sm-12 mb-4">
        <div class="card rounded-4">
          <div class="p-0 card-body">
            <div v-if="chartsLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div v-show="!chartsLoading" id="studentsAgeChart"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Telegram Connection Stats -->
    <div class="card mb-3">
      <div
        class="card-body p-3 cursor-pointer"
        style="cursor: pointer"
        @click="telegramExpanded = !telegramExpanded"
      >
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm">
              <div class="avatar-title rounded-3 fs-3" style="background-color: #0088cc; color: #fff">
                <i class="ri-telegram-line"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-1">{{ t("telegram_connection_overview") }}</h5>
              <div v-if="telegramLoading" class="placeholder-glow"><span class="placeholder col-8"></span></div>
              <p v-else class="text-muted mb-0 small">
                <span class="badge" style="background-color: #28a745; color: #fff">
                  {{ telegramStats?.connected_students ?? 0 }} {{ t("telegram_connected") }}
                </span>
                <span class="badge ms-1" style="background-color: #dc3545; color: #fff">
                  {{ telegramStats?.not_connected_students ?? 0 }} {{ t("telegram_not_connected") }}
                </span>
                <span class="badge ms-1" style="background-color: #0088cc; color: #fff">
                  {{ telegramStats?.connection_percentage ?? 0 }}%
                </span>
              </p>
            </div>
          </div>
          <div>
            <i :class="'ri-arrow-' + (telegramExpanded ? 'up' : 'down') + '-s-line fs-4 text-muted'"></i>
          </div>
        </div>
      </div>
    </div>

    <div v-if="telegramExpanded" class="row">
      <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ t("telegram_connection_overview") }}</h5>
          </div>
          <div class="card-body">
            <div v-if="telegramLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div v-else id="telegramPieChart"></div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ t("telegram_top_connected_groups") }}</h5>
          </div>
          <div class="card-body">
            <div v-if="telegramStats?.top_groups?.length" class="table-responsive">
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>{{ t("groups_group") }}</th>
                    <th class="text-center">{{ t("students_students") }}</th>
                    <th class="text-end">%</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="g in telegramStats.top_groups" :key="g.id">
                    <td>
                      <Link :href="route('admin.groups.show', g.id)" class="text-body fw-medium">{{ g.name }}</Link>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-success-subtle text-success">{{ g.connected_students }}</span>
                      / <span class="badge bg-secondary-subtle text-secondary">{{ g.total_students }}</span>
                    </td>
                    <td class="text-end"><span class="badge bg-info">{{ g.connection_percentage }}%</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-muted">{{ t("no_groups") }}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ t("telegram_needs_attention_groups") }}</h5>
          </div>
          <div class="card-body">
            <div v-if="telegramStats?.bottom_groups?.length" class="table-responsive">
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>{{ t("groups_group") }}</th>
                    <th class="text-center">{{ t("students_students") }}</th>
                    <th class="text-end">%</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="g in telegramStats.bottom_groups" :key="g.id">
                    <td>
                      <Link :href="route('admin.groups.show', g.id)" class="text-body fw-medium">{{ g.name }}</Link>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-success-subtle text-success">{{ g.connected_students }}</span>
                      / <span class="badge bg-secondary-subtle text-secondary">{{ g.total_students }}</span>
                    </td>
                    <td class="text-end">
                      <span
                        :class="[
                          'badge',
                          g.connection_percentage < 50 ? 'bg-danger' : 'bg-warning',
                        ]"
                      >
                        {{ g.connection_percentage }}%
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-muted">{{ t("no_groups") }}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ t("telegram_groups_detail") }}</h5>
          </div>
          <div class="card-body">
            <div v-if="sortedTelegramGroups.length" class="table-responsive">
              <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th style="cursor: pointer" @click="telegramSortBy('name')">{{ t("groups_group") }}</th>
                    <th class="text-center" style="cursor: pointer" @click="telegramSortBy('total_students')">
                      {{ t("students_students") }}
                    </th>
                    <th class="text-center" style="cursor: pointer" @click="telegramSortBy('connected_students')">
                      {{ t("telegram_connected") }}
                    </th>
                    <th class="text-center" style="cursor: pointer" @click="telegramSortBy('not_connected_students')">
                      {{ t("telegram_not_connected") }}
                    </th>
                    <th class="text-center" style="cursor: pointer" @click="telegramSortBy('connection_percentage')">
                      {{ t("telegram_connection_rate") }}
                    </th>
                    <th class="text-center">{{ t("status") }}</th>
                    <th class="text-center text-nowrap" style="min-width: 7rem">{{ t("btn_show") }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(g, index) in sortedTelegramGroups" :key="g.id">
                    <td>{{ index + 1 }}</td>
                    <td>
                      <Link :href="route('admin.groups.show', g.id)" class="text-body fw-medium">{{ g.name }}</Link>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-primary-subtle text-primary">{{ g.total_students }}</span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-success-subtle text-success">{{ g.connected_students }}</span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-danger-subtle text-danger">{{ g.not_connected_students }}</span>
                    </td>
                    <td class="text-center">
                      <div class="progress" style="height: 20px">
                        <div
                          :class="[
                            'progress-bar',
                            g.connection_percentage >= 75 ? 'bg-success' : g.connection_percentage >= 50 ? 'bg-warning' : 'bg-danger',
                          ]"
                          :style="{ width: g.connection_percentage + '%' }"
                        >
                          {{ g.connection_percentage }}%
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <span
                        :class="[
                          'badge',
                          g.connection_percentage >= 75
                            ? 'bg-success'
                            : g.connection_percentage >= 50
                              ? 'bg-warning'
                              : g.connection_percentage >= 25
                                ? 'bg-danger'
                                : 'bg-dark',
                        ]"
                      >
                        {{
                          g.connection_percentage >= 75
                            ? t("telegram_excellent")
                            : g.connection_percentage >= 50
                              ? t("telegram_good")
                              : g.connection_percentage >= 25
                                ? t("telegram_poor")
                                : t("telegram_critical")
                        }}
                      </span>
                    </td>
                    <td class="text-center text-nowrap align-middle">
                      <button
                        type="button"
                        class="btn btn-primary btn-sm px-3"
                        @click="openTelegramStudentsModal(g.id, g.name)"
                      >
                        <i class="ri-eye-line me-1"></i>{{ t("btn_show") }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-5">
              <i class="ri-inbox-line display-4 text-muted"></i>
              <p class="text-muted mt-3">{{ t("no_groups") }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Telegram group students modal (outside .row so layout is valid) -->
    <div
      v-if="telegramStudentsModal.show"
      class="modal show d-block"
      tabindex="-1"
      role="dialog"
      aria-modal="true"
      style="background: rgba(15, 23, 42, 0.6); z-index: 1060"
      @click.self="closeTelegramStudentsModal"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" @click.stop>
        <div class="modal-content">
          <div class="modal-header border-bottom">
            <h5 class="modal-title fw-semibold">{{ telegramStudentsModal.groupName }}</h5>
            <button
              type="button"
              class="btn-close"
              aria-label="Close"
              @click="closeTelegramStudentsModal"
            ></button>
          </div>
          <div class="modal-body pt-3">
            <p class="text-muted small mb-3">{{ t("telegram_students_connection_list") }}</p>
            <div v-if="telegramStudentsModal.loading" class="text-center py-5">
              <div class="spinner-border text-primary"></div>
            </div>
            <template v-else>
              <div v-if="!telegramStudentsModal.students.length" class="text-muted mb-0">
                {{ t("groups_no_students") }}
              </div>
              <div v-else class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 3rem">#</th>
                      <th>{{ t("students_students") }}</th>
                      <th class="text-center text-nowrap">{{ t("telegram_connection_status") }}</th>
                      <th>{{ t("telegram_id") }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(s, si) in telegramStudentsModal.students" :key="s.id">
                      <td>{{ si + 1 }}</td>
                      <td>
                        <Link :href="route('admin.students.show', s.id)" class="text-body fw-medium">{{
                          s.full_name
                        }}</Link>
                      </td>
                      <td class="text-center">
                        <span
                          v-if="s.telegram_chat_id"
                          class="badge bg-success-subtle text-success"
                          >{{ t("telegram_connected") }}</span
                        >
                        <span v-else class="badge bg-warning-subtle text-warning">{{
                          t("telegram_not_connected")
                        }}</span>
                      </td>
                      <td>
                        <code v-if="s.telegram_chat_id" class="user-select-all">{{ s.telegram_chat_id }}</code>
                        <span v-else class="text-muted">—</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </template>
          </div>
          <div class="modal-footer border-top">
            <button type="button" class="btn btn-light" @click="closeTelegramStudentsModal">
              {{ t("btn_cancel") }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.educational-report-page {
  min-height: 100%;
}
</style>
