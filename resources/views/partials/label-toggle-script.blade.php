{{-- 
    Configurable Workflows Label and Dropdown Toggle Script
    
    This script handles:
    1. Label text change (PRA Form Config <-> PRA Workflow)
    2. Show/hide configurable workflows optgroup based on feature flag
    
    Usage in your scripts section:
    @section('scripts')
    <script>
    @include('workflow::partials.label-toggle-script')
    </script>
    @endsection
--}}
// Configurable Workflows Toggle - Label and dropdown visibility
(function() {
    const configurableWorkflowsYes = document.getElementById('configurable_workflows_yes');
    const configurableWorkflowsNo = document.getElementById('configurable_workflows_no');
    const praFieldLabel = document.getElementById('pra-field-label');
    const configurableWorkflowsGroup = document.getElementById('configurable-workflows-group');
    const hardcodedFormOptions = document.getElementsByClassName('hardcoded-form');
    
    // Function to toggle UI based on radio button selection
    function toggleConfigurableWorkflows() {
        const isEnabled = configurableWorkflowsYes && configurableWorkflowsYes.checked;
        
        // Change label text based on feature flag
        if (praFieldLabel) {
            praFieldLabel.textContent = isEnabled ? 'PRA Workflow' : 'PRA Form Config';
        }
        
        // Show/hide configurable workflows optgroup
        // Only shown when feature flag is Yes AND workflows exist 
        // Hidden when feature flag is No OR no workflows exist 
        if (configurableWorkflowsGroup) {
            configurableWorkflowsGroup.style.display = isEnabled ? '' : 'none';

            for (let i = 0; i < hardcodedFormOptions.length; i++) {
                hardcodedFormOptions[i].style.display = isEnabled ? 'none' : '';
            }
        }
    }
    
    // Set initial state on page load
    toggleConfigurableWorkflows();
    
    // Add event listeners for immediate feedback
    if (configurableWorkflowsYes) {
        configurableWorkflowsYes.addEventListener('change', toggleConfigurableWorkflows);
    }
    if (configurableWorkflowsNo) {
        configurableWorkflowsNo.addEventListener('change', toggleConfigurableWorkflows);
    }
})();

