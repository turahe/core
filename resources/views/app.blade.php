<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

    @if (config('core.favicon_enabled'))
        @include('favicon')
    @endif

    @include('theme-change')

    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    @vite(['resources/js/app.js', 'resources/css/contentbuilder/theme.css'])

    <script>
        updateTheme();

        var config = {!! Js::from(array_merge($config, ['csrfToken' => csrf_token()])) !!};
        var lang = {!! Js::from($lang) !!};
    </script>

    <!-- Add all of the custom registered styles -->
    @foreach (\Modules\Core\Facades\Innoclapps::styles() as $name => $path)
        @if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']))
            <link rel="stylesheet" href="{!! $path !!}">
        @else
            <link rel="stylesheet" href="{{ url("styles/$name") }}">
        @endif
    @endforeach

    <script>
        window.Innoclapps = {
            bootingCallbacks: [],
            booting: function(callback) {
                this.bootingCallbacks.push(callback)
            }
        }
    </script>

    {{-- Head Flag --}}
</head>

<body>
    <div class="flex h-screen overflow-hidden bg-neutral-100 dark:bg-neutral-800" id="app" v-cloak>
        <the-sidebar></the-sidebar>

        <div class="flex w-0 flex-1 flex-col overflow-hidden">

            @include('core::warnings.dashboard')

            <the-navbar></the-navbar>

            {{-- Navbar End Flag --}}

            @if ($alert = get_current_alert())
                <i-alert variant="{{ $alert['variant'] }}" dismissible>
                    {{ $alert['message'] }}
                </i-alert>
            @endif

            @if (auth()->user()->can('use voip') && config('core.voip.client') !== null)
                <call-component></call-component>
            @endif

            <router-view></router-view>

            <i-confirmation-dialog v-if="confirmationDialog && !confirmationDialog.injectedInDialog"
                :dialog="confirmationDialog">
            </i-confirmation-dialog>

            <teleport to="body">
                <the-float-notifications></the-float-notifications>
            </teleport>
        </div>
    </div>

    <script src="{{ asset('assets/tinymce/tinymce.min.js?v=' . \Modules\Core\Application::VERSION) }}"></script>

    <!-- Add all of the custom registered scripts -->
    @foreach (\Modules\Core\Facades\Innoclapps::scripts() as $name => $path)
        @if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']))
            <script src="{!! $path !!}"></script>
        @else
            <script src="{{ url("scripts/$name") }}"></script>
        @endif
    @endforeach

    <script defer>
        function bootApplication() {
            window.Innoclapps = CreateApplication(config, Innoclapps.bootingCallbacks)
            Innoclapps.start();
        }
    </script>
</body>

</html>
