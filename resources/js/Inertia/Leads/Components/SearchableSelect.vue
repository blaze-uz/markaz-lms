<template>
  <div class="searchable-select" ref="selectRef" :class="{ 'is-disabled': disabled }">
    <div
      class="form-control d-flex align-items-center justify-content-between"
      @click="toggleDropdown"
      :class="{ 'is-open': isOpen, 'is-invalid': invalid, 'is-disabled-control': disabled }"
    >
      <span v-if="selectedOption" class="selected-text">{{ selectedOption.label }}</span>
      <span v-else class="placeholder-text text-muted">{{ placeholder }}</span>
      <div class="d-flex align-items-center">
        <button 
          v-if="allowClear && selectedOption" 
          type="button"
          class="btn-clear"
          @click.stop="clearSelection"
        >
          <i class="fas fa-times"></i>
        </button>
        <i class="fas fa-chevron-down dropdown-arrow" :class="{ 'rotated': isOpen }"></i>
      </div>
    </div>
    
    <div v-if="isOpen" class="dropdown-menu show">
      <div class="search-input-container">
        <input 
          type="text" 
          class="form-control form-control-sm"
          v-model="searchQuery"
          :placeholder="$t('translation.search')"
          ref="searchInput"
          @keydown.escape="closeDropdown"
          @keydown.enter.prevent="selectFirstOption"
          @keydown.arrow-down.prevent="navigateDown"
          @keydown.arrow-up.prevent="navigateUp"
        />
      </div>
      <div class="options-container">
        <div 
          v-for="(option, index) in filteredOptions" 
          :key="option.value"
          class="dropdown-item"
          :class="{ 'active': index === highlightedIndex }"
          @click="selectOption(option)"
          @mouseenter="highlightedIndex = index"
        >
          {{ option.label }}
        </div>
        <div v-if="filteredOptions.length === 0" class="dropdown-item disabled">
          {{ $t('translation.no_data') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  options: {
    type: Array,
    required: true,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Select option'
  },
  allowClear: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  invalid: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const selectRef = ref(null);
const searchInput = ref(null);
const isOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(-1);

const selectedOption = computed(() => {
  if (!props.modelValue) return null;
  return props.options.find(option => option.value == props.modelValue);
});

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;
  
  return props.options.filter(option => 
    option.label.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const toggleDropdown = () => {
  if (props.disabled) {
    return;
  }
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    nextTick(() => {
      if (searchInput.value) {
        searchInput.value.focus();
      }
    });
  }
};

const closeDropdown = () => {
  isOpen.value = false;
  searchQuery.value = '';
  highlightedIndex.value = -1;
};

const selectOption = (option) => {
  emit('update:modelValue', option.value);
  closeDropdown();
};

const clearSelection = () => {
  emit('update:modelValue', '');
  closeDropdown();
};

const selectFirstOption = () => {
  if (filteredOptions.value.length > 0) {
    selectOption(filteredOptions.value[0]);
  }
};

const navigateDown = () => {
  if (highlightedIndex.value < filteredOptions.value.length - 1) {
    highlightedIndex.value++;
  }
};

const navigateUp = () => {
  if (highlightedIndex.value > 0) {
    highlightedIndex.value--;
  }
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (selectRef.value && !selectRef.value.contains(event.target)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Reset search when dropdown closes
watch(isOpen, (newValue) => {
  if (!newValue) {
    searchQuery.value = '';
    highlightedIndex.value = -1;
  }
});

watch(
  () => props.disabled,
  (isDisabled) => {
    if (isDisabled) {
      closeDropdown();
    }
  },
);
</script>

<style scoped>
.searchable-select {
  position: relative;
  width: 100%;
}

.searchable-select.is-disabled .form-control.is-disabled-control {
  cursor: not-allowed;
  opacity: 0.65;
  background-color: #e9ecef;
}

.form-control {
  cursor: pointer;
  height: 38px;
  min-height: 38px;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  line-height: 1.5;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  background-color: #fff;
}

.form-control:focus {
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-control.is-open {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.placeholder-text {
  font-size: 0.875rem;
  color: #6c757d;
}

.selected-text {
  font-size: 0.875rem;
  color: #212529;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
}

.btn-clear {
  background: none;
  border: none;
  padding: 2px 4px;
  margin-right: 4px;
  color: #6c757d;
  font-size: 0.75rem;
  border-radius: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
}

.btn-clear:hover {
  color: #dc3545;
  background-color: #f8f9fa;
}

.dropdown-arrow {
  font-size: 0.75rem;
  color: #6c757d;
  transition: transform 0.2s;
  margin-left: 4px;
}

.dropdown-arrow.rotated {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1050;
  border: 1px solid #ced4da;
  border-top: none;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
  border-bottom-left-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
  background-color: #fff;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  max-height: 280px;
  overflow: hidden;
  min-width: 100%;
  width: auto;
}

.search-input-container {
  padding: 8px;
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.search-input-container .form-control {
  height: 32px;
  min-height: 32px;
  font-size: 0.8125rem;
  padding: 0.25rem 0.5rem;
}

.options-container {
  max-height: 200px;
  overflow-y: auto;
}

.dropdown-item {
  cursor: pointer;
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  color: #212529;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-item:hover,
.dropdown-item.active {
  background-color: #e9ecef;
  color: #212529;
}

.dropdown-item.disabled {
  cursor: not-allowed;
  opacity: 0.6;
  background-color: transparent;
}

/* Scrollbar styling */
.options-container::-webkit-scrollbar {
  width: 6px;
}

.options-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.options-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.options-container::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style> 