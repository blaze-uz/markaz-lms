<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $t('translation.leads.lead') }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 row">
            <div class="col-md-7 col-sm-12">
              <div class="mb-3 form-group input-group-lg">
                <input 
                  type="text" 
                  class="p-0 border-0 form-control" 
                  :value="lead.name"
                  readonly
                >
              </div>
              <div>
                <small class="text-muted">{{ $t("translation.leads.phone") }}</small>
                <p>{{ lead.phone }}</p>
              </div>
              <div v-if="lead.course">
                <small class="text-muted">{{ $t("translation.courses.course") }}</small>
                <p>{{ lead.course.name }}</p>
              </div>
              <div v-if="lead.board">
                <small class="text-muted">{{ $t("translation.leads.board") }}</small>
                <p>{{ lead.board.name }}</p>
              </div>
              <div v-if="lead.chosen_days">
                <small class="text-muted">{{ $t("translation.leads.chosen_days") }}</small>
                <p>{{ $t('translation.leads.' + lead.chosen_days) }}</p>
              </div>
              <div v-if="lead.chosen_time">
                <small class="text-muted">{{ $t("translation.leads.chosen_time") }}</small>
                <p>{{ lead.chosen_time }}</p>
              </div>
              <div v-if="lead.teachers && lead.teachers.length">
                <small class="text-muted">{{ $t("translation.leads.teacher") }}</small>
                <p>
                  <span v-for="teacher in lead.teachers" :key="teacher.id" class="me-2">
                    {{ teacher.name }}
                  </span>
                </p>
              </div>
              <div v-if="lead.comment">
                <small class="text-muted">{{ $t("translation.leads.comment") }}</small>
                <p>{{ lead.comment }}</p>
              </div>
            </div>
            <div class="col-md-5 col-sm-12">
              <div class="d-flex gap-2 mb-3">
                <button class="btn btn-primary" @click="handleEdit">
                  <i class="fa-solid fa-edit"></i> {{ $t("translation.btn.edit") }}
                </button>
                <button class="btn btn-danger" @click="handleDelete">
                  <i class="fa-solid fa-trash"></i> {{ $t("translation.btn.delete") }}
                </button>
              </div>
              
              <!-- Lead Stats -->
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">{{ $t("translation.leads.statistics") }}</h6>
                </div>
                <div class="card-body">
                  <div class="mb-2">
                    <small class="text-muted">{{ $t("translation.leads.created_at") }}</small>
                    <p class="mb-1">{{ formatDate(lead.created_at, 'd.m.Y H:i') }}</p>
                  </div>
                  <div v-if="lead.causer">
                    <small class="text-muted">{{ $t("translation.leads.created_by") }}</small>
                    <p class="mb-1">{{ lead.causer.name }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            {{ $t("translation.btn.close") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  lead: Object,
  show: Boolean
})

const emit = defineEmits(['close', 'updated'])

const handleEdit = () => {
  router.visit(route('admin.leads.edit', props.lead.id))
}

const handleDelete = async () => {
  if (confirm('Are you sure you want to delete this lead?')) {
    try {
      await axios.delete(route('admin.leads.destroy', props.lead.id))
      emit('updated')
      emit('close')
    } catch (error) {
      console.error('Error deleting lead:', error)
    }
  }
}

const formatDate = (date, format = 'd.m.Y') => {
  const d = new Date(date)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  const hours = String(d.getHours()).padStart(2, '0')
  const minutes = String(d.getMinutes()).padStart(2, '0')
  
  if (format === 'd.m.Y H:i') {
    return `${day}.${month}.${year} ${hours}:${minutes}`
  }
  return `${day}.${month}.${year}`
}
</script> 