<x-layouts.app title="Dashboard">
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center"> {{-- Added text-center for the welcome section --}}
            <h1 class="text-4xl font-extrabold leading-tight text-gray-900 dark:text-white mb-4"> {{-- Made larger --}}
                Welcome to Scrycaster!
            </h1>
            <p class="mt-4 text-xl text-gray-600 dark:text-gray-300"> {{-- Made larger --}}
                This is your central hub for joining and managing your tabletop roleplaying game sessions.
            </p>

            <div class="mt-8">
                <a href="{{ url('/admin') }}"
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition ease-in-out duration-150">
                    <svg class="-ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/20000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065zM12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                    </svg>
                    Go to Your DM Dashboard (Admin Panel)
                </a>
            </div>

            <p class="mt-8 text-lg text-gray-600 dark:text-gray-300">
                As a Dungeon Master, you'll manage your campaigns, characters, monsters, and more
                from the **DM Dashboard**. This section below focuses on the active player views.
            </p>
        </div>

        <div class="mt-12 border-t border-gray-200 dark:border-gray-700 pt-8"> {{-- Added border-t and pt-8 for separation --}}
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Your Active Encounters (Player View)</h2>
            <p class="mb-6 text-gray-600 dark:text-gray-300">
                Clicking "Go to Encounter" below will take you to the **player-facing view**
                of that specific encounter, perfect for sharing with your players or keeping track during gameplay.
            </p>

            @if($encounters->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($encounters as $encounter)
                        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                            <div class="px-4 py-5 sm:p-6 flex flex-col h-full"> {{-- Added flex-col h-full --}}
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    {{ $encounter->name }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Campaign: {{ $encounter->campaign ? $encounter->campaign->name : 'N/A' }}
                                </p>
                                {{-- Optionally add more encounter details here, e.g., current round, if available --}}
                                @if ($encounter->current_round)
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Current Round: {{ $encounter->current_round }}
                                    </p>
                                @endif

                                <div class="mt-4 flex-grow flex items-end"> {{-- Pushes button to bottom --}}
                                    <a href="{{ route('encounter.dashboard', ['encounter' => $encounter->id]) }}"
                                       class="inline-flex items-center justify-center w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600 transition ease-in-out duration-150">
                                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/20000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.127a.5.5 0 00-.707 0L9.439 15.636a.5.5 0 00.707.707l4.606-4.606a.5.5 0 000-.707z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v.01M12 8v.01M12 20v.01M20 12h.01M4 12h.01M18 18h.01M6 6h.01M18 6h.01M6 18h.01" />
                                        </svg>
                                        Go to Player View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                    <div class="px-4 py-5 sm:p-6 text-center">
                        <p class="text-lg text-gray-500 dark:text-gray-400">
                            You don't have any active encounters yet.
                        </p>
                        <p class="mt-2 text-md text-gray-500 dark:text-gray-400">
                            Start a new encounter from your <a href="{{ url('/admin') }}" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-500 dark:hover:text-indigo-600 font-medium underline">DM Dashboard</a>!
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>