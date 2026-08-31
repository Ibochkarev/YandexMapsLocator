export class GeolocationModule {
    /**
     * @param {import('./events.js').EventEmitter} events
     * @param {Record<string, string>} [i18n]
     */
    constructor(events, i18n = {}) {
        this.events = events;
        this.i18n = i18n;
    }

    locate() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                const message = this.i18n.errGeolocationUnsupported || 'Geolocation is not supported';
                this.events.emit('error', { source: 'geolocation', message });
                reject(new Error(message));
                return;
            }

            this.events.emit('geolocation:start');

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const coords = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    };
                    this.events.emit('geolocation:complete', coords);
                    resolve(coords);
                },
                (error) => {
                    const message = this.mapError(error);
                    this.events.emit('error', {
                        source: 'geolocation',
                        message,
                        code: error.code,
                    });
                    reject(new Error(message));
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 },
            );
        });
    }

    mapError(error) {
        switch (error.code) {
            case error.PERMISSION_DENIED:
                return this.i18n.errGeolocationDenied || 'Permission denied';
            case error.POSITION_UNAVAILABLE:
                return this.i18n.errGeolocationUnavailable || 'Position unavailable';
            case error.TIMEOUT:
                return this.i18n.errGeolocationTimeout || 'Timeout';
            default:
                return this.i18n.errGeolocationFailed || 'Geolocation failed';
        }
    }
}
