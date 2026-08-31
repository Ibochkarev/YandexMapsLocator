import { EventEmitter } from './modules/events.js';
import { MapModule } from './modules/Map.js';
import { SearchModule } from './modules/Search.js';
import { GeolocationModule } from './modules/Geolocation.js';
import { StoreListModule } from './modules/StoreList.js';
import { SELECTORS } from './modules/dom.js';

const DESKTOP_MQ = '(min-width: 48.0625rem)';

export class YandexMapsLocator extends EventEmitter {
    /**
     * @param {string|HTMLElement} selector
     * @param {object} options
     */
    constructor(selector, options = {}) {
        super();
        this.root = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!this.root) {
            throw new Error('Locator root not found');
        }

        this.config = options.config || this.readJson(SELECTORS.config) || {};
        this.i18n = this.config.i18n || {};
        this.stores = options.stores || this.readJson(SELECTORS.stores) || [];
        this.apiUrl = options.apiUrl || this.config.apiUrl || '';
        this.errorEl = this.root.querySelector(SELECTORS.error);
        this.searchForm = this.root.querySelector(SELECTORS.search);
        this.submitButton = this.root.querySelector(SELECTORS.submit);
        this.locateButton = this.root.querySelector(SELECTORS.locate);
        this.busy = false;
        this.locationActive = false;

        this.searchModule = new SearchModule(this, {
            searchUrl: this.config.searchUrl || this.apiUrl,
            apiUrl: this.apiUrl,
            restApi: this.config.restApi === true,
            apiToken: this.config.apiToken || '',
            context: this.config.context || '',
            i18n: this.i18n,
        });
        this.geolocationModule = new GeolocationModule(this, this.i18n);
        this.listModule = new StoreListModule(this.root, this, this.i18n, {
            assetsUrl: this.config.assetsUrl || '',
        });
        this.mapModule = new MapModule(this.root.querySelector(SELECTORS.map), this.config, this);

        this.bindUi();
        this.syncEmptyState(this.stores.length === 0);
        this.setView(this.root.dataset.ymlView || 'list');
        this.on('error', ({ message }) => this.showError(message));
        this.loadFrontendModules()
            .then(() => this.mapModule.init(this.stores))
            .catch((error) => this.emit('error', { source: 'map', message: error.message }));
        this.syncEvents();
    }

    async loadFrontendModules() {
        const modules = Array.isArray(this.config.frontendModules) ? this.config.frontendModules : [];
        const assetsBase = (this.config.assetsUrl || '').replace(/\/$/, '');
        const imports = modules.map(async (entry) => {
            const src = typeof entry === 'string' ? entry : entry?.src;
            if (!src) {
                return;
            }
            // Absolute http(s) or site-root paths resolve as-is; relative paths join Free assetsUrl.
            const url = /^https?:\/\//i.test(src) || src.startsWith('/')
                ? src
                : `${assetsBase}/${src.replace(/^\//, '')}`;
            try {
                const mod = await import(/* @vite-ignore */ url);
                if (typeof mod.install === 'function') {
                    await mod.install(this);
                }
            } catch (error) {
                this.emit('error', { source: 'module', message: error.message, module: src });
            }
        });
        await Promise.all(imports);
    }

    readJson(selector) {
        const node = this.root.querySelector(selector);
        if (!node) {
            return null;
        }
        try {
            return JSON.parse(node.textContent || 'null');
        } catch {
            return null;
        }
    }

    bindUi() {
        this.searchForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = this.searchForm.querySelector('input[name="address"]');
            const address = input?.value?.trim() || '';
            if (!address) {
                this.showError(this.i18n.errEmptyAddress || 'Address is required.');
                input?.focus();
                return;
            }
            try {
                await this.search({ address });
                this.setLocationActive(false);
            } catch {
                // surfaced via error event
            }
        });

        this.locateButton?.addEventListener('click', async () => {
            try {
                if (this.locationActive) {
                    await this.clearLocation();
                    return;
                }
                await this.locate();
            } catch {
                // surfaced via error event
            }
        });

        const tabs = [...this.root.querySelectorAll(SELECTORS.viewTab)];
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                this.setView(tab.dataset.ymlViewTab || 'list');
            });
            tab.addEventListener('keydown', (event) => {
                this.handleTabKeydown(event, tabs, index);
            });
        });
        this._mediaQuery = window.matchMedia(DESKTOP_MQ);
        this._onBreakpointChange = () => {
            this.setView(this.root.dataset.ymlView || 'list');
        };
        this._mediaQuery.addEventListener('change', this._onBreakpointChange);
    }

    /**
     * @param {KeyboardEvent} event
     * @param {HTMLElement[]} tabs
     * @param {number} index
     */
    handleTabKeydown(event, tabs, index) {
        let nextIndex = index;
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
            nextIndex = (index + 1) % tabs.length;
        } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
            nextIndex = (index - 1 + tabs.length) % tabs.length;
        } else if (event.key === 'Home') {
            nextIndex = 0;
        } else if (event.key === 'End') {
            nextIndex = tabs.length - 1;
        } else {
            return;
        }

        event.preventDefault();
        const nextTab = tabs[nextIndex];
        nextTab.focus();
        this.setView(nextTab.dataset.ymlViewTab || 'list');
    }

    /**
     * @param {string|null} view
     */
    setView(view) {
        const normalized = view === 'map' ? 'map' : 'list';
        this.root.dataset.ymlView = normalized;

        const tabs = [...this.root.querySelectorAll(SELECTORS.viewTab)];
        tabs.forEach((btn) => {
            const active = btn.dataset.ymlViewTab === normalized;
            if (active) {
                btn.dataset.ymlSelected = '';
            } else {
                delete btn.dataset.ymlSelected;
            }
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
            btn.tabIndex = active ? 0 : -1;
        });

        const isMobile = !window.matchMedia(DESKTOP_MQ).matches;
        const listPanel = this.root.querySelector(SELECTORS.panelList);
        const mapPanel = this.root.querySelector(SELECTORS.panelMap);
        if (isMobile) {
            if (normalized === 'map') {
                listPanel?.setAttribute('hidden', '');
                mapPanel?.removeAttribute('hidden');
            } else {
                listPanel?.removeAttribute('hidden');
                mapPanel?.setAttribute('hidden', '');
            }
        } else {
            listPanel?.removeAttribute('hidden');
            mapPanel?.removeAttribute('hidden');
        }

        if (normalized === 'map' || !isMobile) {
            requestAnimationFrame(() => this.mapModule.refreshSize());
        }
    }

    showError(message) {
        if (!this.errorEl || !message) {
            return;
        }
        this.errorEl.textContent = message;
        this.errorEl.hidden = false;
    }

    clearError() {
        if (!this.errorEl) {
            return;
        }
        this.errorEl.textContent = '';
        this.errorEl.hidden = true;
    }

    setBusy(isBusy) {
        this.busy = isBusy;
        this.root.setAttribute('aria-busy', isBusy ? 'true' : 'false');
        this.searchForm?.setAttribute('aria-busy', isBusy ? 'true' : 'false');

        const submitLabel = this.submitButton?.querySelector('[data-yml-submit-label]');
        if (submitLabel) {
            if (!this.submitButton.dataset.ymlDefaultLabel) {
                this.submitButton.dataset.ymlDefaultLabel = submitLabel.textContent?.trim() || '';
            }
            submitLabel.textContent = isBusy
                ? (this.i18n.searching || 'Searching…')
                : this.submitButton.dataset.ymlDefaultLabel;
        }

        if (this.submitButton) {
            this.submitButton.disabled = isBusy;
        }
        if (this.locateButton) {
            this.locateButton.disabled = isBusy;
        }
    }

    /**
     * @returns {boolean}
     */
    ensureMapVisible() {
        if (window.matchMedia(DESKTOP_MQ).matches) {
            return false;
        }
        if (this.root.dataset.ymlView === 'map') {
            return false;
        }
        this.setView('map');
        return true;
    }

    syncEvents() {
        this.on('store:click', ({ id }) => {
            this.showStore(id);
        });
        this.on('marker:click', ({ id }) => {
            this.listModule.setActive(id);
        });
    }

    async search(params = {}) {
        this.clearError();
        this.setBusy(true);
        try {
            const parents = this.root.dataset.ymlParents || this.config.parents || params.parents || '';
            const context = this.config.context || params.context || '';
            const data = await this.searchModule.search({ parents, context, ...params });
            if (data.results) {
                this.setStores(data.results);
            }
            return data;
        } finally {
            this.setBusy(false);
        }
    }

    async locate() {
        this.clearError();
        this.setBusy(true);
        try {
            const coords = await this.geolocationModule.locate();
            await this.search({ lat: coords.latitude, lng: coords.longitude, sortby: 'distance' });
            this.mapModule.setCenter(coords.latitude, coords.longitude, 12);
            this.setLocationActive(true);
            if (this.ensureMapVisible()) {
                requestAnimationFrame(() => this.mapModule.refreshSize());
            }
            return coords;
        } finally {
            this.setBusy(false);
        }
    }

    /**
     * Drop geo filter and restore the default store list + list view.
     */
    async clearLocation() {
        this.clearError();
        this.setBusy(true);
        try {
            const input = this.searchForm?.querySelector('input[name="address"]');
            if (input) {
                input.value = '';
            }
            await this.search({});
            this.setLocationActive(false);
            this.setView('list');
        } finally {
            this.setBusy(false);
        }
    }

    /**
     * @param {boolean} active
     */
    setLocationActive(active) {
        this.locationActive = active;
        if (active) {
            this.root.dataset.ymlLocated = '';
        } else {
            delete this.root.dataset.ymlLocated;
        }
        if (!this.locateButton) {
            return;
        }
        this.locateButton.textContent = active
            ? (this.i18n.showAll || 'All locations')
            : (this.i18n.locateMe || 'My location');
    }

    showStore(id) {
        this.listModule.setActive(id);
        const openOnMap = () => this.mapModule.showStore(id, this.stores);

        if (!this.ensureMapVisible()) {
            openOnMap();
            return;
        }

        requestAnimationFrame(() => {
            this.mapModule.refreshSize();
            openOnMap();
        });
    }

    setStores(stores) {
        this.stores = stores;
        this.syncEmptyState(stores.length === 0);
        this.listModule.render(stores);
        this.mapModule.updateStores(stores);
    }

    /**
     * @param {boolean} isEmpty
     */
    syncEmptyState(isEmpty) {
        if (isEmpty) {
            this.root.dataset.ymlEmpty = '';
        } else {
            delete this.root.dataset.ymlEmpty;
        }
    }

    setCenter(latitude, longitude, zoom) {
        this.mapModule.setCenter(latitude, longitude, zoom);
    }

    getStores() {
        return [...this.stores];
    }
}

window.YandexMapsLocator = YandexMapsLocator;

document.querySelectorAll(SELECTORS.root).forEach((root) => {
    if (root.__yandexMapsLocator) {
        return;
    }
    root.__yandexMapsLocator = new YandexMapsLocator(root);
});
