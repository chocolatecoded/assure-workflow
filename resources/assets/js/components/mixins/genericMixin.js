export default {

  data: () => ({
    show: false,
    new: false,
    ghost: {},
    original: {}
  }),
  created() {
    console.log("MIXIN HERE")
  },
  methods: {
      getStepNameById(id) {
        let step = this.getStepById(id)
        if (step) return step.name
        return '-'
      },

      getStepById(id) {
        for (let item of this.workflow.steps) {
          if (item.id == id)
            return item
        }
        return {}
      },

      stepOptionsOrderHigherThan(number) {

        let steps = this.workflow.steps.filter((item) => {
          return item.order > number
        })

        return this.stepOptions(steps)
      },
      
      // focus item
      focus() {
        this.$set(this, 'show', true)
      },

      refresh() {
        this.$emit('refresh')
      },

      // close view
      close() {
        console.log("CLOSING MODAL")
        this.$emit('closed')
        this.$set(this, 'show', false)
      },

      // send updated value back
    update() {
      //   this.$set(this, 'show', false)
        this.$emit('update', this.ghost)
      },

      // remove event back
      remove() {
        console.log("REMOVING")
        this.$emit('remove', true)
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





  }
}