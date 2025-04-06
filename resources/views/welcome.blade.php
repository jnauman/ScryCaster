<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Update Title --}}
    <title>ScryCaster</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    {{-- Add Livewire styles for consistency if used elsewhere --}}
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Remove the large inline <style> block from the original file --}}

</head>
{{-- Keep body classes for centering and background --}}
<body class="bg-gray-100 dark:bg-zinc-900 text-gray-900 dark:text-gray-100 flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col antialiased">
{{-- Keep header for Login/Register/Dashboard links --}}
<header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
    @if (Route::has('login'))
        <nav class="flex items-center justify-end gap-4">
            @auth
                <a
                    href="{{ url('/dashboard') }}"
                    class="inline-block px-5 py-1.5 dark:text-gray-300 border-gray-300 hover:border-gray-400 border text-gray-700 dark:border-gray-600 dark:hover:border-gray-500 rounded-sm text-sm leading-normal"
                >
                    Dashboard
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="inline-block px-5 py-1.5 dark:text-gray-300 text-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 rounded-sm text-sm leading-normal"
                >
                    Log in
                </a>

                @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-block px-5 py-1.5 dark:text-gray-300 border-gray-300 hover:border-gray-400 border text-gray-700 dark:border-gray-600 dark:hover:border-gray-500 rounded-sm text-sm leading-normal">
                        Register
                    </a>
                @endif
            @endauth
        </nav>
    @endif
</header>

{{-- Main Content Area --}}
<div class="flex items-center justify-center w-full lg:grow">
    <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row shadow-lg rounded-lg overflow-hidden">

        {{-- Left Panel: Text Content --}}
        <div class="flex-1 p-6 pb-12 lg:p-12 xl:p-20 bg-white dark:bg-gray-800">

            {{-- ScryCaster Logo Placeholder --}}
            <div class="mb-6">
                {{-- Replace with your actual logo --}}
                <img src="/images/logo_03.jpeg" alt="ScryCaster Logo" class="h-12 w-auto">
                {{-- Example with SVG: <svg ...> your logo code </svg> --}}
            </div>

            {{-- Headline --}}
            <h1 class="text-2xl lg:text-3xl font-medium mb-3 text-gray-900 dark:text-white">
                ScryCaster: Initiative and Visuals, Simplified.
            </h1>

            {{-- Description --}}
            <p class="mb-6 text-gray-600 dark:text-gray-300 leading-relaxed">
                ScryCaster helps Game Masters run smoother, more engaging TTRPG encounters. Track initiative, manage combatants, and display maps or monster art directly to your players in real-time.
            </p>

            {{-- Call to Action Buttons (Optional, duplicates header) --}}
            {{-- You could add more prominent buttons here if desired --}}
            {{--
			<div class="flex gap-3 text-sm leading-normal">
				<a href="{{ route('register') }}" class="inline-block px-5 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-white font-medium">
					Get Started
				</a>
				<a href="{{ route('login') }}" class="inline-block px-5 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-gray-800 dark:text-gray-200 font-medium">
					Log In
				</a>
			</div>
			--}}

            {{-- Removed original Laravel ecosystem links --}}

        </div>

        {{-- Right Panel: Image --}}
        {{-- Keep structural classes, replace content --}}
        {{--<div class="bg-gray-200 dark:bg-gray-700 relative lg:-ml-px -mb-px lg:mb-0 aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
            --}}{{-- Replace with your actual hero image/screenshot --}}{{--
            <img src="/images/logo_01.jpeg" alt="ScryCaster Feature Preview" class="absolute inset-0 w-full h-full object-cover">
            --}}{{-- Optional overlay for contrast --}}{{--
            --}}{{-- <div class="absolute inset-0 bg-black opacity-10"></div> --}}{{--
        </div>--}}
    </main>
</div>

{{-- Add Livewire scripts for consistency --}}
@livewireScripts
</body>
</html>