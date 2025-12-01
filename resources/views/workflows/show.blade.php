@extends('workflow::layouts.app')

@section('content')
    <div id="workflow-instance-app"
         data-id="{{ $instance->id }}"
         data-status="{{ $instance->status }}"
         data-workflow_id="{{ $instance->workflow_id }}"
         data-context='@json($instance->context)'></div>
@endsection

