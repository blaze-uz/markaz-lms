<script setup>
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  teachers: Array,
  boards: Array,
  courses: Array,
  meta: Object,
});

const form = useForm({
  name: "",
  phone: "",
  chosen_days: "",
  chosen_time: "",
  board_id: null,
  course_id: null,
  comment: "",
  teachers: [],
});
</script>

<template>
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="mb-0 card-title">Create New Lead</h4>
          <Link href="/leads/inertia" class="btn btn-secondary btn-sm">
            <i class="ri-arrow-left-line align-bottom me-1"></i> Back to Leads
          </Link>
        </div>
      </div>
      <div class="card-body">
        <div class="alert alert-info text-center">
          <p class="mb-0">Lead creation form will be implemented here.</p>
        </div>
      </div>
    </div>
  </div>
</template>
