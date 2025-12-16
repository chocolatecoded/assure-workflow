<template>
  <div class="wf-modal-backdrop">
    <div class="wf-modal-card width-60">
      <div class="d-flex justify-content-between align-items-center mb-2 modal-header">
        <h5 class="mb-0 modal-title">{{ modalTitle }}</h5>
        <button type="button" class="close" @click="$emit('closed')">×</button>
      </div>
      <form @submit.prevent="submit" style="padding: 0 20px 20px 20px;">
        <div class="form-group">
          <label class="new-workflow-label">Workflow:</label>
          <input v-model="form.name" required placeholder="Enter Workflow Name" class="form-control" />
        </div>
        <div class="text-right">
          <button type="button" class="action-button" @click="$emit('closed')">Cancel</button>
          <button type="submit" class="action-button">Save</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: ['workflow', 'isCloning'],
  data: () => ({ form: { name: '' } }),
  computed: {
    modalTitle () {
      if (this.isCloning) return 'Clone Workflow'
      if (this.workflow && this.workflow.id) return 'Edit Workflow'
      return 'New Workflow'
    }
  },
  watch: {
    workflow: {
      immediate: true,
      handler (newVal) {
        if (newVal) {
          this.form = {
            id: newVal.id,
            name: newVal.name || ''
          }
        } else {
          this.form = { name: '' }
        }
      }
    }
  },
  methods: {
    submit () {
      this.$emit('update', Object.assign({}, this.form))
    }
  }
}
</script>

<style scoped>
/* .new-workflow-label {
    font-size: 0.875rem;
    font-family: Roboto, "Source Sans Pro", arial, sans-serif;
} */


.modal-title {
  color: #000;
}

.modal-header {
  padding: 20px 20px 10px 20px;
  border-bottom: 1px solid #cccccc;
}
</style>

