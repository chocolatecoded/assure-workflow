<template>
  <div id="workflow-modal" class="wf-modal-backdrop">
    <div class="wf-modal-card width-70">
      <div class="d-flex justify-content-between align-items-center mb-2 modal-header">
        <h5 class="mb-0 modal-title">
          {{ getTitle }}
        </h5>
        <button type="button" class="close" @click="$emit('closed')">×</button>
      </div>

      <!-- Add a ref to the modal body -->
      <div class="modal-body" ref="modalBody">
      <form>
          <div class="b-custom-form">

            <!-- Step Name -->
            <div class="form-group">
              <label for="name-1">Step Name:</label>
              <input
                id="name-1"
                v-model="ghost.name"
                type="text"
                placeholder="Enter name"
                required
                class="form-control mb-0"
              />
              <p class="label v-required" v-if="serverError('name')">{{ serverError('name') }}</p>
            </div>

            <!-- Step Type -->
            <div class="form-group">
              <label for="step-type">Step Type:</label>
              <select id="step-type" v-model="ghost.type" class="form-control">
                <option v-for="(label, value) in flowTypes" :key="value" :value="value">{{ label }}</option>
              </select>
              <p class="label v-required" v-if="serverError('type')">{{ serverError('type') }}</p>
            </div>

            <!-- Module Step Type -->
            <div class="form-group">
              <label for="module-1">Module Step Type:</label>
              <select id="module-1" v-model="ghost.module" class="form-control">
                <option v-for="(label, value) in stepTypes" :key="value" :value="value">{{ label }}</option>
              </select>
              <p class="label v-required" v-if="serverError('module')">{{ serverError('module') }}</p>
            </div>

            <!-- Form Name (COMPOSER) -->
            <div class="form-group" v-if="ghost.module == 'COMPOSER'">
              <label for="form-name">Form Name:</label>
              <select2
                :value="ghost.data.formId"
                @select="onSelect('formId', $event)"
                :options="formOptions"
                :settings="{
                  placeholder: 'Select Form',
                  dropdownParent: $refs.modalBody,
                  matcher: customMatcher
                }"
              />

              <p class="label v-required" v-if="serverError('formId')">{{ serverError('formId') }}</p>
            </div>

            <!-- Approval-specific Fields -->
            <template v-if="ghost.module == 'APPROVAL'">
              <div class="form-group">
                <label for="show-1">If Resubmit What Step To Show:</label>

                <select2
                  :value="ghost.data.declineGoBack"
                  @select="onSelect('declineGoBack', $event)"
                  :options="formResubmitOptions"
                  :settings="{dropdownParent: $refs.modalBody}"
                />

                <p class="label v-required" v-if="serverError('declineGoBack')">{{ serverError('declineGoBack') }}</p>

              </div>

              <div class="form-group">
                <label for="form-checklist">Select Checklist Form (optional):</label>
                <select2
                  :value="ghost.data.checklistId"
                  @select="onSelect('checklistId', $event)"
                  :options="formOptions"
                  :settings="{placeholder: 'Select Form', dropdownParent: $refs.modalBody, allowClear: true}"
                />
              </div>

              <div class="form-group">
                <label for="form-approve">Select Steps To Approve:</label>
                <select2
                  :value="ghost.data.formsToApprove"
                  @select="onSelect('formsToApprove', $event)"
                  :options="formApproveOptions"
                  :settings="{placeholder: 'Select Step', multiple: true, dropdownParent: $refs.modalBody}"
                />
              </div>
              <p class="label v-required" v-if="serverError('formsToApprove')">{{ serverError('formsToApprove') }}</p>
            </template>
          </div>

        </form>

        <!-- Conditions editor -->
        <step-conditions
          v-if="ghost.id && ghost.module == 'COMPOSER'"
          :step="ghost"
          :workflow="workflow"
          :forms="forms"
          @changed="onConditionsChanged"
          @remove="remove"
          @refresh="refresh"
        />

        <!-- Footer Buttons -->
        <div class="text-right">
          <button type="button" class="action-button" @click="$emit('closed')">Cancel</button>
          <button class="action-button" @click="updateStep">Save</button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import select2 from 'v-select2-component'
import cloneDeep from 'lodash.clonedeep'
import GenericMixin from '../mixins/genericMixin';
import { filter } from 'lodash';
import StepConditions from '../conditions/stepConditions.vue'

export default {
  name: 'workflow-step',
  props: ['workflow', 'step', 'forms', 'errors'],
  components: { select2, StepConditions },
  mixins: [
    GenericMixin
  ],
  data: () => ({
    vErrors: [],
    steps: '',
    ghost: { name: '', type: '', module: '', data: { platform: 'all', required: false, formId: '', declineGoBack: '', checklistId: '', formsToApprove: [] } },
    original: {}
  }),
  mounted () {
    if (!this.step) {
      this.ghost = cloneDeep({ name: '', type: '', module: '', data: { platform: 'all', required: false, formId: '', declineGoBack: '', checklistId: '', formsToApprove: [] } })
    } else {
      this.ghost = cloneDeep(this.step)
      this.original = this.step
    }
  },
  computed: {
    stepTypes () {
      return { 'COMPOSER': 'Forms', 'APPROVAL': 'Approval Required' }
    },
    flowTypes () {
      return this.workflow.stepTypes || {}
    },
    platforms () {
      return { 'all': 'Both Kiosk and Mobile App', 'mobile': 'Mobile App Only' }
    },
    getTitle () {
      if (!this.step || !this.step.name) return 'New Step'
      return `Editing ${this.step.name}`
    },
    formOptions () {
      // normalize id to string to avoid equality issues
      return (this.workflow.forms || []).map(f => ({ id: f.id, text: `${f.title} (${f.id})` }))
    },
    formApproveOptions () {
      return this.workflow.steps.map(s => ({ id: s.id, text: s.name }))
    },

    formResubmitOptions () {

      const currentStepId = this.ghost.id
      const steps = this.workflow.steps || [];

      // If editing an existing step, filter steps before current step
      const filtered = currentStepId
        ? steps.filter(step => step.id < currentStepId)
        : steps; // If creating new step, show all steps

      return filtered.map(f => ({ id: f.id, text: f.name }))
        
    },
    serverError () {
      return (field) => {
        const list = this.errors && this.errors[field]
        return Array.isArray(list) && list.length ? list[0] : ''
      }
    },
  },
  methods: {
    onSelect(field, value) {
      const isMulti = field === 'formsToApprove';

      if (value && typeof value === 'object' && value.id !== undefined) {
        if (isMulti) {
          const arr = this.ghost.data[field] || [];
          if (value.selected) {
            if (!arr.includes(value.id)) arr.push(value.id);
          } else {
            const index = arr.indexOf(value.id);
            if (index !== -1) arr.splice(index, 1);
          }
          this.$set(this.ghost.data, field, arr);
        } else {
          this.$set(this.ghost.data, field, value.id);
        }
      } else {
        this.$set(this.ghost.data, field, value);
      }
    },
    updateStep () {
      const data = Object.assign(this.original || {}, this.ghost)
      this.$emit('update', data)
      // close after save
      // this.$emit('closed')
    },
    onConditionsChanged (conditions) {
      // update local ghost and notify parent so list counts refresh immediately
      this.$set(this.ghost, 'conditions', conditions || [])
      this.$emit('conditions-change', { stepId: this.ghost.id, conditions: conditions || [] })
    },
    refresh () { this.$emit('refresh') },
    remove () { this.$emit('refresh') },
    onHide () { this.$emit('closed') },
    onCancel (hide) {
      this.show = false
      if (typeof hide === 'function') hide()
      this.$emit('closed')
    },

    // Generate Dropdown options for steps list
    stepOptions(steps) {

      let dropDownOptions = [];

      // If creating new step show all Steps
      if(!this.ghost.id) {
        dropDownOptions = this.mapOptions(steps);
      } else {
        // Show Previous Steps from current Step
        let index = steps.findIndex(step => step.id == this.ghost.id);
        let optSteps = steps.slice(0, index);
        dropDownOptions = this.mapOptions(optSteps);
      }

      return dropDownOptions;
      
    },

    mapOptions(steps) {

        let options = {};
        for (let step of steps) {
          options[step.id] = step.name;
        }

        return options
    },

    customMatcher(params, data) {
      if ($.trim(params.term) === '') {
        return data;
      }

      const term = params.term.toLowerCase();

      // Search in text
      const text = (data.text || '').toLowerCase();

      // Search in ID
      const id = (data.id || '').toString().toLowerCase();

      if (text.includes(term) || id.includes(term)) {
        return data;
      }

      return null;
    },
  }
}
</script>

<style>
h5 {
  color: #000 !important;
}

.select2-selection--multiple {
  border: 1px solid #aaa !important;
  border-radius: 4px !important;
}

.select2-selection--multiple {
    color: #444;
    line-height: 28px;
}
.select2-selection--multiple {
    display: block;
    padding-left: 8px;
    padding-right: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

