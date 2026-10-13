<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  active: {
    type: String,
    required: true,
    validator: (v) => ["leads", "table", "reminders", "archived", "none"].includes(v),
  },
  leadsAsButton: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["leads-click"]);

function tabClass(isActive) {
  return {
    "leads-nav-tabs__tab": true,
    "leads-nav-tabs__tab--active": isActive,
  };
}
</script>

<template>
  <nav class="leads-nav-tabs" aria-label="Leads navigation">
    <button
      v-if="leadsAsButton"
      type="button"
      :class="tabClass(active === 'leads')"
      @click="emit('leads-click')"
    >
      <i class="ri-user-line leads-nav-tabs__icon" aria-hidden="true" />
      <span>{{ $t("translation.leads.leads") }}</span>
    </button>
    <Link
      v-else
      :href="route('admin.leads.inertia')"
      :class="tabClass(active === 'leads')"
    >
      <i class="ri-user-line leads-nav-tabs__icon" aria-hidden="true" />
      <span>{{ $t("translation.leads.leads") }}</span>
    </Link>

    <Link :href="route('admin.leads.table-page')" :class="tabClass(active === 'table')">
      <i class="ri-table-line leads-nav-tabs__icon" aria-hidden="true" />
      <span>{{ $t("translation.leads.table") }}</span>
    </Link>

    <Link :href="route('admin.leads.my-reminders')" :class="tabClass(active === 'reminders')">
      <i class="ri-calendar-event-line leads-nav-tabs__icon" aria-hidden="true" />
      <span>{{ $t("translation.leads.reminders") }}</span>
    </Link>

    <Link :href="route('admin.leads.archived-page')" :class="tabClass(active === 'archived')">
      <i class="ri-archive-line leads-nav-tabs__icon" aria-hidden="true" />
      <span>{{ $t("translation.leads.archived") }}</span>
    </Link>
  </nav>
</template>

<style scoped>
.leads-nav-tabs {
  --lnt-rail-bg: #f1f5f9;
  --lnt-rail-border: #e2e8f0;
  --lnt-muted: #64748b;
  --lnt-muted-hover: #0f172a;
  --lnt-active-bg: #fff;
  --lnt-focus-ring: rgba(59, 130, 246, 0.45);

  display: flex;
  width: max-content;
  max-width: 100%;
  flex-wrap: nowrap;
  align-items: stretch;
  gap: 0.375rem;
  padding: 0.375rem;
  overflow-x: auto;
  border-radius: 0.625rem;
  border: 1px solid var(--lnt-rail-border);
  background: var(--lnt-rail-bg);
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.leads-nav-tabs::-webkit-scrollbar {
  display: none;
}

.leads-nav-tabs__tab {
  position: relative;
  z-index: 0;
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin: 0;
  white-space: nowrap;
  border: none;
  border-radius: 0.5rem;
  padding: 0.625rem 1rem;
  font-size: 0.8125rem;
  font-weight: 600;
  line-height: 1.3;
  letter-spacing: 0.01em;
  text-decoration: none;
  color: var(--lnt-muted);
  background: transparent;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  transition:
    color 0.18s ease,
    background-color 0.18s ease,
    box-shadow 0.18s ease;
}

.leads-nav-tabs__tab:hover {
  color: var(--lnt-muted-hover);
  background: rgba(255, 255, 255, 0.72);
}

.leads-nav-tabs__tab:focus {
  outline: none;
}

.leads-nav-tabs__tab:focus-visible {
  outline: none;
  box-shadow:
    0 0 0 2px var(--lnt-rail-bg),
    0 0 0 4px var(--lnt-focus-ring);
}

.leads-nav-tabs__tab--active {
  z-index: 1;
  color: var(--bs-primary, #405189);
  background: var(--lnt-active-bg);
  box-shadow:
    0 1px 3px rgba(15, 23, 42, 0.12),
    0 0 0 1px rgba(15, 23, 42, 0.06);
}

.leads-nav-tabs__tab--active:hover {
  color: var(--bs-primary, #405189);
  background: var(--lnt-active-bg);
}

.leads-nav-tabs__tab--active:focus-visible {
  box-shadow:
    0 1px 3px rgba(15, 23, 42, 0.12),
    0 0 0 1px rgba(15, 23, 42, 0.06),
    0 0 0 3px var(--lnt-focus-ring);
}

.leads-nav-tabs__icon {
  flex-shrink: 0;
  font-size: 1.0625rem;
  line-height: 1;
}

@media (min-width: 576px) {
  .leads-nav-tabs__tab {
    padding: 0.6875rem 1.125rem;
    font-size: 0.875rem;
  }
}
</style>
