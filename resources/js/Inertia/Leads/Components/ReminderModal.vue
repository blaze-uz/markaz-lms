<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $t('translation.leads.add_reminder') }} - {{ lead.name }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label class="form-label">{{ $t('translation.leads.phone') }}</label>
              <p class="form-control-plaintext">{{ lead.phone }}</p>
            </div>
            
            <div class="mb-3">
              <label class="form-label">{{ $t('translation.leads.reminder_date') }} <span class="text-danger">*</span></label>
              <input 
                v-model="form.reminder_date" 
                type="date" 
                class="form-control" 
                required
                :min="today"
              >
            </div>
            
            <div class="mb-3">
              <label class="form-label">{{ $t('translation.leads.reminder_time') }} <span class="text-danger">*</span></label>
              <input 
                v-model="form.reminder_time" 
                type="time" 
                class="form-control" 
                required
              >
            </div>
            
            <div class="mb-3">
              <label class="form-label">{{ $t('translation.leads.reminder_note') }}</label>
              <textarea 
                v-model="form.note" 
                class="form-control" 
                rows="4" 
                :placeholder="$t('translation.leads.reminder_note_placeholder')"
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
import { ref, reactive, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  lead: Object,
  show: Boolean
})

const emit = defineEmits(['close', 'updated'])

const loading = ref(false)
const form = reactive({
  reminder_date: '',
  reminder_time: '',
  note: ''
})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

const handleSubmit = async () => {
  if (!form.reminder_date || !form.reminder_time) {
    alert('Please fill in the required fields')
    return
  }
  
  loading.value = true
  try {
    await axios.post(route('admin.leads.add-reminder', props.lead.id), {
      reminder_date: form.reminder_date,
      reminder_time: form.reminder_time,
      note: form.note
    })
    
    emit('updated')
    emit('close')
    
    // Reset form
    form.reminder_date = ''
    form.reminder_time = ''
    form.note = ''
  } catch (error) {
    console.error('Error saving reminder:', error)
    alert('Error saving reminder')
  } finally {
    loading.value = false
  }
}
</script> 