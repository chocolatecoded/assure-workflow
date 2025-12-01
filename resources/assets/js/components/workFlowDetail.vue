<template>
  <div>
    <div class="large-12">
      <div class='row' style="padding-left: 10px;">

        <div class="medium-12 columns no-padding">
          <h6 class="capitalize breadcrumb">
            <a href="/dashboard" class="link">HOME</a>
            &gt;
            <a href="/workflow" class="link">WORKFLOW</a>
            &gt; {{ workflow.name }} 
          </h6>
        </div>

        <div class="medium-6 columns no-padding">
          <h2 class="capitalize">Workflow Steps</h2>
        </div>

        <div class="medium-6 columns no-padding" style="text-align: right; margin-bottom: 10px;">
          <button class="action-button" @click="addStep">Add Step</button>
        </div>

      </div>

      <div class="row">
        <div class="large-12 columns" style="padding-left: 25px;">
          <table style="width: 100%" class="table responsive stripe woTable">
            <thead>
              <tr>
                <th scope="col">Name</th>
                <th scope="col">Type</th>
                <th scope="col">Module</th>
                <th scope="col">Conditions</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>

              <template v-if="!workflow.steps.length">
                <tr>
                  <td></td>
                  <td></td>
                  <td style="text-align: center; width: 100%;">No Steps Created Yet</td>
                  <td></td>
                  <td></td>
                </tr>
              </template>

              <template v-else>
                <tr
                  v-for="(item, index) in workflow.steps"
                  :key="item.id"
                  draggable="true"
                  @dragstart="dragStart($event, index)"
                  @dragover="dragOver($event, index)"
                  @dragend="dragEnd"
                  :class="{ dragging: draggedIndex === index }"
                >
                  <td class="align-middle">
                    <span class="drag-handle mr-2" style="cursor: move;">☰</span>
                    {{ item.name }}
                  </td>
                  <td class="align-middle">{{ item.type || '-' }}</td>
                  <td class="align-middle">{{ item.module || '-' }}</td>
                  <td class="align-middle">{{ (item.conditions && item.conditions.length) || 0 }}</td>
                  <td class="text-center align-middle">
                    <button class="action-button" @click="editStep(item)">Edit</button>
                    <button class="action-button" @click="deleteNotification(item)">Delete</button>
                  </td>
                </tr>
              </template>

            </tbody>
          </table>

          <button v-if="orderChange" @click="updateSteps" class="action-button">Save Order Changes</button>

        </div>
      </div>

      <!-- Step modal -->
      <step-detail v-if="creatingStep"
                   :step="editingStep"
                   :workflow="workflow"
                   :errors="stepErrors"
                   @closed="creatingStep = false; editingStep = null"
                   @conditions-change="onConditionsChange"
                   @update="addOrUpdateStep"
                   @refresh="refresh" />
    </div>
  </div>
</template>

<script>
import StepDetail from './steps/stepDetail.vue'
import Swal from 'sweetalert2'

export default {
  name: 'workflow-detail',
  components: { StepDetail },
  props: ['id', 'forms', 'work', 'account'],
  data: () => ({
    workflow: { steps: [] },
    creatingStep: false,
    orderChange: false,
    changedOrderIds: [],
    editingStep: null,
    stepErrors: {},
    draggedIndex: null
  }),
  mounted () {
    this.getWorkflow()
  },
  methods: {
    async getWorkflow () {
      const { data } = await this.$api.get(`/api/workflow/${this.id}`)
      this.workflow = data
    },
    refresh () {
      this.getWorkflow()
    },
    addStep () {
      this.editingStep = null
      this.stepErrors = {}
      this.creatingStep = true
    },
    editStep (item) {
      this.editingStep = Object.assign({}, item)
      this.stepErrors = {}
      this.creatingStep = true
    },
    addOrUpdateStep (payload) {
      if (payload && payload.id) {
        this.$api.put(`/api/workflow/${this.workflow.id}/steps/${payload.id}`, payload)
          .then(({ data }) => {
            const idx = this.workflow.steps.findIndex(s => s.id === data.id)
            if (idx !== -1) {
              this.$set(this.workflow.steps, idx, data)
            } else {
              this.workflow.steps.push(data)
            }
            this.creatingStep = false
            this.editingStep = null
            this.stepErrors = {}
          })
          .catch((e) => {
            if (e.response.status === 422) {
              this.stepErrors = this.normalizeErrors(e.response.data.errors || {})
            }
          })
      } else {
        this.$api.post(`/api/workflow/${this.workflow.id}/steps`, payload)
          .then(({ data }) => {
            this.workflow.steps.push(data)
            this.recomputeAndPersistOrder()
            this.creatingStep = false
            this.stepErrors = {}
          })
          .catch((e) => {
            if (e.response.status === 422) {
              this.stepErrors = this.normalizeErrors(e.response.data.errors || {})
            }
          })
      }
    },
    onConditionsChange ({ stepId, conditions }) {
      const idx = this.workflow.steps.findIndex(s => s.id === stepId)
      if (idx !== -1) {
        const step = Object.assign({}, this.workflow.steps[idx])
        step.conditions = Array.isArray(conditions) ? conditions : []
        this.$set(this.workflow.steps, idx, step)
      }
    },
    normalizeErrors (errors) {
      const out = {}
      Object.keys(errors || {}).forEach(k => {
        const key = k.indexOf('data.') === 0 ? k.split('.').slice(1).join('.') : k
        out[key] = errors[k]
      })
      return out
    },
    deleteNotification (item) {
      Swal.fire({
        text: `Are you sure you want to delete step ${item.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
      }).then((result) => {
        if (result.value) {
          this.$api.delete(`/api/workflow/${this.workflow.id}/steps/${item.id}`).then(() => {
            this.workflow.steps = this.workflow.steps.filter(s => s.id !== item.id)
            this.recomputeAndPersistOrder()
            Swal.fire({
              text: `Successfully deleted ${item.name}`,
              icon: 'success',
              confirmButtonColor: '#d33',
              confirmButtonText: 'Yes'
            });
          })
        }
      })
    },
    // --- HTML5 drag-and-drop methods ---
    dragStart(event, index) {
      this.draggedIndex = index
      event.dataTransfer.effectAllowed = 'move'
    },
    dragOver(event, index) {
      event.preventDefault()
      if (index === this.draggedIndex) return
      const draggedItem = this.workflow.steps[this.draggedIndex]
      this.workflow.steps.splice(this.draggedIndex, 1)
      this.workflow.steps.splice(index, 0, draggedItem)
      this.draggedIndex = index
      this.orderChange = true
      this.changedOrderIds = this.workflow.steps.map(s => s.id)
    },
    dragEnd() {
      this.draggedIndex = null
    },
    async updateSteps () {
      await this.$api.post(`/api/workflow/${this.workflow.id}/steps/reorder`, { order: this.changedOrderIds })
      this.orderChange = false
      this.changedOrderIds = []
      this.refresh()
    },
    recomputeAndPersistOrder() {
      this.workflow.steps.forEach((s, i) => { this.$set(s, 'order', i) })
      const ids = this.workflow.steps.map(s => s.id)
      this.$api.post(`/api/workflow/${this.workflow.id}/steps/reorder`, { order: ids })
    }
  }
}
</script>

<style scoped>
.dragging {
  opacity: 0.5;
}

.woTable {
  border-collapse: collapse;
}
.woTable th,
.woTable td {
  border: 1px solid #FFFFFF !important;
}

.breadcrumb {
  background: white;
  color: #368dba;
  padding: 10px;
  font-size: 12px;
  text-transform: uppercase;
  margin-bottom: 10px;
} 

a {
  color: #368dba;
  font-size: 12px;
  padding: 0 2px;
}
</style>
