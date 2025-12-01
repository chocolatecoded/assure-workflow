<template>
  <div>
    <WorkflowConditions :step="step" :workflow="workflow" :forms="forms" @remove="$emit('remove')" @refresh="$emit('refresh')" @changed="(c) => changed(c)"/>
  </div>
</template>

<script>
import WorkflowConditions from '../../components/WorkflowConditions.vue'

export default {
  name: 'step-conditions',
  components: { WorkflowConditions },
  props: ['step', 'workflow', 'forms'],

  methods: {
      changed (conditions) {
      this.$emit('changed', conditions)
    },
  }
}
</script>

