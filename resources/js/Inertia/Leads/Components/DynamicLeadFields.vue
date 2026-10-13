<script setup>
import SearchableSelect from "./SearchableSelect.vue";

const props = defineProps({
  fields: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: Object,
    default: () => ({}),
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const setVal = (key, value) => {
  emit("update:modelValue", { ...props.modelValue, [key]: value });
};

const fieldError = (key) => {
  const dot = props.errors[`custom_attributes.${key}`];
  if (Array.isArray(dot)) {
    return dot[0];
  }
  if (typeof dot === "string") {
    return dot;
  }
  return props.errors[key];
};
</script>

<template>
  <div v-if="fields && fields.length" class="dynamic-lead-fields col-12">
    <h6 class="text-muted mb-3">
      {{ $t("translation.leads.custom_attributes") }}
    </h6>
    <div class="row g-3">
      <div v-for="field in fields" :key="field.key" class="col-md-6">
        <label class="form-label">
          {{ field.label }}
          <span v-if="field.required" class="text-danger">*</span>
        </label>
        <input
          v-if="field.type === 'text'"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': fieldError(field.key) }"
          :value="modelValue[field.key] ?? ''"
          @input="setVal(field.key, $event.target.value)"
          :disabled="disabled"
        />
        <textarea
          v-else-if="field.type === 'textarea'"
          class="form-control"
          rows="3"
          :class="{ 'is-invalid': fieldError(field.key) }"
          :value="modelValue[field.key] ?? ''"
          @input="setVal(field.key, $event.target.value)"
          :disabled="disabled"
        />
        <input
          v-else-if="field.type === 'number'"
          type="number"
          step="any"
          class="form-control"
          :class="{ 'is-invalid': fieldError(field.key) }"
          :value="modelValue[field.key] ?? ''"
          @input="setVal(field.key, $event.target.value)"
          :disabled="disabled"
        />
        <SearchableSelect
          v-else-if="field.type === 'select'"
          :model-value="modelValue[field.key] ?? ''"
          :options="
            (field.options || []).map((opt) => ({ value: opt, label: String(opt) }))
          "
          :placeholder="$t('translation.leads.select_dynamic_value')"
          :allow-clear="!field.required"
          :disabled="disabled"
          :invalid="!!fieldError(field.key)"
          @update:model-value="setVal(field.key, $event)"
        />
        <div v-if="fieldError(field.key)" class="invalid-feedback d-block">
          {{ fieldError(field.key) }}
        </div>
      </div>
    </div>
  </div>
</template>
