<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h2 class="h5 mb-0">Steps</h2>
      <a class="action-button title-button other-button" @click="openCreateStep">Add Step</a>
    </div>
    <ul class="list-group mb-3">
      <li v-for="s in localSteps" :key="s.id" class="list-group-item">
        <div class="flex-grow-1 pr-2 mb-2 d-flex align-items-center justify-content-between">
          <input class="form-control form-control-sm mb-1" v-model="s.name" placeholder="Step name" />
          <div class="form-row">
            <div class="col"><input class="form-control form-control-sm" v-model="s.module" placeholder="Module" /></div>
            <div class="col"><input class="form-control form-control-sm" v-model="s.type" placeholder="Type" /></div>
            <div class="col"><input class="form-control form-control-sm" v-model="s.condition_citeria" placeholder="Condition criteria" /></div>
          </div>
          <div class="btn-group">
            <button class="btn btn-sm btn-primary" @click="saveStep(s)">Save</button>
            <button class="btn btn-sm btn-danger" @click="deleteStep(s)">Delete</button>
          </div>
        </div>
        <WorkflowConditions :workflow-id="workflowId" :step-id="s.id" :conditions="s.conditions || []" />
      </li>
    </ul>
    <div class="text-right">
      <button class="btn btn-outline-secondary btn-sm" @click="reorder">Save Order</button>
    </div>

    <!-- Create Step Modal (Foundation style) -->
    <div id="create-step" class="reveal-modal small" data-options="multiple_opened:true;" data-reveal role="dialog" aria-hidden="true">
      <div class="medium-12 columns no-padding">
        <form @submit.prevent="createStep">
          <a class="close-reveal-modal" aria-label="Close">×</a>
          <h1>New Step</h1>
          <div class="form-group"><input v-model="draft.name" required class="form-control text" placeholder="Name" /></div>
          <div class="form-group"><input v-model="draft.module" class="form-control text" placeholder="Module" /></div>
          <div class="form-group"><input v-model="draft.type" class="form-control text" placeholder="Type" /></div>
          <div class="form-group"><input v-model="draft.condition_citeria" class="form-control text" placeholder="Condition criteria" /></div>
          <div class="form-group"><button type="submit" class="action-button title-button form-button select-action-submit"><span>Save</span></button></div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import WorkflowConditions from './WorkflowConditions.vue'

export default {
  components: { WorkflowConditions },
  props: {
    workflowId: { type: [String, Number], required: true },
    steps: { type: Array, default: () => [] }
  },
  data () {
    return {
      localSteps: JSON.parse(JSON.stringify(this.steps || [])).sort((a,b)=>a.order-b.order),
      draft: { name: '', module: '', type: '', condition_citeria: '' }
    }
  },
  methods: {
    openCreateStep () {
      if (typeof $ !== 'undefined') { $('#create-step').foundation('reveal', 'open') }
    },
    async createStep () {
      const { data } = await axios.post(`/api/workflow/${this.workflowId}/steps`, this.draft)
      this.localSteps.push(data)
      if (typeof $ !== 'undefined') { $('#create-step').foundation('reveal', 'close') }
      this.draft = { name: '', module: '', type: '', condition_citeria: '' }
    },
    async saveStep (s) {
      await axios.put(`/api/workflow/${this.workflowId}/steps/${s.id}`, s)
    },
    async deleteStep (s) {
      await axios.delete(`/api/workflow/${this.workflowId}/steps/${s.id}`)
      this.localSteps = this.localSteps.filter(x => x.id !== s.id)
    },
    async reorder () {
      const ids = this.localSteps.map(s => s.id)
      await axios.post(`/api/workflow/${this.workflowId}/steps/reorder`, { order: ids })
    }
  }
}
</script>

<style scoped>
.list-group-item + .list-group-item { margin-top: .25rem; }
</style>

