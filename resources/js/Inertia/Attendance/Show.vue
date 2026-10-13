<script setup>
import { computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
  record: Object,
  labels: Object,
});

const l = (key) => props.labels?.[key] ?? key;

const themeDisplay = computed(() => {
  const v = props.record?.theme;
  if (v == null || String(v).trim() === "") {
    return l("empty_theme");
  }
  return String(v).trim();
});

const commentDisplay = computed(() => {
  const v = props.record?.comment;
  if (v == null || String(v).trim() === "") {
    return l("empty_no_comment");
  }
  return String(v).trim();
});

const scoreDisplay = computed(() => {
  if (props.record?.score === null || props.record?.score === undefined) {
    return l("empty_score");
  }
  return String(props.record.score);
});

const paymentDisplay = computed(() => {
  if (props.record?.monthly_payment_formatted == null || props.record?.monthly_payment_formatted === "") {
    return l("empty_payment");
  }
  return props.record.monthly_payment_formatted;
});

const notifyTimeDisplay = computed(() => {
  if (!props.record?.notify_time_formatted) {
    return l("empty_notify_time");
  }
  return props.record.notify_time_formatted;
});

const homeworkBadge = computed(() => {
  const h = props.record?.homework;
  if (h === true) {
    return { class: "bg-success-subtle text-success border border-success-subtle", text: l("homework_done") };
  }
  if (h === false) {
    return { class: "bg-warning-subtle text-warning border border-warning-subtle", text: l("homework_not_done") };
  }
  return { class: "bg-secondary-subtle text-secondary border border-secondary-subtle", text: l("homework_unknown") };
});

const notificationBadge = computed(() => {
  if (props.record?.is_notified) {
    return { class: "bg-info-subtle text-info border border-info-subtle", text: l("notified_yes") };
  }
  return { class: "bg-light text-muted border", text: l("notified_no") };
});

const statusVisual = computed(() => {
  const s = props.record?.status;
  if (s === "present") {
    return {
      stripe: "border-start border-success border-4",
      badge: "bg-success text-white px-3 py-2 rounded-pill fw-semibold shadow-sm",
    };
  }
  if (s === "absent") {
    return {
      stripe: "border-start border-danger border-4",
      badge: "bg-danger text-white px-3 py-2 rounded-pill fw-semibold shadow-sm",
    };
  }
  return {
    stripe: "border-start border-secondary border-4",
    badge: "bg-secondary text-white px-3 py-2 rounded-pill fw-semibold shadow-sm",
  };
});

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
    return;
  }
  router.visit(route("admin.dashboard"));
};
</script>

<template>
  <Head :title="l('page_title')" />

  <div class="attendance-show-page container-fluid px-0 px-md-2 pb-4">
    <div class="mb-3">
      <button type="button" class="btn btn-sm btn-outline-secondary" @click="goBack">
        <i class="fas fa-arrow-left me-1"></i>
        {{ l("back") }}
      </button>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <div class="card shadow-sm border-0 overflow-hidden" :class="statusVisual.stripe">
          <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3 mb-4">
              <div>
                <div class="text-uppercase text-muted small fw-semibold mb-1">{{ l("section_overview") }}</div>
                <h1 class="h4 mb-0 text-dark">{{ l("page_title") }}</h1>
                <span class="text-muted small">ID {{ record?.id }}</span>
              </div>
              <div class="d-flex flex-column align-items-md-end gap-2">
                <span :class="statusVisual.badge">{{ record?.status_label }}</span>
                <div class="fw-semibold text-body-secondary">{{ record?.date_formatted || "—" }}</div>
              </div>
            </div>

            <dl class="row mb-0 small gy-2">
              <dt class="col-sm-3 col-md-2 text-muted">{{ l("label_student") }}</dt>
              <dd class="col-sm-9 col-md-4 mb-0">
                <template v-if="record?.student?.url">
                  <Link :href="record.student.url" class="fw-semibold text-decoration-none">
                    {{ record.student.name }}
                  </Link>
                </template>
                <span v-else class="text-muted">—</span>
              </dd>

              <dt class="col-sm-3 col-md-2 text-muted">{{ l("label_group") }}</dt>
              <dd class="col-sm-9 col-md-4 mb-0">
                <template v-if="record?.group?.url">
                  <Link :href="record.group.url" class="fw-semibold text-decoration-none">
                    {{ record.group.name }}
                  </Link>
                </template>
                <span v-else class="text-muted">—</span>
              </dd>

              <dt class="col-sm-3 col-md-2 text-muted">{{ l("label_theme") }}</dt>
              <dd
                class="col-sm-9 col-md-10 mb-0"
                :class="themeDisplay === l('empty_theme') ? 'text-muted fst-italic' : ''"
              >
                {{ themeDisplay }}
              </dd>
            </dl>

            <div class="mt-3 pt-3 border-top d-flex flex-wrap gap-2">
              <Link v-if="record?.student?.url" :href="record.student.url" class="btn btn-sm btn-outline-primary">
                {{ l("open_student") }}
              </Link>
              <Link v-if="record?.group?.url" :href="record.group.url" class="btn btn-sm btn-outline-primary">
                {{ l("open_group") }}
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white border-bottom py-3">
            <span class="fw-semibold text-dark">{{ l("section_performance") }}</span>
          </div>
          <div class="card-body">
            <dl class="row mb-0 small">
              <dt class="col-sm-4 text-muted">{{ l("label_score") }}</dt>
              <dd
                class="col-sm-8 mb-3"
                :class="scoreDisplay === l('empty_score') ? 'text-muted fst-italic' : 'fw-semibold fs-6'"
              >
                {{ scoreDisplay }}
              </dd>

              <dt class="col-sm-4 text-muted">{{ l("label_homework") }}</dt>
              <dd class="col-sm-8 mb-3">
                <span class="badge rounded-pill" :class="homeworkBadge.class">{{ homeworkBadge.text }}</span>
              </dd>

              <dt class="col-sm-4 text-muted align-top">{{ l("label_comment") }}</dt>
              <dd
                class="col-sm-8 mb-0 comment-pre"
                :class="commentDisplay === l('empty_no_comment') ? 'text-muted fst-italic' : ''"
              >
                {{ commentDisplay }}
              </dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="card shadow-sm border-0 mb-3">
          <div class="card-header bg-white border-bottom py-3">
            <span class="fw-semibold text-dark">{{ l("section_finance") }}</span>
          </div>
          <div class="card-body">
            <div class="text-muted small mb-1">{{ l("label_monthly_payment") }}</div>
            <div
              class="fs-5 fw-semibold"
              :class="paymentDisplay === l('empty_payment') ? 'text-muted fst-italic fw-normal fs-6' : ''"
            >
              {{ paymentDisplay }}
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header bg-white border-bottom py-3">
            <span class="fw-semibold text-dark">{{ l("section_communication") }}</span>
          </div>
          <div class="card-body">
            <dl class="row mb-0 small">
              <dt class="col-5 text-muted">{{ l("label_notification") }}</dt>
              <dd class="col-7 mb-3">
                <span class="badge rounded-pill" :class="notificationBadge.class">{{ notificationBadge.text }}</span>
              </dd>

              <dt class="col-5 text-muted">{{ l("label_notify_time") }}</dt>
              <dd
                class="col-7 mb-0"
                :class="notifyTimeDisplay === l('empty_notify_time') ? 'text-muted fst-italic' : 'fw-medium'"
              >
                {{ notifyTimeDisplay }}
              </dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.comment-pre {
  white-space: pre-wrap;
}
</style>
