<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel Analytics</title>
    {{-- Replaced Tailwind CDN with inline styles for production --}}
    <style>
        *, ::before, ::after { box-sizing: border-box; border-width: 0; border-style: solid; }
        html { line-height: 1.5; -webkit-text-size-adjust: 100%; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { margin: 0; line-height: inherit; }
        .min-h-screen { min-height: 100vh; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-white { background-color: #fff; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-700 { color: #374151; }
        .text-gray-900 { color: #111827; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-2xl { font-size: 1.5rem; line-height: 2rem; }
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .p-4 { padding: 1rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-4 { margin-bottom: 1rem; }
        .ml-1 { margin-left: 0.25rem; }
        .mr-2 { margin-right: 0.5rem; }
        .flex { display: flex; }
        .inline-flex { display: inline-flex; }
        .grid { display: grid; }
        .hidden { display: none; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-end { justify-content: flex-end; }
        .justify-between { justify-content: space-between; }
        .gap-4 { gap: 1rem; }
        .w-full { width: 100%; }
        .h-5 { height: 1.25rem; }
        .w-5 { width: 1.25rem; }
        .rounded { border-radius: 0.25rem; }
        .rounded-md { border-radius: 0.375rem; }
        .rounded-lg { border-radius: 0.5rem; }
        .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .border { border-width: 1px; }
        .border-gray-300 { border-color: #d1d5db; }
        .divide-y > :not([hidden]) ~ :not([hidden]) { border-top-width: 1px; border-color: #e5e7eb; }
        .overflow-hidden { overflow: hidden; }
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .right-0 { right: 0; }
        .z-10 { z-index: 10; }
        .origin-top-right { transform-origin: top right; }
        .ring-1 { box-shadow: 0 0 0 1px rgba(0,0,0,0.05); }
        .focus\:outline-none:focus { outline: none; }
        @media (min-width: 640px) {
            .sm\:py-16 { padding-top: 4rem; padding-bottom: 4rem; }
            .sm\:max-w-5xl { max-width: 64rem; }
            .sm\:mx-auto { margin-left: auto; margin-right: auto; }
            .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sm\:p-6 { padding: 1.5rem; }
            .sm\:text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        }
        @media (min-width: 1024px) {
            .lg\:px-0 { padding-left: 0; padding-right: 0; }
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-gray-100 text-gray-500 py-6 flex flex-col sm:py-16">
        <div class="px-4 w-full lg:px-0 sm:max-w-5xl sm:mx-auto">
            <div class="flex justify-end">
                @include('analytics::data.filter')
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @each('analytics::stats.card', $stats, 'stat')
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @include('analytics::data.pages-card')
                @include('analytics::data.sources-card')
                @include('analytics::data.users-card')
                @include('analytics::data.devices-card')
                @each('analytics::data.utm-card', $utm, 'data')
            </div>
        </div>
    </div>

    <script>
        const filterButton = document.getElementById('filter-button');
        const filterDropdown = document.getElementById('filter-dropdown');

        filterButton.addEventListener('click', function (e) {
            e.preventDefault();

            filterDropdown.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            if (!filterButton.contains(e.target) && !filterDropdown.contains(e.target)) {
                filterDropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>
