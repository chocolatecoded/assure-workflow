<template>
  <div v-if="visible" id="modal-condition" class="wf-modal-backdrop">
    <div class="wf-modal-card width-60">
      <div class="d-flex justify-content-between align-items-center mb-2 modal-header">
        <h5 class="mb-0 modal-title">
          {{ draft && draft.id ? 'Edit Step Condition' : 'New Step Condition' }}
        </h5>
        <button type="button" class="close" @click="$emit('close')">×</button>
      </div>
      <div class="modal-body" ref="modalBody">

        <form @submit.prevent="onSubmit">
          <div class="form-group">
            <label>Match Type:</label>
            <select class="form-control" v-model="draft.match_type" required>
              <option v-for="(label, value) in matchTypes" :key="value" :value="value">{{ label }}</option>
            </select>
          </div>
          <div class="form-group">
            <label>Form Component:</label>
            <select class="form-control" v-model="draft.components" required>
              <option v-for="value in formOptions" :key="value.id" :value="value">{{ value.text }}</option>
            </select>
          </div>
          <div class="form-group">
            <label>Value: (match to what value)</label>

            <template v-if="matchOptions">

              <select class="form-control" v-model="draft.value" required>
                <option v-for="o in matchOptions" :value="o.text">{{ o.text }}</option>
              </select>

            </template>

            <template v-else>

              <input
                id="value"
                type="text"
                placeholder="Enter Value"
                required
                class="form-control mb-0"
                v-model="draft.value"
              />

            </template>

          </div>
          <div class="form-group">
            <label>Show Step:</label>
            <select class="form-control" v-model="draft.workflow_show_step_id" required>
              <option v-for="value in resubmitOptions" :key="value.id" :value="value.id">{{ value.text }}</option>
            </select>
          </div>

          <!-- Footer Buttons -->
          <div class="text-right">
            <button type="button" class="action-button" @click="$emit('close')">Cancel</button>
            <button type="submit" class="action-button">Save</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script>
import select2 from 'v-select2-component'

export default {
  name: 'StepDetailConditions',
  components: { select2 },
  props: {
    visible: { type: Boolean, default: false },
    draft:   { type: Object,  required: true },
    formOptions: { type: Array, default: () => [] },
    resubmitOptions: { type: Array, default: () => [] },
    matchTypes: { type: Object, default: () => ({}) }
  },
  data () {
    return {
    }
  },
  watch: {
    'draft.components': function () {
      // reset value when component changes so invalid previous selections don't persist
      if (this.draft) {
        this.$set(this.draft, 'value', '')
      }
    }
  },
  computed: {
    matchOptions () {
      const comp = this.draft && this.draft.components
      if (!comp || !comp.options) return false
      let raw = comp.options
      if (typeof raw === 'string') {
        raw = raw.split('|').map(s => s.trim()).filter(Boolean)
      }
      if (!Array.isArray(raw) || !raw.length) return false
      return raw.map(opt => {
        if (typeof opt === 'object' && opt !== null) {
          const id = String(opt.id != null ? opt.id : (opt.value != null ? opt.value : (opt.text != null ? opt.text : '')))
          const text = String(opt.text != null ? opt.text : (opt.value != null ? opt.value : (opt.id != null ? opt.id : '')))
          return { id, text }
        }
        const id = String(opt)
        return { id, text: opt }
      })
    }
  },
  methods: {
    onSubmit () {
      // emit merged payload so parent can perform create/update
      const payload = Object.assign({}, this.draft)
      // If components object present, map name/condition_id as convenience (parent also handles)
      if (payload && payload.components && typeof payload.components === 'object') {
        payload.text = payload.components.text
        payload.name = payload.components.name
        payload.condition_id = payload.components.id
        payload.data = { type: payload.components.type }
      }
      this.$emit('save', payload)
    }
  }
}
</script>

<style scoped>
#modal-condition { z-index: 100000; }
.select2-results__option { color: #000 !important; }
</style>


