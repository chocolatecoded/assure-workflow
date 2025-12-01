<template>
  <div class="mt-2">
    <div>
      <button class="action-button" @click="openCreate">Add Condition</button>
    </div>

    <div>
      <table class="table mt-15">
        <thead>
          <tr>
            <th>Match Type</th>
            <th>Name</th>
            <th>Value</th>
            <th>Show Step</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
          <StepConditionItem
            v-for="(item, key) in localConditions"
            :key="item.id || key"
            :condition="item"
            :workflow="workflow"
            :step="step"
            @remove="(c) => remove(c)"
            @refresh="() => {}"
            @update="onEditCondition"
            v-if="(localConditions || []).length"
          />
      </table>
    </div>

    <StepDetailConditions
      v-if="modalVisible"
      :visible="modalVisible"
      :draft="condition"
      :match-types="matchTypes"
      :form-options="formComponentOptions"
      :resubmit-options="StepShowOptions"
      @save="saveFromModal"
      @close="modalVisible = false"
    />

  </div>
</template>

<script>
import axios from 'axios'
import StepConditionItem from './conditions/StepConditionItem.vue'
import StepDetailConditions from './steps/StepDetailConditions.vue'

export default {
  components: { StepConditionItem, StepDetailConditions },
  props: ['workflow', 'step'],
  data () {
    return {
      localConditions: JSON.parse(JSON.stringify(this.step.conditions || [])),
      condition: { components: '', condition_id: '', match_type: '', name: '', value: '', workflow_show_step_id: '' },
      workflowId: null,
      stepId: null  ,
      components: [],
      modalVisible: false,
    }
  },
  beforeMount()
  {
    this.workflowId = this.workflow.id
    this.stepId = this.step && this.step.id;

    this.getFormComponents();
  },
  computed: {
    matchTypes() {
      return {
        "CONTAINS": "CONTAINS",
        "NOTCONTAINS": "NOT CONTAIN",
        "EQUALS": "EQUALS",
        "NOTEQUALS": "NOT EQUALS"
      }
    },

    StepShowOptions() {

      let steps = this.workflow.steps;

      // Show onward Steps from current Step
      let index = steps.findIndex(step => step.id == this.step.id);
      let optSteps = steps.slice(index + 1, steps.length);

      return optSteps.map(f => {
          return { id: f.id, text: f.name}
      });

    },

    formComponentOptions() {

      if(this.components.length == 0) {
        return [];
      }

      return this.components.questions
        .filter(c => ![
          'control_text',
          'control_button',
          'control_any_fileupload',
          'control_signature',
          'control_textbox'
        ].includes(c.type))
        .map(c => ({
          id: c.qid,
          text: c.text,
          options: c.options || [],
          name: c.name,
          type: c.type
        }));

    }
  },
  methods: {
    openCreate () {
      // reset for create
      this.condition = { components: '', condition_id: '', match_type: '', name: '', value: '', workflow_show_step_id: '' }
      this.modalVisible = true
    },
    onEditCondition (cond) {
      const comp = (this.formComponentOptions || []).find(o => String(o.id) === String(cond.condition_id))
      this.condition = {
        id: cond.id,
        components: comp || '',
        condition_id: cond.condition_id || '',
        match_type: cond.match_type || '',
        name: cond.name || (comp ? comp.text : ''),
        value: cond.value || '',
        workflow_show_step_id: cond.workflow_show_step_id || ''
      }
      this.modalVisible = true
    },
    remove (c) {
      this.localConditions = this.localConditions.filter(x => x.id !== c.id)
      this.syncUpConditions()
      const next = JSON.parse(JSON.stringify(this.localConditions || []))
      this.$emit('changed', next)
    },
    async saveFromModal (payload) {
      const draft = payload || this.condition
      // derive name/id from selected component if present
      if (draft && draft.components && typeof draft.components === 'object') {
        draft.name = draft.components.name
        draft.text = draft.components.text
        draft.condition_id = draft.components.id
      }
      if (draft && draft.id) {
        const res = await axios.put(`/api/workflow/${this.workflow.id}/steps/${this.step.id}/conditions/${draft.id}`, draft)
        const updated = (res && res.data) ? res.data : draft
        const idx = this.localConditions.findIndex(x => x.id === updated.id)
        if (idx !== -1) this.$set(this.localConditions, idx, updated)
      } else {
        const { data } = await axios.post(`/api/workflow/${this.workflow.id}/steps/${this.step.id}/conditions`, draft)
        this.localConditions.push(data)
      }
      this.syncUpConditions()
      const next = JSON.parse(JSON.stringify(this.localConditions || []))
      this.$emit('changed', next)
      this.modalVisible = false
    },
    async getFormComponents() {
      const data = await axios.get(`/api/workflow/${this.workflow.id}/steps/${this.step.data.formId}/form-components`)
      this.components = data.data.components
    },
    syncUpConditions () {
      // Update parent step.conditions without refetch
      const next = JSON.parse(JSON.stringify(this.localConditions || []))
      if (this.step) {
        this.$set(this.step, 'conditions', next)
      }
    }
  }
}
</script>

