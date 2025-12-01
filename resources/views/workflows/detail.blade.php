@extends('layout.admin')

@section('content')
    <div id="workflow-detail-app"
         data-id="{{ $workflow->id }}"
         data-name="{{ $workflow->name }}"
         data-description="{{ $workflow->description }}"
         data-link_to_locations="{{ isset($workflow->linkToLocations) ? $workflow->linkToLocations : '' }}"
    ></div>
@endsection

@section('scripts')
<link rel="stylesheet" href="/vendor/assure-workflow/css/workflow.css">
<script src="/vendor/assure-workflow/js/workflow.js"></script>
@endsection

<style>
    .max-width-none {
        max-width: none !important;
    }
</style>