import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { createApp, ref, onMounted } from 'vue';

window.Pusher = Pusher;

window.Echo = new Echo({
	broadcaster: 'pusher',
	key: import.meta.env.VITE_PUSHER_APP_KEY,
	cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
	forceTLS: true
});

// Enable Pusher logging for debugging
//window.Pusher.logToConsole = true;

const app = createApp({
	setup() {
		const message = ref(''); // Reactive variable for the message

		onMounted(() => {
			//console.log('Vue component mounted, setting up listener.');

			window.Echo.channel('encounter').listen('.TurnChanged', (event) => {
				const currentTurn = event.currentTurn;
				const currentRound = event.currentRound;

				// Update Round Display
				document.querySelector('.text-lg.mb-2').textContent = `Round: ${currentRound}`;

				// Toggle Classes
				const listItems = document.querySelectorAll('#encounter-' + window.encounterId + ' li');
				listItems.forEach(li => {
					const order = parseInt(li.dataset.order);
					if (order === currentTurn) {
						li.classList.add('bg-[var(--color-accent)]', 'border', 'border-[var(--color-accent-foreground)]', 'text-[var(--color-accent-foreground)]');
						li.classList.remove('bg-[var(--color-accent-content)]');
					} else {
						li.classList.remove('bg-[var(--color-accent)]', 'border', 'border-[var(--color-accent-foreground)]', 'text-[var(--color-accent-foreground)]');
						li.classList.add('bg-[var(--color-accent-content)]');
					}
				});

				//console.log('Player Turn Updated:', currentTurn, currentRound);
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

