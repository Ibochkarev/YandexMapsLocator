export class SearchModule {
    /**
     * @param {import('./events.js').EventEmitter} events
     * @param {object} options
     * @param {string} [options.searchUrl]
     * @param {string} [options.apiUrl]
     * @param {boolean} [options.restApi]
     * @param {Record<string, string>} [options.i18n]
     */
    constructor(events, options = {}) {
        this.events = events;
        this.i18n = options.i18n || {};
        this.restApi = options.restApi === true;
        this.searchUrl = (options.searchUrl || options.apiUrl || '').replace(/\/$/, '');
        this.apiUrl = (options.apiUrl || '').replace(/\/$/, '');
        this.context = typeof options.context === 'string' ? options.context : '';
    }

    headers() {
        // The on-page locator never holds a Bearer token: secrets must not leak into HTML.
        // REST with Bearer is for server-side clients (Nuxt BFF, custom backend).
        return { Accept: 'application/json' };
    }

    /**
     * @param {Record<string, unknown>} params
     * @returns {Promise<{success: boolean, results: object[], meta?: object}>}
     */
    async search(params = {}) {
        this.events.emit('search:start', params);

        const query = new URLSearchParams();
        const mapped = this.mapParams(params);
        Object.entries(mapped).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                query.set(key, String(value));
            }
        });

        let url;
        if (this.restApi) {
            query.set('route', 'api/v1/locations');
            if (!query.has('fields')) {
                query.set(
                    'fields',
                    'id,title,address,latitude,longitude,coordinates,phone,email,working_hours,working_hours_formatted,working_hours_compact,url,category,distance,distance_formatted,balloon_image,marker_icon,is_open_now,closes_at,next_open_at,status_hint',
                );
            }
            url = `${this.apiUrl}?${query.toString()}`;
        } else {
            url = `${this.searchUrl}?${query.toString()}`;
        }

        const response = await fetch(url, {
            headers: this.headers(),
            credentials: 'same-origin',
        });

        let payload;
        try {
            payload = await response.json();
        } catch {
            const message = this.i18n.errInvalidResponse || 'Invalid API response';
            this.events.emit('error', { source: 'search', message });
            throw new Error(message);
        }

        if (!response.ok || !payload?.success) {
            let message = payload?.error || `${this.i18n.errSearchFailed || 'Search failed'} (${response.status})`;
            if (response.status === 403 && payload?.code === 'pro_required') {
                message = this.i18n.errProRequired || message;
            }
            if (response.status === 429) {
                const retryAfter = response.headers.get('Retry-After');
                message = retryAfter
                    ? `${this.i18n.errRateLimit || 'Rate limit exceeded'}. ${retryAfter}s`
                    : this.i18n.errRateLimit || 'Rate limit exceeded. Retry in about 60s';
            }
            this.events.emit('error', { source: 'search', message, status: response.status });
            throw new Error(message);
        }

        const results = (Array.isArray(payload.data) ? payload.data : []).map((row) => this.normalizeStore(row));
        const data = { success: true, results, meta: payload.meta || null };
        this.events.emit('search:complete', data);
        return data;
    }

    /**
     * @param {Record<string, unknown>} params
     * @returns {Record<string, unknown>}
     */
    mapParams(params) {
        const out = { ...params };
        if (out.lat == null && out.latitude != null) {
            out.lat = out.latitude;
        }
        if (out.lng == null && out.longitude != null) {
            out.lng = out.longitude;
        }
        delete out.latitude;
        delete out.longitude;
        if (out.context == null && this.context) {
            out.context = this.context;
        }
        delete out.ctx;
        return out;
    }

    /**
     * @param {Record<string, unknown>} row
     * @returns {Record<string, unknown>}
     */
    normalizeStore(row) {
        const coords = row.coordinates && typeof row.coordinates === 'object' ? row.coordinates : null;
        const latitude = row.latitude ?? coords?.lat ?? null;
        const longitude = row.longitude ?? coords?.lon ?? coords?.lng ?? null;

        return {
            id: row.id,
            pagetitle: row.pagetitle ?? row.title ?? '',
            longtitle: row.longtitle ?? '',
            description: row.description ?? '',
            url: row.url ?? '',
            address: row.address ?? '',
            latitude,
            longitude,
            phone: row.phone ?? '',
            email: row.email ?? '',
            working_hours: row.working_hours ?? '',
            working_hours_formatted: row.working_hours_formatted ?? '',
            working_hours_compact: row.working_hours_compact ?? '',
            category: row.category ?? '',
            context_key: row.context_key ?? '',
            distance: row.distance ?? null,
            distance_formatted: row.distance_formatted ?? '',
            idx: row.idx ?? 0,
            balloon_image: row.balloon_image ?? '',
            marker_icon: row.marker_icon ?? '',
            is_open_now: typeof row.is_open_now === 'boolean' ? row.is_open_now : undefined,
            closes_at: row.closes_at ?? undefined,
            next_open_at: row.next_open_at ?? undefined,
            status_hint: row.status_hint ?? undefined,
        };
    }
}
