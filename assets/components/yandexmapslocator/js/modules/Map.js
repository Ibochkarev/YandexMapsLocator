import { MarkersModule } from './Markers.js';

export class MapModule {
    /**
     * @param {HTMLElement} container
     * @param {object} config
     * @param {import('./events.js').EventEmitter} events
     */
    constructor(container, config, events) {
        this.container = container;
        this.config = config;
        this.events = events;
        this.map = null;
        this.markers = null;
        this.ready = false;
        /** @type {Promise<void>|null} */
        this.initPromise = null;
    }

    async init(stores) {
        if (!this.container) {
            throw new Error('Map container not found');
        }

        this.initPromise = this.doInit(stores);
        await this.initPromise;
    }

    async doInit(stores) {
        await this.loadYmaps();
        const center = this.config.center || { latitude: 55.751244, longitude: 37.618423 };
        this.map = new window.ymaps.Map(this.container, {
            center: [center.latitude, center.longitude],
            zoom: this.config.zoom || 10,
            controls: ['zoomControl', 'geolocationControl'],
        });
        this.markers = new MarkersModule(this.map, this.events, {
            cluster: this.config.cluster !== false,
            markerOptions: this.config.markerOptions || { preset: 'islands#redDotIcon' },
            markerIconSize: this.config.markerIconSize || [32, 32],
            balloon: this.config.balloon || {},
            i18n: this.config.i18n || {},
        });
        this.markers.render(stores);
        this.fitStores(stores);
        this.refreshSize();
        this.ready = true;
    }

    /**
     * @param {Array<object>} stores
     */
    fitStores(stores) {
        if (!this.map || stores.length === 0) {
            return;
        }

        if (stores.length === 1) {
            const store = stores[0];
            this.setCenter(store.latitude, store.longitude, Math.max(12, this.config.zoom || 10));
            return;
        }

        const points = stores.map((store) => [store.latitude, store.longitude]);
        this.map.setBounds(window.ymaps.util.bounds.fromPoints(points), {
            checkZoomRange: true,
            zoomMargin: 40,
        });
    }

    /**
     * Recalculate map size after container becomes visible (e.g. mobile list/map toggle).
     */
    refreshSize() {
        if (!this.map) {
            return;
        }
        try {
            this.map.container.fitToViewport();
        } catch {
            // ignore
        }
    }

    /**
     * @returns {Promise<void>}
     */
    whenReady() {
        return this.ready ? Promise.resolve() : (this.initPromise ?? Promise.resolve());
    }

    loadYmaps() {
        if (window.ymaps?.Map) {
            return window.ymaps.ready();
        }

        if (window.__ymlYmapsLoading) {
            return window.__ymlYmapsLoading;
        }

        const apiKey = this.config.apiKey || '';
        const src = `https://api-maps.yandex.ru/2.1/?lang=ru_RU${apiKey ? `&apikey=${encodeURIComponent(apiKey)}` : ''}`;

        window.__ymlYmapsLoading = new Promise((resolve, reject) => {
            const existing = document.querySelector('script[data-yml-ymaps]');
            if (existing) {
                existing.addEventListener('load', () => window.ymaps.ready(resolve));
                existing.addEventListener('error', () => reject(new Error('Failed to load Yandex Maps')));
                if (window.ymaps?.ready) {
                    window.ymaps.ready(resolve);
                }
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.dataset.ymlYmaps = '1';
            script.onload = () => window.ymaps.ready(resolve);
            script.onerror = () => {
                window.__ymlYmapsLoading = null;
                reject(new Error('Failed to load Yandex Maps'));
            };
            document.head.appendChild(script);
        });

        return window.__ymlYmapsLoading;
    }

    setCenter(latitude, longitude, zoom) {
        this.map?.setCenter([latitude, longitude], zoom ?? this.map.getZoom(), { duration: 300 });
    }

    /**
     * @param {number|string} id
     * @param {Array<object>} stores
     * @returns {Promise<void>}
     */
    async showStore(id, stores) {
        await this.whenReady();

        const store = stores.find((item) => item.id == id);
        if (!store || !this.map) {
            return;
        }

        const zoom = 14;
        const coords = [store.latitude, store.longitude];
        const panOptions = { flying: false, duration: 300, checkZoomRange: true, zoomMargin: 40 };

        this.refreshSize();

        try {
            if (typeof this.map.panTo === 'function') {
                await this.map.panTo(coords, panOptions);
                if (this.map.getZoom() < zoom) {
                    await this.map.setZoom(zoom, { duration: 200 });
                }
            } else {
                this.setCenter(store.latitude, store.longitude, zoom);
                await new Promise((resolve) => setTimeout(resolve, 320));
            }
        } catch {
            this.setCenter(store.latitude, store.longitude, zoom);
            await new Promise((resolve) => setTimeout(resolve, 50));
        }

        this.refreshSize();
        await this.markers?.open(id);
    }

    updateStores(stores) {
        this.markers?.render(stores);
        this.fitStores(stores);
        this.refreshSize();
    }
}
