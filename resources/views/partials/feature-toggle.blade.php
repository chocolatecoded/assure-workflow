{{-- 
    Configurable Workflows Feature Toggle Component
    
    Usage in your view:
    @include('workflow::partials.feature-toggle', ['client' => $client])
--}}
<div style="margin-top: 15px;">
    <label>Configurable Workflows</label>
    {!! Form::radio('configurable_workflows_enabled', '1', $client->configurable_workflows_enabled == 1, ['id' => 'configurable_workflows_yes', 'class' => 'configurable-workflows-toggle']) !!}<label for="configurable_workflows_yes">Yes</label>
    {!! Form::radio('configurable_workflows_enabled', '0', $client->configurable_workflows_enabled == 0 || $client->configurable_workflows_enabled === null, ['id' => 'configurable_workflows_no', 'class' => 'configurable-workflows-toggle']) !!}<label for="configurable_workflows_no">No</label>
    <span class="errors">{{ $errors->first('configurable_workflows_enabled') }}</span>
</div>

