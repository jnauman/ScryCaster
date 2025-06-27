<x-layouts.app title="Dashboard">
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold leading-tight text-gray-900 dark:text-white">
                Welcome to Scrycaster!
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                This is your central hub for managing your tabletop roleplaying game sessions.
                Below you'll find a list of your active encounters. You can jump directly into any encounter
                to manage combat, track initiative, and keep the game flowing smoothly.
            </p>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">
                Use the navigation menu to manage your campaigns, characters, and monsters.
            </p>
        </div>

        <div class="mt-10">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Your Encounters</h2>
            @if($encounters->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($encounters as $encounter)
                        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    {{ $encounter->name }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Campaign: {{ $encounter->campaign ? $encounter->campaign->name : 'N/A' }}
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('encounter.dashboard', ['encounter' => $encounter->id]) }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                                        Go to Encounter
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <p class="text-center text-gray-500 dark:text-gray-400">
                            You don't have any encounters yet.
                        </p>
                        <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                            Create a campaign and add encounters, or join a campaign as a player character.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
