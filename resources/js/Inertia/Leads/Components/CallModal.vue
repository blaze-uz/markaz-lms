<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $t('translation.leads.call') }} - {{ lead.name }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label class="form-label">{{ $t('translation.leads.phone') }}</label>
              <p class="form-control-plaintext">{{ lead.phone }}</p>
            </div>
            
            <div class="mb-3">
              <label class="form-label">{{ $t('translation.leads.call_status') }} <span class="text-danger">*</span></label>
              <select v-model="form.status" class="form-select" required>
                <option value="">{{ $t('translation.leads.select_status') }}</option>
                <option value="answered">{{ $t('translation.leads.answered') }}</option>
                <option value="not_answered">{{ $t('translation.leads.not_answered') }}</option>
                <option value="busy">{{ $t('translation.leads.busy') }}</option>
                <option value="declined">{{ $t('translation.leads.declined') }}</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label class="form-label">{{ $t('translation.leads.comment') }}</label>
              <textarea 
                v-model="form.comment" 
                class="form-control" 
                rows="4" 
                :placeholder="$t('translation.leads.call_comment_placeholder')"
              ></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            {{ $t("translation.btn.cancel") }}
          </button>
          <button type="button" class="btn btn-primary" @click="handleSubmit" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ $t("translation.btn.save") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const props = defineProps({
  lead: Object,
  show: Boolean
})

const emit = defineEmits(['close', 'updated'])

const loading = ref(false)
const form = reactive({
  status: '',
  comment: ''
})

const handleSubmit = async () => {
  if (!form.status) {
    alert('Please select a call status')
    return
  }
  
  loading.value = true
  try {
    await axios.post(route('admin.leads.store-call', props.lead.id), {
      status: form.status,
      comment: form.comment
    })
    
    emit('updated')
    emit('close')
    
    // Reset form
    form.status = ''
    form.comment = ''
  } catch (error) {
    console.error('Error saving call:', error)
    alert('Error saving call')
  } finally {
    loading.value = false
  }
}
</script> 