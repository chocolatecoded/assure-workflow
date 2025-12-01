<template>
  <tr>
    <th scope="row" class="align-middle">
      <template v-if="condition.match_type === 'NOTCONTAINS'">Not Contains</template>
      <template v-else-if="condition.match_type === 'NOTEQUALS'">NOT EQUALS</template>
      <template v-else>{{ condition.match_type }}</template>
    </th>
    <th scope="row" class="align-middle condition-ellipsis" :title="condition.text">{{ condition.text }}</th>
    <th scope="row" class="align-middle condition-ellipsis" :title="condition.value">{{ condition.value }}</th>
    <th scope="row" class="align-middle condition-ellipsis" :title="showStep">{{ showStep }}</th>
    <td class="text-center">
      <button @click="onEdit" class="action-button">Edit</button>
      <button @click="deleteNofication(condition)" class="action-button">Delete</button>
    </td>
  </tr>
</template>

<script>
import Swal from 'sweetalert2'

export default {
  name: 'workflow-step-condition-item',
  props: ['condition', 'workflow', 'forms', 'step'],
  computed: {
    showStep () {
      const id = this.condition && this.condition.workflow_show_step_id
      const s = (this.workflow.steps || []).find(x => x.id === id)
      return s ? s.name : ''
    }
  },
  methods: {
    onEdit () {
      // Emit the condition so the parent can open and seed the modal
      this.$emit('update', this.condition)
    },
    async update () {
      // Keep for compatibility if parent uses it
      await this.$api.put(`/api/workflow/${this.workflow.id}/steps/${this.step.id}/conditions/${this.condition.id}`, this.condition)
      this.$emit('refresh')
    },
    async remove () {
      await this.$api.delete(`/api/workflow/delete-conditions/${this.condition.id}`)
      this.$emit('remove', this.condition)
    },
    deleteNofication (item) {
      this.item = item
      Swal.fire({
        text: `Are you sure you want to delete condition ${this.item.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
      }).then((result) => {
        if (result && result.value) {

            Swal.fire({
              text: `Successfully deleted ${item.name}`,
              icon: 'success',
              confirmButtonColor: '#d33',
              confirmButtonText: 'Yes'
            });

          this.remove()
        }
      })
    }
  }
}
</script>

<style scoped>
.condition-ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}
</style>



