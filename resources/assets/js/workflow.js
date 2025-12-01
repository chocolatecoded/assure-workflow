// Prefer a global Vue if present (avoids conflicts with other loaders)
const VueLib = (typeof window !== 'undefined' && window.Vue)
  ? window.Vue
  : (require('vue').default || require('vue'))

import axios from 'axios'
import 'jquery'
import 'popper.js'
import 'bootstrap'
const BootstrapVue = (require('bootstrap-vue').default || require('bootstrap-vue'))
import Swal from 'sweetalert2'

import WorkflowList from './components/WorkflowList.vue'
import WorkflowEdit from './components/WorkflowEdit.vue'
import WorkflowInstance from './components/WorkflowInstance.vue'
import WorkflowDetail from './components/workFlowDetail.vue'

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
const token = document.querySelector('meta[name="csrf-token"]')
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content')
}

// microservice-like $api wrapper with (url, params, config)
VueLib.prototype.$api = {
  get (url, params = {}, config = {}) {
    return axios.get(url, Object.assign({ params }, config))
  },
  post (url, data = {}, config = {}) {
    return axios.post(url, data, config)
  },
  put (url, data = {}, config = {}) {
    return axios.put(url, data, config)
  },
  delete (url, config = {}) {
    return axios.delete(url, config)
  }
}

VueLib.prototype.$swal = Swal

// Inline list component removed in favor of SFC import

const boot = () => {
  const el = document.getElementById('workflow-app')
  if (el) {
    new VueLib({
      render: h => h(WorkflowList)
    }).$mount('#workflow-app')
  }
  const editEl = document.getElementById('workflow-edit-app')
  if (editEl) {
    const wfId = editEl.getAttribute('data-id')
    const wfName = editEl.getAttribute('data-name')
    const wfDesc = editEl.getAttribute('data-description')
    new VueLib({
      render: h => h(WorkflowEdit, { props: { id: wfId, initialName: wfName, initialDescription: wfDesc } })
    }).$mount('#workflow-edit-app')
  }
  const detailEl = document.getElementById('workflow-detail-app')
  if (detailEl) {
    const id = detailEl.getAttribute('data-id')
    const name = detailEl.getAttribute('data-name')
    const description = detailEl.getAttribute('data-description')
    const work = { id, name, description, linkToLocations: detailEl.getAttribute('data-link_to_locations') || '' }
    new VueLib({
      render: h => h(WorkflowDetail, { props: { id, work } })
    }).$mount('#workflow-detail-app')
  }
  const instEl = document.getElementById('workflow-instance-app')
  if (instEl) {
    const id = instEl.getAttribute('data-id')
    const status = instEl.getAttribute('data-status')
    const workflowId = instEl.getAttribute('data-workflow_id')
    const contextRaw = instEl.getAttribute('data-context') || '{}'
    let context
    try { context = JSON.parse(contextRaw) } catch (e) { context = {} }
    new VueLib({
      render: h => h(WorkflowInstance, { props: { id, status, workflowId, context } })
    }).$mount('#workflow-instance-app')
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot)
} else {
  // DOM already ready
  boot()
}

