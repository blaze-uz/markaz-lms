<script setup>
import {
  reactive,
  defineAsyncComponent,
  onMounted,
  watch,
  ref,
  nextTick,
  computed,
} from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import LeadsNavTabs from "./Components/LeadsNavTabs.vue";

// Lazy load components
const LeadShow = defineAsyncComponent(() => import("./Show.vue"));
const CreateLead = defineAsyncComponent(() => import("./Components/CreateLead.vue"));
const EditLead = defineAsyncComponent(() => import("./Components/EditLead.vue"));
const DeleteConfirmModal = defineAsyncComponent(() =>
  import("./Components/DeleteConfirmModal.vue")
);
const ConvertToStudent = defineAsyncComponent(() =>
  import("./Components/ConvertToStudent.vue")
);
const LeadsTable = defineAsyncComponent(() => import("./Components/LeadsTable.vue"));
const LeadCollectionSettings = defineAsyncComponent(() =>
  import("./Components/LeadCollectionSettings.vue")
);

const props = defineProps({
  collections: Array,
  leads: Object,
  leadCounts: Object,
  teachers: Array,
  boards: Array,
  courses: Array,
  groups: Array,
  meta: Object,
  leadDynamicFields: {
    type: Array,
    default: () => [],
  },
});

// Make collections reactive
const collections = ref([...props.collections]);

// Reactive leads data for drag and drop
const leadsData = reactive({});

// Initialize and sort leads data
Object.keys(props.leads).forEach((collectionId) => {
  leadsData[collectionId] = [...props.leads[collectionId]].sort(
    (a, b) => (a.order || 0) - (b.order || 0)
  );
});

// Track loaded counts and loading states
const loadedCounts = reactive({});
const loadingStates = reactive({});
const hasMore = reactive({});

// Initialize tracking data
collections.value.forEach((collection) => {
  const currentCount = leadsData[collection.id] ? leadsData[collection.id].length : 0;
  const totalCount = props.leadCounts[collection.id] || 0;

  loadedCounts[collection.id] = currentCount;
  loadingStates[collection.id] = false;
  hasMore[collection.id] = currentCount < totalCount;
});

const getLeadCount = (collectionId) => {
  return props.leadCounts[collectionId] || 0;
};

const getLoadedCount = (collectionId) => {
  return loadedCounts[collectionId] || 0;
};

// Drag and drop functionality
let draggedLead = null;
let draggedFromCollection = null;

const onDragStart = (event, lead, fromCollectionId) => {
  draggedLead = lead;
  draggedFromCollection = fromCollectionId;
  event.dataTransfer.effectAllowed = "move";
  event.target.classList.add("opacity-50", "shadow-sm");
  event.target.style.transform = "scale(1.02)";
};

const onDragEnd = (event) => {
  event.target.classList.remove("opacity-50", "shadow-sm");
  event.target.style.transform = "";
  // Remove drag-over classes from all drop zones
  document.querySelectorAll(".drop-zone").forEach((el) => {
    el.classList.remove(
      "bg-primary-subtle",
      "border-primary",
      "border-2",
      "border-dashed"
    );
  });
  // Remove drop indicators from all items
  document.querySelectorAll(".list-group-item").forEach((el) => {
    el.classList.remove("border-top", "border-primary", "border-3");
  });
  draggedLead = null;
  draggedFromCollection = null;
};

const onDragOver = (event) => {
  event.preventDefault();
  event.dataTransfer.dropEffect = "move";

  // Add Bootstrap classes for visual feedback
  if (event.currentTarget.classList.contains("drop-zone")) {
    event.currentTarget.classList.add(
      "bg-primary-subtle",
      "border-primary",
      "border-2",
      "border-dashed"
    );
  }

  // Add drop indicator for individual items
  if (event.currentTarget.classList.contains("list-group-item")) {
    event.currentTarget.classList.add("border-top", "border-primary", "border-3");
  }
};

const onDragLeave = (event) => {
  // Remove visual feedback when leaving drop zones
  if (!event.currentTarget.contains(event.relatedTarget)) {
    event.currentTarget.classList.remove(
      "bg-primary-subtle",
      "border-primary",
      "border-2",
      "border-dashed",
      "border-top",
      "border-3"
    );
  }
};

const onDrop = async (event, toCollectionId, targetLead = null) => {
  event.preventDefault();

  if (!draggedLead || draggedFromCollection === toCollectionId) {
    return;
  }

  try {
    // Remove lead from original collection
    const fromLeads = leadsData[draggedFromCollection];
    const leadIndex = fromLeads.findIndex((l) => l.id === draggedLead.id);
    if (leadIndex > -1) {
      fromLeads.splice(leadIndex, 1);
    }

    // Add lead to new collection at the right position
    if (!leadsData[toCollectionId]) {
      leadsData[toCollectionId] = [];
    }

    // Remove the lead from the target collection if it exists there
    const existingIndex = leadsData[toCollectionId].findIndex(
      (l) => l.id === draggedLead.id
    );
    if (existingIndex > -1) {
      leadsData[toCollectionId].splice(existingIndex, 1);
    }

    let targetIndex = 0; // Default to top of collection

    // If we have a target lead, insert before it
    if (targetLead) {
      targetIndex = leadsData[toCollectionId].findIndex((l) => l.id === targetLead.id);
      if (targetIndex === -1) {
        targetIndex = leadsData[toCollectionId].length; // If not found, add to end
      }
    }

    // Insert the lead at the target position
    leadsData[toCollectionId].splice(targetIndex, 0, draggedLead);

    // Update order for all leads in both collections
    const reorderItems = [];

    // Update orders in the source collection (reindex from 0)
    fromLeads.forEach((lead, index) => {
      lead.order = index;
      reorderItems.push({
        id: lead.id,
        order: index,
        collection_id: draggedFromCollection,
      });
    });

    // Update orders in the target collection (reindex from 0)
    leadsData[toCollectionId].forEach((lead, index) => {
      lead.order = index;
      reorderItems.push({
        id: lead.id,
        order: index,
        collection_id: toCollectionId,
      });
    });

    console.log("DEBUG: Reorder items being sent:", reorderItems.slice(0, 10)); // Log first 10 items

    // Get the final order of the dragged lead after reindexing
    const finalOrder = leadsData[toCollectionId].findIndex(
      (l) => l.id === draggedLead.id
    );

    // Debug logging
    console.log("CROSS-COLLECTION MOVE DEBUG:");
    console.log("Dragged lead ID:", draggedLead.id);
    console.log("From collection:", draggedFromCollection);
    console.log("To collection:", toCollectionId);
    console.log("Target index:", targetIndex);
    console.log("Final order:", finalOrder);
    console.log("Reorder items count:", reorderItems.length);
    console.log(
      "Target collection leads after move:",
      leadsData[toCollectionId].map((l) => ({ id: l.id, name: l.name, order: l.order }))
    );

    // Send update to backend
    await axios.post(
      "/admin/leads/update-order",
      {
        lead_id: draggedLead.id,
        collection_id: toCollectionId,
        order: finalOrder,
        reorder_items: reorderItems,
      },
      {
        headers: {
          "Content-Type": "application/json",
        },
      }
    );
  } catch (error) {
    console.error("Error moving lead:", error);
    // Revert the change if API call fails
    window.location.reload();
  }
};

const onDropOnLead = async (event, targetLead, collectionId) => {
  event.preventDefault();

  if (!draggedLead || draggedLead.id === targetLead.id) {
    return;
  }

  // Check if it's within the same collection or between collections
  if (draggedFromCollection === collectionId) {
    // Within same collection - reorder
    await onDropWithinCollection(event, targetLead, collectionId);
  } else {
    // Between collections - move to position before target lead
    await onDrop(event, collectionId, targetLead);
  }
};

const onDropWithinCollection = async (event, targetLead, collectionId) => {
  event.preventDefault();

  if (
    !draggedLead ||
    draggedLead.id === targetLead.id ||
    draggedFromCollection !== collectionId
  ) {
    return;
  }

  try {
    const leads = leadsData[collectionId];
    const draggedIndex = leads.findIndex((l) => l.id === draggedLead.id);
    const targetIndex = leads.findIndex((l) => l.id === targetLead.id);

    if (draggedIndex > -1 && targetIndex > -1) {
      // Remove dragged lead and insert at target position
      const [removed] = leads.splice(draggedIndex, 1);
      leads.splice(targetIndex, 0, removed);

      // Update order for all leads in collection
      const reorderItems = leads.map((lead, index) => {
        lead.order = index;
        return {
          id: lead.id,
          order: index,
          collection_id: collectionId,
        };
      });

      // Send update to backend
      await axios.post(
        "/admin/leads/update-order",
        {
          lead_id: draggedLead.id,
          collection_id: collectionId,
          order: targetIndex,
          reorder_items: reorderItems,
        },
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
  } catch (error) {
    console.error("Error reordering lead:", error);
    // Revert the change if API call fails
    window.location.reload();
  }
};

// Infinite scroll functionality
const loadMoreLeads = async (collectionId) => {
  if (loadingStates[collectionId] || !hasMore[collectionId]) {
    return;
  }

  loadingStates[collectionId] = true;

  try {
    const response = await axios.get("/admin/leads/load-more", {
      params: {
        collection_id: collectionId,
        offset: loadedCounts[collectionId],
        limit: 50,
      },
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (response.data.success) {
      // Add new leads to existing ones
      if (!leadsData[collectionId]) {
        leadsData[collectionId] = [];
      }

      // Append new leads and sort by order to maintain proper positioning
      leadsData[collectionId].push(...response.data.leads);
      leadsData[collectionId].sort((a, b) => (a.order || 0) - (b.order || 0));

      loadedCounts[collectionId] += response.data.leads.length;
      hasMore[collectionId] = response.data.hasMore;
    }
  } catch (error) {
    console.error("Error loading more leads:", error);
  } finally {
    loadingStates[collectionId] = false;
  }
};

// Scroll event handler for infinite scroll
const handleScroll = (event, collectionId) => {
  // Don't load more during search or when filters are active - these show all results at once
  if (searchQuery.value.trim() || hasActiveFilters.value) {
    return;
  }

  const element = event.target;
  const threshold = 100; // Load more when 100px from bottom

  // Check if user has scrolled to the bottom or near bottom
  const distanceFromBottom =
    element.scrollHeight - (element.scrollTop + element.clientHeight);

  if (
    distanceFromBottom <= threshold &&
    hasMore[collectionId] &&
    !loadingStates[collectionId]
  ) {
    loadMoreLeads(collectionId);
  }
};

// Add state for showing lead details
const showLeadDetails = reactive({
  visible: false,
  lead: null,
  activeTab: "details",
});

// Add state for create lead modal
const showCreateLead = ref(false);

// Add state for edit lead modal
const showEditLead = ref(false);
const editingLead = ref(null);

// Add state for delete confirmation modal
const showDeleteConfirm = ref(false);
const deletingLead = ref(null);
const deleteModalRef = ref(null);

// Convert to student modal state
const showConvertModal = ref(false);
const convertingLead = ref(null);

// Settings modal state
const showSettings = ref(false);

// Filters state
const showFilters = ref(false);
const filters = reactive({
  selectedChosenDays: "",
  selectedTeacher: "",
  selectedBoard: "",
  selectedCourse: "",
  selectedAttend: "",
  dateRange: "",
});

// Add state for archived view
const activeTab = ref("leads"); // 'leads', 'tasks', 'archived'

// Search functionality
const searchQuery = reactive({ value: "" });
const isSearching = reactive({ value: false });
const searchResults = ref(null); // Store search results separately
const searchInputRef = ref(null); // Reference to search input

// Function to get URL parameters
const getUrlParam = (param) => {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(param);
};

// Function to update URL parameters
const updateUrlParam = (param, value) => {
  const url = new URL(window.location);
  if (value) {
    url.searchParams.set(param, value);
  } else {
    url.searchParams.delete(param);
  }
  window.history.replaceState({}, "", url);
};

// Function to update filters in URL
const updateFiltersInUrl = () => {
  const url = new URL(window.location);

  // Update or remove filter parameters
  if (filters.selectedChosenDays) {
    url.searchParams.set("chosenDays", filters.selectedChosenDays);
  } else {
    url.searchParams.delete("chosenDays");
  }

  if (filters.selectedTeacher) {
    url.searchParams.set("teacher", filters.selectedTeacher);
  } else {
    url.searchParams.delete("teacher");
  }

  if (filters.selectedBoard) {
    url.searchParams.set("board", filters.selectedBoard);
  } else {
    url.searchParams.delete("board");
  }

  if (filters.selectedCourse) {
    url.searchParams.set("course", filters.selectedCourse);
  } else {
    url.searchParams.delete("course");
  }

  if (filters.selectedAttend) {
    url.searchParams.set("attend", filters.selectedAttend);
  } else {
    url.searchParams.delete("attend");
  }

  if (filters.dateRange) {
    url.searchParams.set("dateRange", filters.dateRange);
  } else {
    url.searchParams.delete("dateRange");
  }

  window.history.replaceState({}, "", url);
};

// Function to initialize filters from URL
const initializeFiltersFromUrl = () => {
  const urlParams = new URLSearchParams(window.location.search);

  filters.selectedChosenDays = urlParams.get("chosenDays") || "";
  filters.selectedTeacher = urlParams.get("teacher") || "";
  filters.selectedBoard = urlParams.get("board") || "";
  filters.selectedCourse = urlParams.get("course") || "";
  filters.selectedAttend = urlParams.get("attend") || "";
  filters.dateRange = urlParams.get("dateRange") || "";
};

// Function to format date as dd.mm.yyyy
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();
  return `${day}.${month}.${year}`;
};

// Function to find lead by ID across all collections
const findLeadById = (leadId) => {
  // Convert to number for comparison since URL params are strings
  const numericLeadId = parseInt(leadId);
  for (const collectionId in leadsData) {
    const lead = leadsData[collectionId].find(
      (l) => l.id === numericLeadId || l.id == leadId
    );
    if (lead) {
      return lead;
    }
  }
  return null;
};

// Function to show lead details
const handleShowLead = (lead, activeTab = "details") => {
  showLeadDetails.lead = lead;
  showLeadDetails.visible = true;
  showLeadDetails.activeTab = activeTab;
  updateUrlParam("lead", lead.id);
};

// Function to show lead with calls tab
const handleShowLeadCalls = (lead) => {
  handleShowLead(lead, "calls");
};

// Function to close lead details
const handleCloseLead = () => {
  showLeadDetails.visible = false;
  showLeadDetails.lead = null;
  showLeadDetails.activeTab = "details";
  updateUrlParam("lead", null);
};

// Function to fetch lead by ID if not found in current data
const fetchLeadById = async (leadId) => {
  try {
    const response = await axios.get(`/admin/leads/${leadId}/details`, {
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content"),
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });

    if (response.data) {
      showLeadDetails.lead = response.data;
      showLeadDetails.visible = true;
    }
  } catch (error) {
    console.error("Error fetching lead:", error);
    // Only remove URL parameter if it's a 404, not auth issues
    if (error.response && error.response.status === 404) {
      updateUrlParam("lead", null);
    }
  }
};

// Search functionality with backend
let searchTimeout = null;

const performSearch = async (query) => {
  if (isSearching.value) return;

  // Store current focus state
  const wasSearchInputFocused = document.activeElement === searchInputRef.value;

  isSearching.value = true;

  try {
    // Update URL with search parameter
    const url = new URL(window.location);
    if (query.trim()) {
      url.searchParams.set("search", query.trim());
    } else {
      url.searchParams.delete("search");
    }
    window.history.replaceState({}, "", url);

    // Make direct API call for search
    const searchParams = {
      search: query.trim() || undefined,
    };

    // Include active filters in search
    if (hasActiveFilters.value) {
      searchParams.selectedChosenDays = filters.selectedChosenDays || undefined;
      searchParams.selectedTeacher = filters.selectedTeacher || undefined;
      searchParams.selectedBoard = filters.selectedBoard || undefined;
      searchParams.selectedCourse = filters.selectedCourse || undefined;
      searchParams.selectedAttend = filters.selectedAttend || undefined;
      searchParams.dateRange = filters.dateRange || undefined;
      // When filters are active, get all results
      searchParams.limit = 1000;
      searchParams.offset = 0;
    } else {
      // When clearing search (no query), limit to 50 leads per collection
      if (!query.trim()) {
        searchParams.limit = 50;
        searchParams.offset = 0;
      }
    }

    const response = await axios.get("/admin/leads/search", {
      params: searchParams,
    });

    if (response.data) {
      if (query.trim()) {
        // Store search results
        searchResults.value = response.data;

        // Update leads data with search results using Vue.set equivalent
        Object.keys(response.data.leads).forEach((collectionId) => {
          // Force reactivity by creating new array
          leadsData[collectionId] = [...response.data.leads[collectionId]];
        });

        // Update lead counts
        Object.keys(response.data.leadCounts).forEach((collectionId) => {
          loadedCounts[collectionId] = response.data.leadCounts[collectionId];
        });
      } else {
        // Clear search - restore original data
        searchResults.value = null;

        // Restore original leads data for all collections
        collections.value.forEach((collection) => {
          const collectionId = collection.id;
          const collectionLeads = response.data.leads[collectionId] || [];
          const totalCount = response.data.leadCounts[collectionId] || 0;

          leadsData[collectionId] = [...collectionLeads];
          loadedCounts[collectionId] = collectionLeads.length;
          hasMore[collectionId] = collectionLeads.length < totalCount;
        });
      }
    }
  } catch (error) {
    console.error("Search error:", error);
  } finally {
    isSearching.value = false;

    // Restore focus to search input if it was focused before
    if (wasSearchInputFocused && searchInputRef.value) {
      // Use nextTick to ensure DOM has updated
      nextTick(() => {
        searchInputRef.value.focus();
        // Restore cursor position to end
        const length = searchInputRef.value.value.length;
        searchInputRef.value.setSelectionRange(length, length);
      });
    }
  }
};

// Debounced search
const debouncedSearch = (query) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    performSearch(query);
  }, 500); // 500ms delay
};

// Watch for search query changes
watch(
  () => searchQuery.value,
  (newQuery) => {
    debouncedSearch(newQuery);
  }
);

// Clear search
const clearSearch = async () => {
  // Set loading state
  isSearching.value = true;

  try {
    searchQuery.value = "";

    // Clear URL parameter
    const url = new URL(window.location);
    url.searchParams.delete("search");
    window.history.replaceState({}, "", url);

    // Reset search state
    searchResults.value = null;

    // Manually reload original data by calling the search endpoint without query
    const clearParams = {
      limit: 50, // Only load first 50 leads per collection like initial load
      offset: 0,
    };

    // Include active filters when clearing search
    if (hasActiveFilters.value) {
      clearParams.selectedChosenDays = filters.selectedChosenDays || undefined;
      clearParams.selectedTeacher = filters.selectedTeacher || undefined;
      clearParams.selectedBoard = filters.selectedBoard || undefined;
      clearParams.selectedCourse = filters.selectedCourse || undefined;
      clearParams.selectedAttend = filters.selectedAttend || undefined;
      clearParams.dateRange = filters.dateRange || undefined;
      // When filters are active, get all results
      clearParams.limit = 1000;
      clearParams.offset = 0;
    }

    const response = await axios.get("/admin/leads/search", {
      params: clearParams,
    });

    if (response.data) {
      // Restore original leads data for all collections
      props.collections.forEach((collection) => {
        const collectionId = collection.id;
        const collectionLeads = response.data.leads[collectionId] || [];
        const totalCount = response.data.leadCounts[collectionId] || 0;

        leadsData[collectionId] = [...collectionLeads];
        loadedCounts[collectionId] = collectionLeads.length;
        // When filters are active, hasMore should be false since we're showing all results
        hasMore[collectionId] = hasActiveFilters.value
          ? false
          : collectionLeads.length < totalCount;
      });
    }
  } catch (error) {
    console.error("Error clearing search:", error);
    // Fallback to Inertia reload if API call fails
    router.reload({
      only: ["leads", "leadCounts", "collections"],
      preserveScroll: false,
    });
  } finally {
    isSearching.value = false;
  }
};

// Handle create lead
const handleCreateLead = () => {
  showCreateLead.value = true;
};

const handleCloseCreateLead = () => {
  showCreateLead.value = false;
};

const handleLeadCreated = (newLead) => {
  // Add the new lead to the top of the appropriate collection
  if (!leadsData[newLead.collection_id]) {
    leadsData[newLead.collection_id] = [];
  }

  // Shift all existing leads down and add new lead at the top
  leadsData[newLead.collection_id].forEach((lead) => {
    lead.order = lead.order + 1;
  });

  leadsData[newLead.collection_id].unshift(newLead);

  // Update counts
  loadedCounts[newLead.collection_id] = (loadedCounts[newLead.collection_id] || 0) + 1;

  // Close the modal
  showCreateLead.value = false;
};

// Handle edit lead
const handleEditLead = (lead) => {
  editingLead.value = lead;
  showEditLead.value = true;
};

const handleCloseEditLead = () => {
  showEditLead.value = false;
  editingLead.value = null;
};

const handleLeadUpdated = (updatedLead) => {
  // Find the lead in all collections and update it
  for (const collectionId in leadsData) {
    const leadIndex = leadsData[collectionId].findIndex((l) => l.id === updatedLead.id);
    if (leadIndex !== -1) {
      // Update the lead data
      leadsData[collectionId][leadIndex] = {
        ...leadsData[collectionId][leadIndex],
        ...updatedLead,
      };
      break;
    }
  }

  // If the lead was moved to a different collection, handle the move
  if (updatedLead.collection_id !== editingLead.value.collection_id) {
    // Remove from old collection
    const oldCollectionId = editingLead.value.collection_id;
    const oldLeadIndex = leadsData[oldCollectionId].findIndex(
      (l) => l.id === updatedLead.id
    );
    if (oldLeadIndex !== -1) {
      leadsData[oldCollectionId].splice(oldLeadIndex, 1);
      loadedCounts[oldCollectionId] = (loadedCounts[oldCollectionId] || 1) - 1;
    }

    // Add to new collection
    if (!leadsData[updatedLead.collection_id]) {
      leadsData[updatedLead.collection_id] = [];
    }
    leadsData[updatedLead.collection_id].unshift(updatedLead);
    loadedCounts[updatedLead.collection_id] =
      (loadedCounts[updatedLead.collection_id] || 0) + 1;
  }

  // Close the modal
  showEditLead.value = false;
  editingLead.value = null;
};

// Handle delete lead - show confirmation modal
const handleDeleteLead = (lead) => {
  deletingLead.value = lead;
  showDeleteConfirm.value = true;
  // Close lead details modal if open
  if (showLeadDetails.visible) {
    handleCloseLead();
  }
};

// Handle delete confirmation
const handleDeleteConfirm = async (lead) => {
  if (!lead) return;

  try {
    // Set loading state in modal
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
      // Remove lead from the UI
      for (const collectionId in leadsData) {
        const leadIndex = leadsData[collectionId].findIndex((l) => l.id === lead.id);
        if (leadIndex !== -1) {
          leadsData[collectionId].splice(leadIndex, 1);
          loadedCounts[collectionId] = Math.max((loadedCounts[collectionId] || 1) - 1, 0);
          break;
        }
      }

      // Show success message
      if (window.notifyMessage) {
        window.notifyMessage(response.data.message, "success");
      }

      // Close modal
      handleDeleteCancel();
    }
  } catch (error) {
    console.error("Error deleting lead:", error);

    // Show error message
    if (window.notifyMessage) {
      const errorMessage = error.response?.data?.message || "Failed to delete lead";
      window.notifyMessage(errorMessage, "danger");
    }
  } finally {
    // Remove loading state in modal
    if (deleteModalRef.value) {
      deleteModalRef.value.setLoading(false);
    }
  }
};

// Handle delete cancel
const handleDeleteCancel = () => {
  showDeleteConfirm.value = false;
  deletingLead.value = null;
};

// Handle tab switching
const handleTabChange = (tab) => {
  activeTab.value = tab;
};

// Handle convert to student
const handleConvertToStudent = (lead) => {
  convertingLead.value = lead;
  showConvertModal.value = true;
};

// Handle convert modal close
const handleCloseConvertModal = () => {
  showConvertModal.value = false;
  convertingLead.value = null;
};

// Handle convert success
const handleConvertSuccess = (lead) => {
  // Remove lead from UI since it's now a student
  for (const collectionId in leadsData) {
    const leadIndex = leadsData[collectionId].findIndex((l) => l.id === lead.id);
    if (leadIndex !== -1) {
      leadsData[collectionId].splice(leadIndex, 1);
      loadedCounts[collectionId] = Math.max((loadedCounts[collectionId] || 1) - 1, 0);
      break;
    }
  }
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

// Filter methods
const toggleFilters = () => {
  showFilters.value = !showFilters.value;
};

const applyFilters = async () => {
  if (isSearching.value) return;

  console.log("Applying filters:", filters);

  isSearching.value = true;

  // Update URL with filter parameters
  updateFiltersInUrl();

  try {
    const params = {
      search: searchQuery.value.trim() || undefined,
      selectedChosenDays: filters.selectedChosenDays || undefined,
      selectedTeacher: filters.selectedTeacher || undefined,
      selectedBoard: filters.selectedBoard || undefined,
      selectedCourse: filters.selectedCourse || undefined,
      selectedAttend: filters.selectedAttend || undefined,
      dateRange: filters.dateRange || undefined,
      // Request all results when filtering (no pagination)
      limit: 1000, // Large limit to get all filtered results
      offset: 0,
    };

    console.log("API request params:", params);

    const response = await axios.get("/admin/leads/search", {
      params,
    });

    console.log("API response:", response.data);

    if (response.data) {
      // Update leads data with filtered results
      Object.keys(response.data.leads).forEach((collectionId) => {
        leadsData[collectionId] = [...response.data.leads[collectionId]];
      });

      // Update lead counts
      Object.keys(response.data.leadCounts).forEach((collectionId) => {
        loadedCounts[collectionId] = response.data.leadCounts[collectionId];
        // When filtering, we want to show all results, so hasMore should be false
        hasMore[collectionId] = false;
      });

      console.log("Updated leads data:", leadsData);
    }
  } catch (error) {
    console.error("Filter error:", error);
  } finally {
    isSearching.value = false;
  }
};

const clearAllFilters = async () => {
  console.log("Clearing all filters");

  isSearching.value = true;

  filters.selectedChosenDays = "";
  filters.selectedTeacher = "";
  filters.selectedBoard = "";
  filters.selectedCourse = "";
  filters.selectedAttend = "";
  filters.dateRange = "";

  // Clear URL parameters
  updateFiltersInUrl();

  // Reset pagination state for all collections
  collections.value.forEach((collection) => {
    hasMore[collection.id] = true;
  });

  // Reset to original data with pagination
  try {
    const response = await axios.get("/admin/leads/search", {
      params: {
        limit: 50, // Reset to original pagination
        offset: 0,
      },
    });

    if (response.data) {
      // Restore original leads data for all collections
      collections.value.forEach((collection) => {
        const collectionId = collection.id;
        const collectionLeads = response.data.leads[collectionId] || [];
        const totalCount = response.data.leadCounts[collectionId] || 0;

        leadsData[collectionId] = [...collectionLeads];
        loadedCounts[collectionId] = collectionLeads.length;
        hasMore[collectionId] = collectionLeads.length < totalCount;
      });
    }
  } catch (error) {
    console.error("Error clearing filters:", error);
    // Fallback to page reload if API call fails
    router.reload({
      only: ["leads", "leadCounts", "collections"],
      preserveScroll: false,
    });
  } finally {
    isSearching.value = false;
  }
};

// Computed property for active filters
const hasActiveFilters = computed(() => {
  return !!(
    filters.selectedChosenDays ||
    filters.selectedTeacher ||
    filters.selectedBoard ||
    filters.selectedCourse ||
    filters.selectedAttend ||
    filters.dateRange
  );
});

// Initialize search and filters from URL parameters
onMounted(() => {
  // Initialize filters from URL
  initializeFiltersFromUrl();

  // If filters are present in URL, apply them
  if (hasActiveFilters.value) {
    applyFilters();
  }

  const searchParam = getUrlParam("search");
  if (searchParam) {
    searchQuery.value = searchParam;
  }

  const leadId = getUrlParam("lead");
  if (leadId) {
    const lead = findLeadById(leadId);
    if (lead) {
      handleShowLead(lead);
    } else {
      // If lead not found in current data, try to fetch it
      fetchLeadById(leadId);
    }
  }
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
          <LeadsNavTabs
            :active="activeTab === 'leads' ? 'leads' : 'none'"
            leads-as-button
            @leads-click="handleTabChange('leads')"
          />
        </div>

        <!-- Right side search and add -->
        <div
          class="tab-actions d-flex align-items-center gap-2"
          v-if="activeTab === 'leads'"
        >
          <div class="search-box me-2">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="ri-search-line text-muted"></i>
              </span>
              <input
                ref="searchInputRef"
                type="text"
                class="form-control bg-light border-start-0 p-0"
                :placeholder="$t('translation.leads.search')"
                v-model="searchQuery.value"
                :disabled="isSearching.value"
              />
              <button
                v-if="isSearching.value"
                class="btn btn-light border-start-0"
                type="button"
                disabled
              >
                <div class="spinner-border spinner-border-sm" role="status">
                  <span class="visually-hidden">Searching...</span>
                </div>
              </button>
              <button
                v-else-if="searchQuery.value"
                class="btn btn-light border-start-0"
                type="button"
                @click="clearSearch"
              >
                <i class="ri-close-line"></i>
              </button>
            </div>
          </div>
          <button
            class="btn btn-outline-secondary"
            @click="toggleFilters"
            :class="{ active: showFilters }"
            :disabled="isSearching.value"
          >
            <div
              v-if="isSearching.value && hasActiveFilters"
              class="spinner-border spinner-border-sm me-1"
              role="status"
            >
              <span class="visually-hidden">Loading...</span>
            </div>
            <i v-else class="ri-filter-3-line me-1"></i>
            <span class="d-none d-md-inline">{{ $t("translation.filter") }}</span>
          </button>
          <button class="btn btn-outline-secondary" @click="handleOpenSettings">
            <i class="ri-settings-3-line me-1"></i>
            <span class="d-none d-md-inline">{{ $t("translation.settings") }}</span>
          </button>
          <button class="btn btn-primary" @click="handleCreateLead">
            <i class="ri-add-line me-1"></i>
            <span>{{ $t("translation.leads.add_lead") }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Collapsible Filters Section -->
    <div
      v-if="activeTab === 'leads'"
      class="filters-section"
      :class="{ show: showFilters }"
    >
      <div class="card mb-3 shadow-sm">
        <div class="card-body py-3" :class="{ 'opacity-75': isSearching.value }">
          <div class="row g-2">
            <div class="col-6 col-md-4 col-lg-2">
              <select
                v-model="filters.selectedChosenDays"
                class="form-select form-select-sm"
                :disabled="isSearching.value"
                @change="applyFilters"
              >
                <option value="">{{ $t("translation.leads.chosen_days") }}</option>
                <option value="odd">{{ $t("translation.leads.odd") }}</option>
                <option value="even">{{ $t("translation.leads.even") }}</option>
              </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <select
                v-model="filters.selectedTeacher"
                class="form-select form-select-sm"
                :disabled="isSearching.value"
                @change="applyFilters"
              >
                <option value="">{{ $t("translation.leads.teacher") }}</option>
                <option
                  v-for="teacher in props.teachers"
                  :key="teacher.id"
                  :value="teacher.id"
                >
                  {{ teacher.name }}
                </option>
              </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <select
                v-model="filters.selectedBoard"
                class="form-select form-select-sm"
                :disabled="isSearching.value"
                @change="applyFilters"
              >
                <option value="">{{ $t("translation.leads.board") }}</option>
                <option v-for="board in props.boards" :key="board.id" :value="board.id">
                  {{ board.name }}
                </option>
              </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <select
                v-model="filters.selectedCourse"
                class="form-select form-select-sm"
                :disabled="isSearching.value"
                @change="applyFilters"
              >
                <option value="">{{ $t("translation.courses.course") }}</option>
                <option
                  v-for="course in props.courses"
                  :key="course.id"
                  :value="course.id"
                >
                  {{ course.name }}
                </option>
              </select>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <select
                v-model="filters.selectedAttend"
                class="form-select form-select-sm"
                :disabled="isSearching.value"
                @change="applyFilters"
              >
                <option value="">{{ $t("translation.leads.attend") }}</option>
                <option value="1">{{ $t("translation.yes") }}</option>
                <option value="0">{{ $t("translation.no") }}</option>
              </select>
            </div>
            <div class="col-12 col-md-4 col-lg-2">
              <select
                v-model="filters.dateRange"
                class="form-select form-select-sm"
                :disabled="isSearching.value"
                @change="applyFilters"
              >
                <option value="">{{ $t("translation.all_dates") }}</option>
                <option value="today">{{ $t("translation.today") }}</option>
                <option value="yesterday">{{ $t("translation.yesterday") }}</option>
                <option value="this_week">{{ $t("translation.this_week") }}</option>
                <option value="last_week">{{ $t("translation.last_week") }}</option>
                <option value="this_month">{{ $t("translation.this_month") }}</option>
                <option value="last_month">{{ $t("translation.last_month") }}</option>
                <option value="this_year">{{ $t("translation.this_year") }}</option>
              </select>
            </div>
          </div>
          <div class="row mt-2" v-if="hasActiveFilters">
            <div class="col-12">
              <button
                type="button"
                class="btn btn-link btn-sm p-0 text-decoration-none"
                :disabled="isSearching.value"
                @click="clearAllFilters"
              >
                <div
                  v-if="isSearching.value"
                  class="spinner-border spinner-border-sm me-1"
                  role="status"
                >
                  <span class="visually-hidden">Loading...</span>
                </div>
                <i v-else class="ri-close-circle-line me-1"></i>
                {{ $t("translation.clear_filters") }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content based on active tab -->
    <!-- Kanban board for leads -->
    <div v-if="activeTab === 'leads'" class="d-flex gap-3 kanban-board">
      <div v-for="collection in collections" :key="collection.id" class="kanban-column">
        <div class="card shadow-sm h-100" style="transition: all 0.2s ease">
          <div class="card-header bg-light p-3">
            <div class="d-flex align-items-center">
              <span v-if="collection.name.includes('📝')" class="me-2">📝</span>
              <span v-else-if="collection.name.includes('⏳')" class="me-2">⏳</span>
              <span v-else-if="collection.name.includes('✅')" class="me-2">✅</span>
              <span v-else class="me-2">📋</span>

              <h5 class="card-title mb-0">
                {{ collection.name.replace(/[📝⏳✅]/g, "").trim() }}
              </h5>
              <span class="badge bg-primary rounded-pill ms-2">
                <template v-if="searchQuery.value.trim()">
                  {{ leadsData[collection.id]?.length || 0 }}
                  <i class="ri-search-line ms-1" style="font-size: 10px"></i>
                </template>
                <template v-else-if="hasActiveFilters">
                  {{ leadsData[collection.id]?.length || 0 }}
                  <i class="ri-filter-line ms-1" style="font-size: 10px"></i>
                </template>
                <template v-else>
                  {{ getLoadedCount(collection.id) }} / {{ getLeadCount(collection.id) }}
                </template>
              </span>
            </div>
          </div>
          <div
            class="card-body p-0 overflow-auto drop-zone"
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop($event, collection.id)"
            @scroll="handleScroll($event, collection.id)"
          >
            <div class="list-group list-group-flush">
              <!-- Loading skeleton when searching -->
              <div v-if="isSearching.value" class="p-3">
                <div v-for="i in 3" :key="i" class="skeleton-lead-item mb-3">
                  <div class="d-flex align-items-start">
                    <div class="skeleton-drag-handle me-2"></div>
                    <div class="flex-grow-1">
                      <div class="skeleton-name mb-2"></div>
                      <div class="skeleton-phone mb-2"></div>
                      <div class="skeleton-date"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actual leads data -->
              <div
                v-else-if="
                  leadsData &&
                  leadsData[collection.id] &&
                  leadsData[collection.id].length > 0
                "
              >
                <div
                  v-for="lead in leadsData[collection.id]"
                  :key="lead.id"
                  class="list-group-item p-2 user-select-none"
                  draggable="true"
                  @dragstart="onDragStart($event, lead, collection.id)"
                  @dragend="onDragEnd"
                  @dragover="onDragOver"
                  @dragleave="onDragLeave"
                  @drop="onDropOnLead($event, lead, collection.id)"
                  @click="handleShowLead(lead)"
                  style="cursor: pointer"
                >
                  <div class="d-flex align-items-start">
                    <div class="me-2 text-muted">
                      <i class="ri-drag-move-line"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="mb-0 fs-6">{{ lead.name }}</h6>
                        <small class="text-muted">
                          <i class="ri-calendar-line me-1"></i>
                          {{ formatDate(lead.created_at) }}
                        </small>
                      </div>
                      <div class="d-flex align-items-center mb-1">
                        <i class="ri-phone-line me-1 text-muted"></i>
                        <a :href="'tel:' + lead.phone" class="text-body small">{{
                          lead.phone
                        }}</a>
                      </div>
                      <div class="d-flex align-items-center mb-1" v-if="lead.phone2">
                        <i class="ri-phone-line me-1 text-muted"></i>
                        <a :href="'tel:' + lead.phone2" class="text-body small">{{
                          lead.phone2
                        }}</a>
                      </div>
                      <div class="d-flex align-items-center mb-1" v-if="lead.address">
                        <i class="ri-map-pin-line me-1 text-muted"></i>
                        <span class="text-body small">{{ lead.address }}</span>
                      </div>
                      <div v-if="lead.teacher?.name" class="mb-1">
                        <small class="text-muted">
                          <i class="ri-user-line me-1"></i>
                          {{ lead.teacher.name }}
                        </small>
                      </div>
                      <div v-if="lead.board?.name" class="mb-1">
                        <small class="text-info">
                          <i class="ri-dashboard-line me-1"></i>
                          {{ lead.board.name }}
                        </small>
                      </div>
                      <div v-if="lead.session_id" class="mb-1">
                        <small class="text-success">
                          <i class="ri-target-line me-1"></i>
                          {{ $t("translation.leads.targeted") }}
                        </small>
                      </div>
                      <div class="d-flex align-items-center">
                        <div class="causer-avatar me-1">
                          <img
                            v-if="lead.causer?.avatar_url"
                            :src="lead.causer.avatar_url"
                            :alt="lead.causer.name"
                            class="causer-avatar-img"
                          />
                          <span v-else class="causer-avatar-letter">
                            {{ lead.causer?.name?.charAt(0) || "U" }}
                          </span>
                        </div>
                        <button class="btn btn-sm btn-light me-1">
                          <i class="ri-eye-line"></i>
                        </button>
                        <button
                          class="btn btn-sm btn-light me-1"
                          @click.stop="handleShowLeadCalls(lead)"
                        >
                          <i class="ri-phone-line"></i>
                        </button>
                        <button
                          class="btn btn-sm btn-success me-1"
                          @click.stop="handleConvertToStudent(lead)"
                          :title="$t('translation.leads.convert_to_student')"
                        >
                          <i class="ri-user-add-line"></i>
                        </button>
                        <button
                          class="btn btn-sm btn-light"
                          @click.stop="handleEditLead(lead)"
                        >
                          <i class="ri-edit-line"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Loading indicator -->
              <div v-if="loadingStates[collection.id]" class="p-3 text-center text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                  <span class="visually-hidden">{{
                    $t("translation.leads.loading_more")
                  }}</span>
                </div>
                {{ $t("translation.leads.loading_more") }}
              </div>

              <!-- Load more button (if there are more items but not loading and not searching or filtering) -->
              <div
                v-else-if="
                  !searchQuery.value.trim() &&
                  !hasActiveFilters &&
                  hasMore[collection.id] &&
                  leadsData[collection.id] &&
                  leadsData[collection.id].length > 0
                "
                class="p-2 text-center"
              >
                <button
                  class="btn btn-outline-primary btn-sm"
                  @click="loadMoreLeads(collection.id)"
                >
                  <i class="ri-add-line me-1"></i>
                  {{ $t("translation.leads.load_more") }} ({{
                    getLeadCount(collection.id) - getLoadedCount(collection.id)
                  }}
                  {{ $t("translation.leads.remaining") }})
                </button>
              </div>

              <div
                v-else-if="
                  !leadsData[collection.id] || leadsData[collection.id].length === 0
                "
                class="p-4 text-center text-muted drop-zone rounded-3"
                @dragover="onDragOver"
                @dragleave="onDragLeave"
                @drop="onDrop($event, collection.id)"
                style="
                  min-height: 120px;
                  display: flex;
                  flex-direction: column;
                  justify-content: center;
                "
              >
                <i class="ri-inbox-line fs-1 mb-2 d-block"></i>
                <p class="mb-1">{{ $t("translation.leads.no_leads") }}</p>
                <small class="text-muted">
                  <i class="ri-drag-drop-line me-1"></i>
                  {{ $t("translation.leads.drag_drop_hint") }}
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tasks view (placeholder) -->
    <div v-else-if="activeTab === 'tasks'" class="text-center py-5">
      <i class="ri-file-list-line fs-1 text-muted d-block mb-3"></i>
      <h4 class="text-muted">{{ $t("translation.leads.tasks") }}</h4>
      <p class="text-muted">{{ $t("translation.leads.tasks_coming_soon") }}</p>
    </div>
  </div>

  <!-- Lead Details Modal -->
  <LeadShow
    v-if="showLeadDetails.visible"
    :lead="showLeadDetails.lead"
    :initial-tab="showLeadDetails.activeTab"
    @close="handleCloseLead"
    @edit="handleEditLead"
    @delete="handleDeleteLead"
    @convert="handleConvertToStudent"
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

  <!-- Edit Lead Modal -->
  <EditLead
    v-if="showEditLead && editingLead"
    :lead="editingLead"
    :collections="collections"
    :courses="courses"
    :teachers="teachers"
    :boards="boards"
    :lead-dynamic-fields="leadDynamicFields"
    @close="handleCloseEditLead"
    @updated="handleLeadUpdated"
  />

  <!-- Delete Confirmation Modal -->
  <DeleteConfirmModal
    ref="deleteModalRef"
    :visible="showDeleteConfirm"
    :lead="deletingLead"
    @close="handleDeleteCancel"
    @confirm="handleDeleteConfirm"
  />

  <!-- Convert to Student Modal -->
  <ConvertToStudent
    v-if="showConvertModal"
    :visible="showConvertModal"
    :lead="convertingLead"
    @close="handleCloseConvertModal"
    @converted="handleConvertSuccess"
  />

  <!-- Lead Collection Settings Modal -->
  <LeadCollectionSettings
    v-if="showSettings"
    :collections="collections"
    :groups="groups"
    :lead-dynamic-fields="leadDynamicFields"
    @close="handleCloseSettings"
    @updated="handleCollectionsUpdated"
  />
</template>

<style scoped>
.kanban-container {
  display: flex;
  flex-direction: column;
  height: 79vh;
  width: 100%;
}

.header-tabs {
  background-color: #fff;
  padding: 13px 0;
}

.search-box .input-group {
  width: 250px;
}

.kanban-board {
  flex: 1;
  overflow-x: auto;
  overflow-y: hidden;
  height: 100%;
  -ms-overflow-style: none !important; /* IE and Edge */
  scrollbar-width: none !important; /* Firefox */
}

.kanban-board::-webkit-scrollbar {
  display: none !important; /* Chrome, Safari and Opera */
}

.kanban-column {
  min-width: 320px;
  width: 320px;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card {
  display: flex;
  flex-direction: column;
  transition: all 0.2s ease;
}

.card-body {
  flex: 1;
  overflow-y: auto;
  max-height: 100%;
  transition: all 0.2s ease;
}

.card-header h5 {
  font-size: 1rem;
}

/* Smooth drag and drop transitions */
.list-group-item {
  transition: all 0.2s ease;
  border-bottom: 1px solid #f0f0f0;
}

.list-group-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Compact card styling */
.list-group-item h6 {
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.list-group-item .small {
  font-size: 0.75rem;
}

.list-group-item .btn-sm {
  font-size: 0.8rem;
}

.causer-avatar {
  width: 24px;
  height: 24px;
  font-size: 0.7rem;
}

.drop-zone {
  transition: all 0.3s ease;
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

/* Remove custom modal styles */
.custom-modal-backdrop,
.custom-modal-wrapper,
.custom-modal-dialog {
  display: none !important;
}

/* Skeleton loading styles */
.skeleton-lead-item {
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.skeleton-drag-handle {
  width: 16px;
  height: 16px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 2px;
}

.skeleton-name {
  width: 70%;
  height: 18px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 4px;
}

.skeleton-phone {
  width: 50%;
  height: 14px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 4px;
}

.skeleton-date {
  width: 40%;
  height: 12px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 4px;
}

@keyframes skeleton-loading {
  0% {
    background-position: -200px 0;
  }
  100% {
    background-position: calc(200px + 100%) 0;
  }
}

/* Filters Section Styles */
.filters-section {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease-out;
}

.filters-section.show {
  max-height: 200px;
  transition: max-height 0.3s ease-in;
}

.filters-section .card {
  border: 1px solid #e3e6f0;
  border-radius: 0.35rem;
  background-color: #f8f9fc;
}

.filters-section .card-body {
  padding: 1rem;
}

.filters-section .form-select {
  font-size: 0.875rem;
  border-color: #d1d3e2;
}

.filters-section .form-select:focus {
  border-color: #bac8f3;
  box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.filters-section .btn-sm {
  font-size: 0.875rem;
  padding: 0.375rem 0.75rem;
}

.btn.active {
  background-color: #4e73df;
  border-color: #4e73df;
  color: #fff;
}

/* Small avatar circle for causer */
.avatar-circle-sm {
  width: 20px;
  height: 20px;
  font-size: 0.7rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #e3e6f0;
  font-weight: bold;
  color: #4f5d73;
  flex-shrink: 0;
}

/* Causer avatar styling */
.causer-avatar {
  width: 32px;
  height: 32px;
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
  font-size: 0.875rem;
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

  .search-box {
    margin-bottom: 0.5rem;
    margin-right: 0 !important;
    width: 100%;
  }

  .search-box .input-group {
    width: 100%;
  }

  .tab-actions .btn-primary {
    width: 100%;
    font-size: 0.9rem;
  }

  .filters-section.show {
    max-height: 300px;
  }

  .filters-section .form-select {
    font-size: 0.8rem;
  }

  .filters-section .btn-sm {
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
  }

  .kanban-board {
    overflow-x: auto;
    padding-bottom: 1rem;
  }

  .kanban-column {
    min-width: 280px;
    width: 280px;
  }

  .card-header h5 {
    font-size: 0.9rem;
  }

  .list-group-item {
    padding: 0.5rem !important;
  }

  .list-group-item h6 {
    font-size: 0.85rem;
  }

  .btn-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.4rem;
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

  .kanban-column {
    min-width: 260px;
    width: 260px;
  }

  .tab-actions .btn-primary span {
    font-size: 0.8rem;
  }
}
</style>
