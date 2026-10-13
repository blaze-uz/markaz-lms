import { reactive, ref } from "vue";

export const LEADS_TABLE_COLUMNS_STORAGE_KEY = "markaz_lms_leads_table_columns_v1";

export const LEADS_TABLE_CORE_COLUMN_KEYS = [
  "id",
  "name",
  "phone",
  "address",
  "chosen_time",
  "teacher",
  "board",
  "course",
  "attend",
  "date",
];

/**
 * @param {string} fieldKey
 * @returns {string}
 */
export function leadsTableCustomColumnKey(fieldKey) {
  return `custom:${fieldKey}`;
}

/**
 * @param {string[]} dynamicFieldKeys
 * @returns {Record<string, boolean>}
 */
function buildDefaultVisibility(dynamicFieldKeys) {
  /** @type {Record<string, boolean>} */
  const vis = {};
  for (const k of LEADS_TABLE_CORE_COLUMN_KEYS) {
    vis[k] = true;
  }
  for (const key of dynamicFieldKeys) {
    vis[leadsTableCustomColumnKey(key)] = false;
  }
  return vis;
}

/**
 * @param {string[]} dynamicFieldKeys
 * @returns {string[]}
 */
export function buildDefaultColumnOrder(dynamicFieldKeys) {
  return [...LEADS_TABLE_CORE_COLUMN_KEYS, ...dynamicFieldKeys.map(leadsTableCustomColumnKey)];
}

/**
 * @param {Record<string, boolean>} defaults
 * @param {Record<string, unknown>|null|undefined} saved
 * @returns {Record<string, boolean>}
 */
function mergeVisibilityWithSaved(defaults, saved) {
  const out = { ...defaults };
  if (!saved || typeof saved !== "object") {
    return out;
  }
  for (const key of Object.keys(out)) {
    if (Object.prototype.hasOwnProperty.call(saved, key)) {
      out[key] = Boolean(saved[key]);
    }
  }
  return out;
}

/**
 * @param {string[]|null|undefined} savedOrder
 * @param {string[]} defaultOrder
 * @returns {string[]}
 */
function mergeOrderWithDefaults(savedOrder, defaultOrder) {
  if (!Array.isArray(savedOrder) || savedOrder.length === 0) {
    return [...defaultOrder];
  }
  const defaultSet = new Set(defaultOrder);
  const result = [];
  const seen = new Set();
  for (const k of savedOrder) {
    if (defaultSet.has(k)) {
      result.push(k);
      seen.add(k);
    }
  }
  for (const k of defaultOrder) {
    if (!seen.has(k)) {
      result.push(k);
    }
  }
  return result;
}

/**
 * @returns {{ visibility: Record<string, unknown>|null, order: string[]|null, legacyFlat: boolean }}
 */
function readNormalizedStored() {
  /** @type {{ visibility: Record<string, unknown>|null, order: string[]|null, legacyFlat: boolean }} */
  const empty = { visibility: null, order: null, legacyFlat: false };
  try {
    const raw = localStorage.getItem(LEADS_TABLE_COLUMNS_STORAGE_KEY);
    if (!raw) {
      return empty;
    }
    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== "object") {
      return empty;
    }
    if (parsed.visibility && typeof parsed.visibility === "object") {
      return {
        visibility: parsed.visibility,
        order: Array.isArray(parsed.order) ? parsed.order : null,
        legacyFlat: false,
      };
    }
    return {
      visibility: parsed,
      order: null,
      legacyFlat: true,
    };
  } catch {
    return empty;
  }
}

const visibilityState = reactive(/** @type {Record<string, boolean>} */ ({}));

/** @type {import('vue').Ref<string[]>} */
export const leadsTableColumnOrder = ref([]);

/**
 * @param {Array<{ key: string }>} dynamicFields
 */
function dynamicKeysFromFields(dynamicFields) {
  return (dynamicFields || []).map((f) => f.key).filter(Boolean);
}

function snapshotVisibility() {
  return { ...visibilityState };
}

export function persistLeadsTableColumnsState() {
  const payload = {
    visibility: snapshotVisibility(),
    order: [...leadsTableColumnOrder.value],
  };
  localStorage.setItem(LEADS_TABLE_COLUMNS_STORAGE_KEY, JSON.stringify(payload));
}

/**
 * @param {Array<{ key: string }>} dynamicFields
 */
export function hydrateLeadsTableColumnVisibility(dynamicFields) {
  const dynKeys = dynamicKeysFromFields(dynamicFields);
  const defaultsVis = buildDefaultVisibility(dynKeys);
  const defaultsOrder = buildDefaultColumnOrder(dynKeys);

  const { visibility: savedVis, order: savedOrder, legacyFlat } = readNormalizedStored();
  const mergedVis = mergeVisibilityWithSaved(defaultsVis, savedVis);
  const mergedOrder = mergeOrderWithDefaults(savedOrder, defaultsOrder);

  Object.keys(visibilityState).forEach((k) => {
    delete visibilityState[k];
  });
  Object.assign(visibilityState, mergedVis);
  leadsTableColumnOrder.value = mergedOrder;

  if (legacyFlat) {
    persistLeadsTableColumnsState();
  }
}

/**
 * @param {string} columnKey
 * @param {boolean} value
 */
export function setLeadsTableColumnVisible(columnKey, value) {
  visibilityState[columnKey] = value;
  persistLeadsTableColumnsState();
}

/**
 * @param {string[]} nextOrder
 * @param {Array<{ key: string }>} dynamicFields
 */
export function setLeadsTableColumnOrder(nextOrder, dynamicFields) {
  const defaultsOrder = buildDefaultColumnOrder(dynamicKeysFromFields(dynamicFields));
  leadsTableColumnOrder.value = mergeOrderWithDefaults(nextOrder, defaultsOrder);
  persistLeadsTableColumnsState();
}

/**
 * @param {Array<{ key: string }>} dynamicFields
 */
export function resetLeadsTableColumnVisibilityToDefaults(dynamicFields) {
  const keys = dynamicKeysFromFields(dynamicFields);
  const defaults = buildDefaultVisibility(keys);
  Object.keys(visibilityState).forEach((k) => {
    delete visibilityState[k];
  });
  Object.assign(visibilityState, defaults);
  leadsTableColumnOrder.value = buildDefaultColumnOrder(keys);
  persistLeadsTableColumnsState();
}

/**
 * @param {string} columnKey
 */
export function isLeadsTableColumnVisible(columnKey) {
  return visibilityState[columnKey] !== false;
}

/**
 * @returns {{
 *   visibilityState: Record<string, boolean>,
 *   leadsTableColumnOrder: import('vue').Ref<string[]>,
 *   hydrateLeadsTableColumnVisibility: typeof hydrateLeadsTableColumnVisibility,
 *   setLeadsTableColumnVisible: typeof setLeadsTableColumnVisible,
 *   setLeadsTableColumnOrder: typeof setLeadsTableColumnOrder,
 *   resetLeadsTableColumnVisibilityToDefaults: typeof resetLeadsTableColumnVisibilityToDefaults,
 *   isLeadsTableColumnVisible: typeof isLeadsTableColumnVisible,
 *   leadsTableCustomColumnKey: typeof leadsTableCustomColumnKey,
 *   LEADS_TABLE_CORE_COLUMN_KEYS: typeof LEADS_TABLE_CORE_COLUMN_KEYS,
 * }}
 */
export function useLeadsTableColumnVisibility() {
  return {
    visibilityState,
    leadsTableColumnOrder,
    hydrateLeadsTableColumnVisibility,
    setLeadsTableColumnVisible,
    setLeadsTableColumnOrder,
    resetLeadsTableColumnVisibilityToDefaults,
    isLeadsTableColumnVisible,
    leadsTableCustomColumnKey,
    LEADS_TABLE_CORE_COLUMN_KEYS,
  };
}
