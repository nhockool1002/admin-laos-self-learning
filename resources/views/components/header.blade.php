@php
    $pageTitle = ($title ?? 'Trang chủ') . ' | Học Tiếng Lào Admin Panel';
@endphp
@if (!isset($__header_rendered))
    @php($__header_rendered = true)
    <head>
        <title>{{ $pageTitle }}</title>
        <link rel="icon" type="image/png" href="/assets/imgs/laos.png">
    </head>
@endif
<nav class="w-full flex items-center justify-between bg-[#232946] bg-opacity-90 px-6 py-4 shadow-lg sticky top-0 z-30">
    <div class="flex items-center gap-4">
        {{ $left ?? '' }}
        <h2 class="text-2xl font-bold text-purple-200 whitespace-nowrap">{{ $title }}</h2>
    </div>
    <div>
        {{ $right ?? '' }}
    </div>
</nav> 