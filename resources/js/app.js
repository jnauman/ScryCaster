import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { createApp, ref, onMounted } from 'vue';

document.addEventListener('DOMContentLoaded', () => {
	window.Pusher = Pusher;

	window.Echo = new Echo({
		broadcaster: 'pusher',
		key: import.meta.env.VITE_PUSHER_APP_KEY,
		cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
		forceTLS: true
	});

// Enable Pusher logging for debugging
	window.Pusher.logToConsole = true;

	const app = createApp({
		setup()
		{
			const message = ref(''); // Reactive variable for the message

			onMounted(() => {
				console.log('Vue component mounted, setting up listener.sgddgs');
				console.log(window.encounterId);
				window.Echo.channel('encounter.' + window.encounterId).listen('.TurnChanged', (event) => {
					console.log(window.encounterId);
					const currentTurn = event.currentTurn;
					const currentRound = event.currentRound;

					// Update Round Display
					document.querySelector('.text-lg.mb-2').textContent = `Round: ${currentRound}`;

					// Toggle Classes
					const listItems = document.querySelectorAll('#encounter-' + window.encounterId + '-combatants li');

					listItems.forEach(li => {
						const order = parseInt(li.dataset.order);
						const isMonster = li.classList.contains('monster-not-turn') || li.classList.contains('monster-current-turn');

						if (order === event.currentTurn) {
							if (isMonster) {
								li.classList.remove('monster-not-turn');
								li.classList.add('monster-current-turn');
							} else {
								li.classList.remove('player-not-turn');
								li.classList.add('player-current-turn');
							}
						} else {
							if (isMonster) {
								li.classList.remove('monster-current-turn');
								li.classList.add('monster-not-turn');
							} else {
								li.classList.remove('player-current-turn');
								li.classList.add('player-not-turn');
							}
						}
					});

					console.log('Player Turn Updated:', currentTurn, currentRound);
				});
			});

			return {
				message, // Expose the message variable to the template
			};
		},
		template: `
          <!--        <div>
					  <h1>Pusher Test</h1>
					  <p>Received Message: {{ message }}</p>
				  </div>-->
		`
	});

	app.mount('#app'); // Mount the Vue component to the #app element

});

