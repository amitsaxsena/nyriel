{{--
    Nyriel — admin panel wrapper.

    The admin styling lives in the same generated stylesheet as the client theme
    (the admin rules are gated on the admin_enabled setting), so all this has to
    do is load that stylesheet and give the watermark a home on <body>.
--}}
@php
    $adminOn = ((string) ($blueprint->dbGet('nyriel', 'admin_enabled', '1'))) === '1';
    $mark    = trim((string) $blueprint->dbGet('nyriel', 'admin_watermark', ''));
@endphp
@if($adminOn)
    <link rel="stylesheet" href="{{ route('nyriel.css') }}">
    <style>
        /* The watermark text comes from a setting, so it is read from an
           attribute rather than interpolated into CSS. */
        html body.adminlte[data-nyriel-mark]:not([data-nyriel-mark=""])::after {
            content: attr(data-nyriel-mark);
        }
    </style>
@endif
@if($mark !== '')
    <script>
        // Set on <body> before first paint so there is no flash of unbranded
        // corner. textContent, never innerHTML: the value is admin input.
        document.addEventListener('DOMContentLoaded', function () {
            document.body.setAttribute('data-nyriel-mark', @json($mark));
        });
    </script>
@endif
