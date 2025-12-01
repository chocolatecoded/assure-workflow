@extends('layout.admin')

@section('content')
<div class="row">
    <div class="coulmns large-12">
        <div id="workflow-app"></div>
    </div>
</div>
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