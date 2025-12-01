<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Workflow Instance #{{ id }}</h1>
      <a href="/workflow" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Status</label>
              <input class="form-control" :value="status" readonly />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Workflow ID</label>
              <input class="form-control" :value="workflowId" readonly />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-header">Context</div>
      <div class="card-body">
        <pre class="mb-0">{{ pretty }}</pre>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: { type: [String, Number], required: true },
    status: { type: String, default: '' },
    workflowId: { type: [String, Number], required: true },
    context: { type: Object, default: () => ({}) }
  },
  computed: {
    pretty () { return JSON.stringify(this.context, null, 2) }
  }
}
</script>

