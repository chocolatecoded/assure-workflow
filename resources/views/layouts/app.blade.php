<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workflow</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/vendor/assure-workflow/css/workflow.css">
    @yield('head')
</head>
<body>
@yield('content')
<script src="/vendor/assure-workflow/js/workflow.js"></script>
@yield('scripts')
</body>
</html>

