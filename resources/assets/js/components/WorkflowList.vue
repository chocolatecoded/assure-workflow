<template>
  <div class="row">
    <div class="large-12">
      <div class='row'>

        <div class="large-6 columns no-padding">
          <h2 class="capitalize">Workflow</h2>
        </div>

        <div class="large-6 columns" style="text-align: right; margin-bottom: 10px;">
          <button @click.prevent="importWorkFlow()" class="action-button menu" role="button">Import Workflow</button>
          <button @click.prevent="addWorkFlow" class="action-button menu" role="button">Add Workflow</button>
          <WorkflowModal v-if="creatingWorkflow || editingWorkflow || cloningWorkflow"
                          @closed="closeModal"
                          @update="saveWorkflow"
                          :workflow="workflowToEdit"
                          :workflows="workflows"
                          :isCloning="cloningWorkflow"/>
        </div>
      </div>

      <div class="row">
        <div class="large-12 columns">
          <table style="width: 100%" class="table responsive stripe woTable">
            <thead>
              <tr>
                <th>Name</th>
                <th>Clients</th>
                <th></th>
              </tr>

              <tr>
                <th><input type="text" v-model="nameFilter" @input="onNameFilterChange" placeholder="Filter by Name" class="filter-input"></th>
                <th><input type="text" placeholder="Filter by Client" class="filter-input"></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template v-if="!workflows.length">
                <tr>
                  <td></td>
                  <td style="text-align: center; width: 100%;">No Workflows Created Yet</td>
                  <td></td>
                </tr>
              </template>
              <template v-else>
                <tr v-for="item in workflows" :key="item.id">
                  <td class="align-middle">
                    <i @click="editWorkFlow(item)" class="fa fa-edit edit-icon" style="cursor: pointer; margin-right: 8px;" title="Edit Workflow"></i>
                    {{ item.name }}
                  </td>
                  <td class="align-middle">{{ item.description ? item.description : "None" }}</td>
                  <td class="align-middle">
                    <button @click="selectWorkflow(item)" class="action-button">Edit</button>
                    <button @click="cloneWorkflow(item)" class="action-button">Clone</button>
                    <button @click="exportWorkflow(item)" class="action-button">Export</button>
                    <button @click="deleteNofication(item)" class="action-button">Delete</button>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1" class="pagination-container" style=" margin-bottom: 20px; text-align: center;">
            <div class="pagination-info" style="margin-bottom: 10px; color: white">
              Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} workflows
            </div>
            <ul class="pagination" style="display: inline-flex; list-style: none; padding: 0; margin: 0;">
              <li>
                <button 
                  @click="goToPage(pagination.current_page - 1)" 
                  :disabled="!pagination.prev_page_url"
                  class="pagination-button"
                  :class="{ 'disabled': !pagination.prev_page_url }">
                  Previous
                </button>
              </li>
              <li v-for="page in getPageNumbers()" :key="page">
                <button 
                  v-if="page !== '...'"
                  @click="goToPage(page)"
                  class="pagination-button"
                  :class="{ 'active': page === pagination.current_page }">
                  {{ page }}
                </button>
                <span v-else class="pagination-ellipsis">...</span>
              </li>
              <li>
                <button 
                  @click="goToPage(pagination.current_page + 1)" 
                  :disabled="!pagination.next_page_url"
                  class="pagination-button"
                  :class="{ 'disabled': !pagination.next_page_url }">
                  Next
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import WorkflowDetail from './workFlowDetail.vue'
import WorkflowModal from './modal/workflowModal.vue'
import Swal from 'sweetalert2'

export default {
  name: 'workflow-datatable',
  components: { WorkflowDetail, WorkflowModal },
  props: ['account'],
  data: () => ({
    workflows: [],
    view: false,
    id: null,
    item: [],
    creatingWorkflow: false,
    editingWorkflow: false,
    cloningWorkflow: false,
    workflowToEdit: null,
    workflowToClone: null,
    forms: [],
    work: null,
    newCreatedWorkFlow: null,
    accountId: null,
    pagination: null,
    currentPage: 1,
    nameFilter: '',
    filterTimeout: null
  }),
  mounted () {
    this.accountId = this.account ? this.account.id : null
    this.getWorkflows(false)
    this.getforms()
    // Ensure create modal is hidden when returning via back/forward cache
    this.onPageShow = () => { 
      this.creatingWorkflow = false
      this.editingWorkflow = false
      this.cloningWorkflow = false
    }
    if (typeof window !== 'undefined') {
      window.addEventListener('pageshow', this.onPageShow)
    }
  },
  beforeDestroy () {
    if (typeof window !== 'undefined' && this.onPageShow) {
      window.removeEventListener('pageshow', this.onPageShow)
    }
    // Clear filter timeout to prevent memory leaks
    if (this.filterTimeout) {
      clearTimeout(this.filterTimeout)
    }
  },
  methods: {
    async getWorkflows (bool, currentWorkId = false, page = 1, name = null) {
      const params = { accountId: this.accountId, page: page }
      if (name !== null && name.trim() !== '') {
        params.name = name.trim()
      }
      const response = await this.$api.get('/api/workflow', params).catch((e) => {
        this.errorMessage(e && e.response && e.response.data && e.response.data.message)
      })
      if (!response) return
      
      // Handle pagination response
      if (response.data && response.data.data) {
        this.$set(this, 'workflows', response.data.data)
        // Store pagination metadata
        this.$set(this, 'pagination', {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total,
          from: response.data.from,
          to: response.data.to,
          prev_page_url: response.data.prev_page_url,
          next_page_url: response.data.next_page_url
        })
        this.currentPage = response.data.current_page
      } else {
        this.$set(this, 'workflows', [])
        this.$set(this, 'pagination', null)
      }

      if (currentWorkId) this.findWorkflow(currentWorkId)
    },
    async getforms () {
      this.$set(this, 'forms', [])
    },
    selectWorkflow (item) {
      this.id = item.id
      this.work = item
      // window.history.pushState('', '', `/workflow/${this.id}`)
      window.location.href = `/workflow/${this.id}`;
    },
    remove () {
      this.$api.delete(`/api/workflow/${this.item.id}`)
        .then(() => {
          this.successMessage(this.item, 'deleted')
          // Stay on current page, or go to previous page if current page becomes empty
          const page = this.pagination && this.workflows.length === 1 && this.pagination.current_page > 1
            ? this.pagination.current_page - 1
            : (this.pagination ? this.pagination.current_page : 1)
          this.getWorkflows(false, false, page)
        })
        .catch((e) => {
          this.handleWorkflowError(e)
        })
    },
    deleteNofication (item) {
      this.item = item
      Swal.fire({
        text: `Are you sure you want to delete workflow ${this.item.name}?`,
        icon: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
      }).then((result) => {
        if (result.value) {
          this.remove()
        }
      })
    },
    addWorkFlow () {
      this.workflowToEdit = null
      this.workflowToClone = null
      this.cloningWorkflow = false
      this.editingWorkflow = false
      this.$set(this, 'creatingWorkflow', true)
    },
    editWorkFlow (item) {
      this.workflowToEdit = item
      this.workflowToClone = null
      this.cloningWorkflow = false
      this.creatingWorkflow = false
      this.$set(this, 'editingWorkflow', true)
    },
    closeModal () {
      this.creatingWorkflow = false
      this.editingWorkflow = false
      this.cloningWorkflow = false
      this.workflowToEdit = null
      this.workflowToClone = null
    },
    saveWorkflow (workflow) {
      if (this.accountId) workflow.account_id = this.accountId
      
      // Handle cloning
      if (this.cloningWorkflow && this.workflowToClone) {
        this.$api.post(`/api/workflow/${this.workflowToClone.id}/clone`, { name: workflow.name })
          .then((r) => {
            this.closeModal()
            // Insert cloned workflow at top of current list for instant feedback
            this.workflows.unshift(r.data)
            // If pagination present and we exceeded page size, trim the last item to maintain UI consistency
            if (this.pagination && this.workflows.length > this.pagination.per_page) {
              this.workflows.pop()
            }
            this.successMessage(r.data, 'cloned')
          })
          .catch((e) => {
            // Don't close modal on error - let user fix the validation error
            this.handleWorkflowError(e)
          })
        return
      }
      
      const isUpdate = !!workflow.id
      const apiCall = isUpdate
        ? this.$api.put(`/api/workflow/${workflow.id}`, workflow, {})
        : this.$api.post('/api/workflow', workflow, {})
      
      apiCall
        .then((r) => {
          this.closeModal()
          if (isUpdate) {
            this.successMessage(r.data, 'updated')
            // Stay on current page after update
            const page = this.pagination ? this.pagination.current_page : 1
            this.getWorkflows(false, false, page)
          } else {
            this.newCreatedWorkFlow = r.data
            // Go to first page to show newly created workflow
            this.getWorkflows(true, false, 1)
            // this.successMessage(workflow, 'created')
          }
        })
        .catch((e) => {
          // Don't close modal on error - let user fix the validation error
          this.handleWorkflowError(e)
        })
    },
    handleWorkflowError (e) {
      // Parse laravel validation response
      if (e.response && e.response.data && e.response.data.errors) {
        const errorMessages = Object.entries(e.response.data.errors)
          .map(([field, messages]) => {
            const fieldName = field.charAt(0).toUpperCase() + field.slice(1).replace(/_/g, ' ')
            return `${fieldName}: ${messages.join(', ')}`
          })
          .join('\n')
        this.errorMessage(errorMessages)
      } else {
        const errorMsg = e.response && e.response.data && e.response.data.message || 'An error occurred'
        this.errorMessage(errorMsg)
      }
    },
    successMessage (workflow, action) {
      Swal.fire('', `Successfully ${action} ${workflow.name} workflow.`, 'success')
    },
    errorMessage (errorMsg) {
      Swal.fire('', errorMsg, 'error')
    },
    home () {
      this.view = false
    },
    cloneWorkflow (item) {
      // Reset all modal states first
      this.creatingWorkflow = false
      this.editingWorkflow = false
      this.workflowToEdit = null
      this.workflowToClone = null
      
      // Set up the modal for cloning
      this.workflowToClone = item
      this.$nextTick(() => {
        this.workflowToEdit = {
          name: `Copy of ${item.name}`
        }
        this.$set(this, 'cloningWorkflow', true)
      })
    },
    async exportWorkflow (item) {
      try {
        const { data } = await this.$api.get(`/api/workflow/${item.id}/export`)
        const json = JSON.stringify(data, null, 2)
        const blob = new Blob([json], { type: 'application/json' })
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        const ts = new Date().toISOString().replace(/[:.]/g, '-')
        a.href = url
        a.download = `workflow-${(item.name || 'export').replace(/[^A-Za-z0-9_-]+/g, '-')}-${ts}.json`
        document.body.appendChild(a)
        a.click()
        a.remove()
        window.URL.revokeObjectURL(url)
      } catch (e) {
        this.handleWorkflowError(e)
      }
    },
    importWorkFlow () {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'application/json'
      input.onchange = async (evt) => {
        const file = evt && evt.target && evt.target.files && evt.target.files[0]
        if (!file) return
        try {
          const text = await file.text()
          const payload = JSON.parse(text)
          const { data } = await this.$api.post('/api/workflow/import', { data: payload })
          // Prepend imported workflow to list
          this.workflows.unshift(data)
          if (this.pagination && this.workflows.length > this.pagination.per_page) {
            this.workflows.pop()
          }
          this.successMessage(data, 'imported')
        } catch (e) {
          this.handleWorkflowError(e)
        }
      }
      input.click()
    },
    findWorkflow (workId) {
      const w = this.workflows.find(el => el.id === workId)
      if (w) this.selectWorkflow(w)
    },
    goToPage (page) {
      if (page < 1 || (this.pagination && page > this.pagination.last_page)) return
      // Preserve filter when navigating pages
      const filterValue = this.nameFilter && this.nameFilter.trim() !== '' ? this.nameFilter.trim() : null
      this.getWorkflows(false, false, page, filterValue)
    },
    getPageNumbers () {
      if (!this.pagination) return []
      
      const current = this.pagination.current_page
      const last = this.pagination.last_page
      const pages = []
      
      // Always show first page
      if (last <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= last; i++) {
          pages.push(i)
        }
      } else {
        // Show first page
        pages.push(1)
        
        if (current <= 3) {
          // Near the beginning
          for (let i = 2; i <= 4; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(last)
        } else if (current >= last - 2) {
          // Near the end
          pages.push('...')
          for (let i = last - 3; i <= last; i++) {
            pages.push(i)
          }
        } else {
          // In the middle
          pages.push('...')
          for (let i = current - 1; i <= current + 1; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(last)
        }
      }
      
      return pages
    },
    onNameFilterChange () {
      // Clear existing timeout
      if (this.filterTimeout) {
        clearTimeout(this.filterTimeout)
      }
      
      // Debounce the filter - wait 500ms after user stops typing
      this.filterTimeout = setTimeout(() => {
        // Reset to page 1 when filtering
        this.getWorkflows(false, false, 1, this.nameFilter)
      }, 500)
    }
  }
}
</script>

<style scoped>

/* Add border lines to each cell */
.woTable {
  border-collapse: collapse;
  table-layout: fixed;
  width: 100%;
}
.woTable th,
.woTable td {
  border: 1px solid #FFFFFF !important;
}

/* Edit icon styling */
.edit-icon {
  display: inline-block;
  font-size: 16px;
  line-height: 1;
}

/* Filter input styling */
.filter-input {
  width: 100%;
  box-sizing: border-box;
  min-width: 150px;
}

/* Pagination styles */
.pagination-container {
  padding: 20px 0;
}

.pagination-info {
  color: #666;
  font-size: 14px;
}

.pagination {
  gap: 5px;
}

.pagination-button {
  padding: 8px 12px;
  margin: 0 2px;
  border: 1px solid #ddd;
  background: #fff;
  color: #333;
  cursor: pointer;
  border-radius: 4px;
  min-width: 40px;
}

.pagination-button:hover:not(.disabled):not(.active) {
  background: #f5f5f5;
  border-color: #999;
}

.pagination-button.active {
  background: #e4002b;
  color: #fff;
  border-color: #e4002b;
  font-weight: bold;
}

.pagination-button.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f5f5f5;
}

.pagination-ellipsis {
  padding: 8px 4px;
  color: #666;
}
</style>