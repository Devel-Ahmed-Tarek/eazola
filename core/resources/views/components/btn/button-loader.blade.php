@props([
    'tag' => 'span',
    'tagEnd' => false,
    'icon' => 'mdi mdi-spin mdi-loading',
    'class' => ''
])

<{{$tag}} class="loading-icon {{$icon}} {{$class}}">@if(!$tagEnd)</{{$tag}}>@endif

<style>
    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    .loading-icon {
        font-size: inherit;
        animation: rotate 1s linear infinite;
    }
</style>
