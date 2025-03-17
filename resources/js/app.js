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
window.Echo.channel('encounter').listen('TurnChanged', (event) => {
	console.log('Basic listener: TurnChanged event received:', event);
});
const app = createApp({
	setup() {
		const currentTurn = ref(window.initialCurrentTurn); // Initialize currentTurn
		const encounterId = ref(window.encounterId); // Initialize encounterId

		onMounted(() => {
			console.log('Echo is Ready!');
			console.log("Encounter ID:", encounterId.value); // Add this line

			window.Echo.channel('encounter')
				.listen('TurnChanged', (event) => {
					console.log('Current Turn Updated:');
					// Parse the JSON string
					const parsedData = JSON.parse(event.data);
					currentTurn.value = parsedData.currentTurn;
					console.log('Current Turn Updated:', currentTurn.value);
				});
		});

		return {
			currentTurn,
			encounterId // Expose encounterId if needed in the template
		};
	},
	template: `
        <div>
            <h1>Testing Vue</h1>
            <p>Current Turn: {{ currentTurn }}</p>
        </div>
    `
});

app.mount('#app'); // Mount to a specific ID for testing