'use strict';

const socket = require('./device-session');

// on page ready
window.addEventListener('load', function () {

	if (document.getElementById('session-status') !== null) {
		let statusEl = document.getElementById('session-status');

		socket.on('handshakeOK', () => {
			socket.on('account', status => {
				statusEl.innerHTML = JSON.stringify(status);
			});

			let statusHandler = setInterval(() => {
				socket.emit('account');
			}, 1000);

			socket.on('logout', () => {
				clearInterval(statusHandler);
			});
		});

	}

});

module.exports = socket;
