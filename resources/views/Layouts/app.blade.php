<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | DNSC Findr</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite('resources/js/app.js')
    @vite('resources/css/app.css')

    <!-- BOOTSTRAP CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        html body {
            margin: 0;
            background: #f9f9f9;
            font-family: "Montserrat", sans-serif;
            color: #333;
        }

        #changePasswordModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            pointer-events: auto;
        }
    </style>

</head>

<body>

    @yield('content')

    <!-- MODAL OUTSIDE VUE -->

    <div>
        @auth
            @if (auth()->user()->hasTempPassword() && !session('temp_password_skipped'))
                @include('Components.change-password-modal')
            @endif
        @endauth
    </div>

    <!-- Botpress Webchat -->
<script src="https://cdn.botpress.cloud/webchat/v3.4/inject.js"></script>
<script src="https://files.bpcontent.cloud/2025/12/02/10/20251202105928-41UXDK2B.js" defer></script>

    
</body>

</html>
