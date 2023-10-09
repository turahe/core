<x-core::layouts.skin>
    @section('title', __('core::app.file_preview'))

    @push('head')
        <meta name="robots" content="noindex">
    @endpush

    <div class="h-screen min-h-screen bg-neutral-50 dark:bg-neutral-800">
        <div class="w-full border-b border-neutral-200 bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-900">
            <div class="m-auto max-w-6xl">
                <div class="flex items-center p-4">
                    <div class="grow">
                        <h5 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">
                            {{ __('core::app.file_preview') }}
                        </h5>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i-button-copy icon="Share" class="mr-4" text="{{ $media->getViewUrl() }}"
                            success-message="{{ __('core::media.link_copied') }}" v-i-tooltip.bottom="'{{ __('core::app.copy') }}'">
                        </i-button-copy>
                        <a href="{{ $media->getDownloadUrl() }}" class="btn btn-primary btn-sm rounded-md">
                            {{ __('core::app.download') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="m-auto max-w-6xl">
            <div class="flex w-full flex-col p-4">
                @if ($media->aggregate_type === 'image')
                    <img src="{{ $media->previewPath() }}" class="mx-auto rounded" alt="{{ $media->basename }}">
                @elseif($media->aggregate_type === 'pdf')
                    <div class="aspect-w-16 aspect-h-9">
                        <iframe src="{{ $media->previewPath() }}" name="{{ $media->filename }}" allowfullscreen>
                        </iframe>
                    </div>
                @elseif($media->mime_type === 'text/plain')
                    <div class="whitespace-normal text-left">
                        {{ $media->contents() }}
                    </div>
                @elseif($media->aggregate_type === 'video' && $media->isHtml5SupportedVideo())
                    <div class="aspect-w-16 aspect-h-9">
                        <video autoplay controls>
                            <source src="{{ $media->previewPath() }}" type="{{ $media->mime_type }}">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @elseif($media->aggregate_type === 'audio' && $media->isHtml5SupportedAudio())
                    <audio autoplay controls>
                        <source src="{{ $media->previewPath() }}" type="{{ $media->mime_type }}">
                        Your browser does not support the audio tag.
                    </audio>
                @else
                    <p class="text-center text-neutral-600 dark:text-neutral-200">
                        {{ __('core::media.no_preview_available') }}
                    </p>
                @endif
                <div class="mt-5 text-center">
                    <a href="{{ $media->getDownloadUrl() }}" class="link">{{ $media->basename }}</a>
                </div>
            </div>
        </div>
    </div>
</x-core::layouts.skin>
