@props([
    'title' => '',
    'tag' => 'h2',
])

<{{ $tag }} class="flex mb-5 space-x-4">
    <div class="text-xl font-semibold min-w-fit">{{ $title }}</div>
    <hr class="w-full h-px my-auto bg-gray-200 border-0 dark:bg-gray-700">
</{{ $tag }}>