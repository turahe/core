@if ((Str::contains(config('app.url'), 'www') && !Str::contains(Request::url(), 'www')) || (Str::contains(Request::url(), 'www') && !Str::contains(config('app.url'), 'www')))
    <i-alert variant="danger" :rounded="{{ !Auth::check() ? 'true' : 'false' }}">
        You must <a href="{{ config('app.url') }}"
            class="text-danger-800 hover:text-danger-600 font-semibold">access</a> the installation URL
        {{ Str::contains(config('app.url'), 'www') && !Str::contains(Request::url(), 'www') ? 'with' : 'without' }}
        <b>www</b>.
    </i-alert>
@endif
