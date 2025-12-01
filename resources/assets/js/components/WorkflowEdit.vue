<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Edit Workflow</h1>
      <a href="/workflow" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
    <div class="card">
      <div class="card-body">
        <div class="form-group">
          <label>Name</label>
          <input class="form-control" v-model="name" />
        </div>
        <div class="form-group">
          <label>Description</label>
          <input class="form-control" v-model="description" />
        </div>
        <button class="action-button title-button form-button select-action-submit" @click="save">
          <span>Save</span>
        </button>
      </div>
    </div>

    <div class="mt-4">
      <WorkflowSteps :workflow-id="id" :steps="steps" />
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import WorkflowSteps from './WorkflowSteps.vue'

export default {
  components: { WorkflowSteps },
  props: {
    id: { type: [String, Number], required: true },
    initialName: { type: String, default: '' },
    initialDescription: { type: String, default: '' }
  },
  data () {
    return {
      name: this.initialName,
      description: this.initialDescription,
      steps: []
    }
  },
  async mounted () {
    const { data } = await axios.get(`/api/workflow/${this.id}`)
    this.steps = (data.steps || []).sort((a,b)=>a.order-b.order)
  },
  methods: {
    async save () {
      await axios.put(`/api/workflow/${this.id}`, { name: this.name, description: this.description })
      window.location.href = '/workflow'
    }
  }
}
</script>

