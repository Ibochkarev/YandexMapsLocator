import { buildBalloonProperties, resolveMarkerOptions } from './balloon.js';

export class MarkersModule {
    /**
     * @param {object} map
     * @param {import('./events.js').EventEmitter} events
     * @param {object} config
     */
    constructor(map, events, config) {
        this.map = map;
        this.events = events;
        this.config = config;
        this.i18n = config.i18n || {};
        this.collection = null;
        this.placemarks = new Map();
        this.ymaps = window.ymaps;
    }

    render(stores) {
        this.clear();
        const items = [];

        stores.forEach((store) => {
            if (store.latitude == null || store.longitude == null) {
                return;
            }

            const properties = buildBalloonProperties(store, this.i18n, this.config);
            this.events.emit('balloon:build', { store, properties, config: this.config });

            const markerDetail = {
                store,
                options: resolveMarkerOptions(store, this.config),
                config: this.config,
            };
            this.events.emit('marker:options', markerDetail);

            const placemark = new this.ymaps.Placemark(
                [store.latitude, store.longitude],
                properties,
                markerDetail.options,
            );
            placemark.events.add('click', () => {
                this.events.emit('marker:click', { id: store.id, store });
            });
            this.placemarks.set(store.id, placemark);
            items.push(placemark);
        });

        if (this.config.cluster && this.ymaps.Clusterer) {
            this.collection = new this.ymaps.Clusterer({
                preset: 'islands#invertedRedClusterIcons',
                clusterBalloonMaxWidth: 360,
                clusterBalloonMinWidth: 220,
            });
            this.collection.add(items);
            this.map.geoObjects.add(this.collection);
        } else {
            items.forEach((item) => this.map.geoObjects.add(item));
            this.collection = items;
        }
    }

    /**
     * Open placemark balloon. Safe when map has size and when marker is inside a cluster.
     * @param {number|string} id
     * @returns {Promise<void>}
     */
    async open(id) {
        const placemark = this.placemarks.get(id)
            ?? this.placemarks.get(Number(id))
            ?? this.placemarks.get(String(id));
        if (!placemark || !this.map) {
            return;
        }

        try {
            this.map.container.fitToViewport();
        } catch {
            // ignore
        }

        const [width = 0, height = 0] = this.map.container.getSize?.() ?? [];
        if (width < 1 || height < 1) {
            return;
        }

        try {
            const clusterState = this.collection?.getObjectState?.(placemark);
            if (clusterState?.isClustered && clusterState.cluster) {
                clusterState.cluster.state.set('activeObject', placemark);
                await this.collection.balloon.open(clusterState.cluster);
                return;
            }
            await placemark.balloon.open();
        } catch (error) {
            this.events.emit('error', {
                source: 'markers',
                message: error?.message || String(error),
            });
        }
    }

    clear() {
        this.map.geoObjects.removeAll();
        this.placemarks.clear();
        this.collection = null;
    }
}
