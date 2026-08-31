import { emphasizeWorkingHoursDays, formatWorkingHours, formatWorkingHoursCompact } from './workingHours.js';
import { BEM, SELECTORS } from './dom.js';

export class StoreListModule {
    /**
     * @param {HTMLElement} root
     * @param {import('./events.js').EventEmitter} events
     * @param {Record<string, string>} [i18n]
     * @param {{ assetsUrl?: string }} [options]
     */
    constructor(root, events, i18n = {}, options = {}) {
        this.root = root.querySelector(SELECTORS.list);
        this.events = events;
        this.i18n = i18n;
        this.assetsUrl = (options.assetsUrl || '').replace(/\/?$/, '/');
        this.activeId = null;
        this.bind();
    }

    bind() {
        this.root?.addEventListener('click', (event) => {
            const selectButton = event.target.closest(SELECTORS.select);
            if (!selectButton) {
                return;
            }
            const item = selectButton.closest(SELECTORS.store);
            if (!item) {
                return;
            }
            const id = Number(item.dataset.ymlStoreId);
            this.setActive(id);
            this.events.emit('store:click', { id, element: item });
        });
    }

    /**
     * @param {Array<object>} stores
     */
    render(stores) {
        if (!this.root) {
            return;
        }

        while (this.root.firstChild) {
            this.root.removeChild(this.root.firstChild);
        }

        if (!Array.isArray(stores) || stores.length === 0) {
            const empty = document.createElement('p');
            empty.className = BEM.locatorEmpty;
            empty.setAttribute('role', 'status');
            empty.textContent = this.i18n.empty || 'No stores found.';
            this.root.appendChild(empty);
            this.activeId = null;
            return;
        }

        stores.forEach((store, index) => {
            this.root.appendChild(this.createItem(store, index === 0));
        });

        const firstId = stores[0]?.id;
        if (firstId != null) {
            this.setActive(firstId);
        }
    }

    /**
     * @param {object} store
     * @param {boolean} active
     * @returns {HTMLElement}
     */
    createItem(store, active) {
        const article = document.createElement('article');
        article.className = BEM.store;
        article.dataset.ymlStoreId = String(store.id);
        article.setAttribute('role', 'listitem');
        if (store.latitude != null) {
            article.dataset.ymlLat = String(store.latitude);
        }
        if (store.longitude != null) {
            article.dataset.ymlLng = String(store.longitude);
        }

        const titleId = `yml-store-title-${store.id}`;
        article.setAttribute('aria-labelledby', titleId);
        if (active) {
            article.dataset.ymlActive = '';
            article.setAttribute('aria-current', 'true');
        }

        const title = document.createElement('h3');
        title.className = BEM.storeTitle;
        const link = document.createElement('a');
        link.id = titleId;
        link.href = store.url || '#';
        link.textContent = store.pagetitle || store.title || `#${store.id}`;
        title.appendChild(link);
        article.appendChild(title);

        this.appendText(article, BEM.storeAddress, store.address);
        this.appendText(article, BEM.storeDistance, store.distance_formatted);
        if (store.phone) {
            const phoneWrap = document.createElement('p');
            phoneWrap.className = BEM.storePhone;
            const phoneLink = document.createElement('a');
            phoneLink.href = `tel:${store.phone}`;
            phoneLink.textContent = String(store.phone);
            phoneWrap.appendChild(phoneLink);
            article.appendChild(phoneWrap);
        }

        const meta = document.createElement('div');
        meta.className = BEM.storeMeta;

        if (typeof store.is_open_now === 'boolean') {
            const status = document.createElement('span');
            status.className = store.is_open_now ? `${BEM.storeStatus} is-open` : `${BEM.storeStatus} is-closed`;
            status.textContent = store.is_open_now
                ? (this.i18n.openNow || 'Open')
                : (this.i18n.closedNow || 'Closed');
            meta.appendChild(status);
        }

        const hoursText = this.resolveHoursText(store);
        if (hoursText) {
            const hours = document.createElement('p');
            hours.className = BEM.storeHours;
            hours.innerHTML = emphasizeWorkingHoursDays(hoursText);
            meta.appendChild(hours);
        }

        if (meta.childNodes.length > 0) {
            article.appendChild(meta);
        }

        const actions = document.createElement('div');
        actions.className = BEM.storeActions;

        const selectButton = document.createElement('button');
        selectButton.type = 'button';
        selectButton.className = BEM.storeSelect;
        selectButton.dataset.ymlSelect = '';
        selectButton.textContent = this.i18n.showOnMap || 'Show on map';
        actions.appendChild(selectButton);

        if (store.latitude != null && store.longitude != null) {
            actions.appendChild(this.createRouteLink(store));
        }

        article.appendChild(actions);

        return article;
    }

    /**
     * @param {object} store
     * @returns {string}
     */
    resolveHoursText(store) {
        const hoursText = (
            store.working_hours_compact
            || store.working_hours_formatted
            || formatWorkingHoursCompact(store.working_hours)
            || formatWorkingHours(store.working_hours)
            || ''
        ).trim();
        if (!hoursText) {
            return '';
        }

        const openLabel = (this.i18n.openNow || 'Open').trim().toLowerCase();
        const closedLabel = (this.i18n.closedNow || 'Closed').trim().toLowerCase();
        const duplicates = new Set([
            openLabel,
            closedLabel,
            'закрыто',
            'closed',
            'открыто',
            'open',
        ]);
        if (duplicates.has(hoursText.toLowerCase())) {
            return '';
        }

        return hoursText;
    }

    /**
     * @param {object} store
     * @returns {HTMLAnchorElement}
     */
    createRouteLink(store) {
        const label = this.i18n.route || 'Get directions';
        const routeLink = document.createElement('a');
        routeLink.className = BEM.storeRoute;
        routeLink.href = `https://yandex.ru/maps/?rtext=~${store.latitude},${store.longitude}&rtt=auto`;
        routeLink.target = '_blank';
        routeLink.rel = 'noopener noreferrer';
        routeLink.setAttribute('aria-label', label);
        routeLink.title = label;

        const icon = document.createElement('img');
        icon.className = BEM.storeRouteIcon;
        icon.src = `${this.assetsUrl}img/yandex-navigator.svg`;
        icon.width = 20;
        icon.height = 20;
        icon.alt = '';
        icon.decoding = 'async';
        routeLink.appendChild(icon);

        return routeLink;
    }

    /**
     * @param {HTMLElement} parent
     * @param {string} className
     * @param {unknown} value
     */
    appendText(parent, className, value) {
        if (value == null || value === '') {
            return;
        }
        const node = document.createElement('p');
        node.className = className;
        node.textContent = String(value);
        parent.appendChild(node);
    }

    setActive(id) {
        this.activeId = id;
        this.root?.querySelectorAll(SELECTORS.store).forEach((node) => {
            const isActive = Number(node.dataset.ymlStoreId) === id;
            if (isActive) {
                node.dataset.ymlActive = '';
                node.setAttribute('aria-current', 'true');
            } else {
                delete node.dataset.ymlActive;
                node.removeAttribute('aria-current');
            }
        });
        const active = this.root?.querySelector(`[data-yml-store-id="${id}"]`);
        active?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        this.events.emit('store:active', { id, element: active });
    }
}
