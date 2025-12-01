{{-- 
    Configurable Workflows Dropdown Options Component
    
    This component renders configurable workflows in a separate optgroup.
    It automatically shows/hides based on:
    - Feature flag (Yes/No)
    - Whether workflows exist
    
    Usage in your view:
    @include('workflow::partials.workflow-dropdown-options', [
        'workflows' => $configurableWorkflows,
        'selectedValue' => $client->pra_form_configuration
    ])
--}}
@if(isset($workflows) && $workflows->count() > 0)
<optgroup label="Configurable workflows" id="configurable-workflows-group" style="display: none;">
    @foreach($workflows as $workflow)
        <option value="WORKFLOW/{{ $workflow->id }}" {{ isset($selectedValue) && $selectedValue == 'WORKFLOW/' . $workflow->id ? 'selected' : '' }}>
            {{ $workflow->name }}
        </option>
    @endforeach
</optgroup>
@endif

