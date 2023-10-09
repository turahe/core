<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

    @if (config('core.favicon_enabled'))
        @include('favicon')
    @endif

    @if (($darkMode ?? true) === true)
        @include('theme-change')
    @endif

    <title>@yield('title')</title>

    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    @vite(['resources/js/app.js'])

    <script>
        @if (($darkMode ?? true) === true)
            updateTheme();
        @endif
        var config = {!! Js::from(array_merge($config, ['csrfToken' => csrf_token()])) !!};
        var lang = {!! Js::from($lang) !!};
    </script>

    @stack('head')

    <script>
        window.Innoclapps = {
            bootingCallbacks: [],
            booting: function(callback) {
                this.bootingCallbacks.push(callback)
            }
        }
    </script>
</head>

<body>
    <div id="app" v-cloak>
        {{ $slot }}

        <teleport to="body">
            <the-float-notifications></the-float-notifications>
        </teleport>
    </div>

    <script defer>
        function bootApplication() {
            window.Innoclapps = CreateApplication(config, Innoclapps.bootingCallbacks)
            Innoclapps.start();
        }
    </script>
</body>

</html>
