@extends('workflow::layouts.app')

@section('content')
    <div id="workflow-edit-app"
         data-id="{{ $workflow->id }}"
         data-name="{{ $workflow->name }}"
         data-description="{{ $workflow->description }}"></div>
@endsection

