<template>
  <Head :title="$t('translation.leads.table')" />
  <div class="leads-table-page">
    <!-- Header with tabs - Same as Index page -->
    <div class="header-tabs mb-4 rounded">
      <div class="d-flex justify-content-between align-items-center px-3">
        <!-- Left side tabs -->
        <div class="tab-buttons">
          <LeadsNavTabs active="table" />
        </div>

        <!-- Right side actions -->
        <div class="tab-actions d-flex align-items-center gap-2 flex-wrap">
          <button
            type="button"
            class="btn btn-success"
            @click="handleExportLeads"
          >
            <i class="fa-solid fa-file-export me-1"></i>
            <span>{{ $t("translation.export") }}</span>
          </button>
          <button @click="handleOpenSettings" class="btn btn-outline-secondary">
            <i class="ri-settings-3-line me-1"></i>
            <span>{{ $t("translation.settings") }}</span>
          </button>
          <button @click="handleCreateLead" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>
            <span>{{ $t("translation.leads.add_lead") }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Table Component -->
    <LeadsTable
      ref="leadsTableRef"
      :collections="collections"
      :teachers="teachers"
      :boards="boards"
      :courses="courses"
      :causers="causers"
      :initial-search="search"
      :lead-dynamic-fields="leadDynamicFields"
    />

    <!-- Create Lead Modal -->
    <CreateLead
      v-if="showCreateLead"
      :collections="collections"
      :courses="courses"
      :teachers="teachers"
      :boards="boards"
      :lead-dynamic-fields="leadDynamicFields"
      @close="handleCloseCreateLead"
      @created="handleLeadCreated"
    />

    <!-- Lead Collection Settings Modal -->
    <LeadCollectionSettings
      v-if="showSettings"
      :collections="collections"
      :lead-dynamic-fields="leadDynamicFields"
      @close="handleCloseSettings"
      @updated="handleCollectionsUpdated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, defineAsyncComponent } from "vue";
import { router, Head } from "@inertiajs/vue3";
import { debounce } from "lodash";
import LeadsTable from "./Components/LeadsTable.vue";
import LeadsNavTabs from "./Components/LeadsNavTabs.vue";

// Import components
const CreateLead = defineAsyncComponent(() => import("./Components/CreateLead.vue"));
const LeadCollectionSettings = defineAsyncComponent(() =>
  import("./Components/LeadCollectionSettings.vue")
);

// Props
const props = defineProps({
  collections: Array,
  teachers: Array,
  boards: Array,
  courses: Array,
  causers: Array,
  search: String,
  leadDynamicFields: {
    type: Array,
    default: () => [],
  },
});

// Make collections reactive
const collections = ref([...props.collections]);

// Reactive data
const searchQuery = ref(props.search || "");
const searchInputRef = ref(null);
const showCreateLead = ref(false);
const showSettings = ref(false);
const leadsTableRef = ref(null);

const handleExportLeads = () => {
  leadsTableRef.value?.exportLeads?.();
};

// Methods
const handleSearch = debounce(() => {
  updateUrl();
}, 500);

const clearSearch = () => {
  searchQuery.value = "";
  updateUrl();
};

const updateUrl = () => {
  const params = {};
  if (searchQuery.value) {
    params.search = searchQuery.value;
  }

  router.visit(route("admin.leads.table-page"), {
    data: params,
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

// Create lead handlers
const handleCreateLead = () => {
  showCreateLead.value = true;
};

const handleCloseCreateLead = () => {
  showCreateLead.value = false;
};

const handleLeadCreated = (newLead) => {
  // Refresh the page to show the new lead
  router.reload();
  handleCloseCreateLead();
};

// Settings handlers
const handleOpenSettings = () => {
  showSettings.value = true;
};

const handleCloseSettings = () => {
  showSettings.value = false;
};

const handleCollectionsUpdated = (updatedCollections) => {
  // Update the reactive collections with new data
  if (updatedCollections && Array.isArray(updatedCollections)) {
    // Sort collections by order and update reactive ref
    collections.value = [...updatedCollections].sort((a, b) => a.order - b.order);
  } else {
    // Fallback: reload the page if no data provided
    router.reload();
  }
  handleCloseSettings();
};

// Initialize search input focus
onMounted(() => {
  if (searchInputRef.value && !searchQuery.value) {
    // Optional: Focus search input when page loads
  }
});
</script>

<style scoped>
.leads-table-page {
  padding: 0;
}

.header-tabs {
  background-color: #fff;
  padding: 13px 0;
}

.tab-actions .search-box {
  min-width: 300px;
}

.search-box .form-control {
  border: 1px solid #dee2e6;
}

.search-box .input-group-text {
  border: 1px solid #dee2e6;
}

@media (max-width: 768px) {
  .tab-actions .search-box {
    min-width: 200px;
  }

  .header-tabs .d-flex {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
