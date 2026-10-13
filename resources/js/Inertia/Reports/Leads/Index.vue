<script setup>
import { ref, onMounted } from "vue";
import { Link, Head } from "@inertiajs/vue3";

// Reactive data
const reportCategories = ref([
  {
    id: "overview",
    title: "Overview & Statistics",
    description: "Get comprehensive insights into your leads performance",
    icon: "ri-dashboard-line",
    color: "primary",
    route: "reports.leads-overview",
    stats: { value: "12,450", label: "Total Leads" },
  },
  {
    id: "calls",
    title: "Calls Analytics",
    description: "Track call performance, success rates and call duration",
    icon: "ri-phone-line",
    color: "success",
    route: "reports.leads-calls",
    stats: { value: "2,340", label: "Total Calls" },
  },
  {
    id: "conversion",
    title: "Sales & Conversion",
    description: "Monitor conversion rates and sales funnel performance",
    icon: "ri-money-dollar-circle-line",
    color: "warning",
    route: "reports.leads-conversion",
    stats: { value: "18.5%", label: "Conversion Rate" },
  },
  {
    id: "marketing",
    title: "Marketing Analytics",
    description: "Analyze lead sources, campaigns and marketing ROI",
    icon: "ri-megaphone-line",
    color: "info",
    route: "reports.leads-marketing",
    stats: { value: "8", label: "Active Campaigns" },
  },
  {
    id: "team",
    title: "Team Performance",
    description: "Compare team member performance and productivity",
    icon: "ri-team-line",
    color: "secondary",
    route: "reports.leads-team",
    stats: { value: "15", label: "Team Members" },
  },
  {
    id: "trends",
    title: "Trends & Forecasting",
    description: "View historical trends and forecasting analytics",
    icon: "ri-line-chart-line",
    color: "dark",
    route: "reports.leads-trends",
    stats: { value: "+23%", label: "Growth Rate" },
  },
]);

const quickStats = ref([
  { label: "New Leads Today", value: "45", icon: "ri-user-add-line", color: "primary" },
  { label: "Calls Made Today", value: "128", icon: "ri-phone-line", color: "success" },
  { label: "Converted Today", value: "12", icon: "ri-check-line", color: "warning" },
  { label: "Active Campaigns", value: "8", icon: "ri-megaphone-line", color: "info" },
]);

// Methods
const getColorClass = (color) => {
  const colorMap = {
    primary: "primary",
    success: "success",
    warning: "warning",
    info: "info",
    secondary: "secondary",
    dark: "dark",
  };
  return colorMap[color] || "primary";
};

onMounted(() => {
  // Initialize any data if needed
});
</script>

<template>
  <Head title="Leads Reports Dashboard" />

  <div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Leads Reports Dashboard</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item">
                <Link href="/admin/dashboard">Dashboard</Link>
              </li>
              <li class="breadcrumb-item">
                <Link href="/admin/reports">Reports</Link>
              </li>
              <li class="breadcrumb-item active">Leads Reports</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light">
            <h5 class="card-title mb-0">
              <i class="ri-dashboard-line me-2"></i>Quick Overview
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div
                v-for="stat in quickStats"
                :key="stat.label"
                class="col-lg-3 col-md-6 mb-3"
              >
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div
                      class="avatar-sm rounded-circle"
                      :class="`bg-${getColorClass(stat.color)}-subtle`"
                    >
                      <span
                        class="avatar-title rounded-circle"
                        :class="`bg-${getColorClass(
                          stat.color
                        )}-subtle text-${getColorClass(stat.color)}`"
                      >
                        <i :class="stat.icon" class="fs-16"></i>
                      </span>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p
                      class="text-uppercase fw-medium text-muted text-truncate fs-13 mb-1"
                    >
                      {{ stat.label }}
                    </p>
                    <h4 class="mb-0" :class="`text-${getColorClass(stat.color)}`">
                      {{ stat.value }}
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Categories -->
    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light">
            <h5 class="card-title mb-0">
              <i class="ri-file-chart-line me-2"></i>Report Categories
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="row">
              <div
                v-for="category in reportCategories"
                :key="category.id"
                class="col-lg-4 col-md-6 mb-4"
              >
                <div class="card h-100 border-0 shadow-sm report-card">
                  <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                      <div class="flex-shrink-0">
                        <div
                          class="avatar-md rounded-circle"
                          :class="`bg-${getColorClass(category.color)}-subtle`"
                        >
                          <span
                            class="avatar-title rounded-circle"
                            :class="`bg-${getColorClass(
                              category.color
                            )}-subtle text-${getColorClass(category.color)}`"
                          >
                            <i :class="category.icon" class="fs-20"></i>
                          </span>
                        </div>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-2">{{ category.title }}</h5>
                        <p class="text-muted mb-3 fs-14">{{ category.description }}</p>

                        <!-- Quick Stat -->
                        <div class="d-flex align-items-center mb-3">
                          <span
                            class="badge fs-12 px-3 py-2"
                            :class="`bg-${getColorClass(
                              category.color
                            )}-subtle text-${getColorClass(category.color)}`"
                          >
                            {{ category.stats.value }}
                          </span>
                          <small class="text-muted ms-2">{{
                            category.stats.label
                          }}</small>
                        </div>

                        <!-- Action Button -->
                        <Link
                          :href="`/admin/reports/leads/${category.id}`"
                          class="btn btn-sm w-100"
                          :class="`btn-outline-${getColorClass(category.color)}`"
                          preserve-scroll
                        >
                          <i class="ri-arrow-right-line me-1"></i>
                          View {{ category.title }}
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light">
            <h5 class="card-title mb-0">
              <i class="ri-time-line me-2"></i>Recent Activity
            </h5>
          </div>
          <div class="card-body">
            <div class="activity-feed">
              <div class="activity-item d-flex align-items-start mb-3">
                <div
                  class="activity-icon bg-success-subtle text-success rounded-circle me-3"
                >
                  <i class="ri-phone-line fs-14"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1">New call report generated</h6>
                  <p class="text-muted mb-0 fs-13">
                    Today's call analytics updated successfully
                  </p>
                  <small class="text-muted">2 minutes ago</small>
                </div>
              </div>
              <div class="activity-item d-flex align-items-start mb-3">
                <div
                  class="activity-icon bg-warning-subtle text-warning rounded-circle me-3"
                >
                  <i class="ri-user-add-line fs-14"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1">45 new leads added</h6>
                  <p class="text-muted mb-0 fs-13">
                    Marketing campaign brought new prospects
                  </p>
                  <small class="text-muted">15 minutes ago</small>
                </div>
              </div>
              <div class="activity-item d-flex align-items-start">
                <div class="activity-icon bg-info-subtle text-info rounded-circle me-3">
                  <i class="ri-megaphone-line fs-14"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1">Marketing report updated</h6>
                  <p class="text-muted mb-0 fs-13">
                    Campaign performance metrics refreshed
                  </p>
                  <small class="text-muted">1 hour ago</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light">
            <h5 class="card-title mb-0">
              <i class="ri-settings-line me-2"></i>Quick Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <Link
                href="/admin/leads/inertia"
                class="btn btn-outline-primary"
                preserve-scroll
              >
                <i class="ri-user-line me-2"></i>View All Leads
              </Link>
              <Link
                href="/admin/leads/my-reminders"
                class="btn btn-outline-success"
                preserve-scroll
              >
                <i class="ri-calendar-event-line me-2"></i>My Reminders
              </Link>
              <Link
                href="/admin/leads/archived-page"
                class="btn btn-outline-secondary"
                preserve-scroll
              >
                <i class="ri-archive-line me-2"></i>Archived Leads
              </Link>
              <Link
                href="/admin/reports/leads"
                class="btn btn-outline-info"
                preserve-scroll
              >
                <i class="ri-bar-chart-line me-2"></i>Legacy Reports
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.report-card {
  transition: all 0.3s ease;
  cursor: pointer;
}

.report-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.activity-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.page-title-box {
  padding: 1.5rem 0;
}

.card {
  border-radius: 0.5rem;
}

.avatar-md {
  width: 3rem;
  height: 3rem;
}

.avatar-sm {
  width: 2.5rem;
  height: 2.5rem;
}

.badge {
  font-weight: 500;
}

.btn {
  transition: all 0.15s ease;
}

.btn:hover {
  transform: translateY(-1px);
}

.activity-feed {
  position: relative;
}

.activity-feed::before {
  content: "";
  position: absolute;
  left: 16px;
  top: 40px;
  bottom: 20px;
  width: 2px;
  background: linear-gradient(to bottom, #e9ecef, transparent);
}

.activity-item:last-child::after {
  display: none;
}
</style>
