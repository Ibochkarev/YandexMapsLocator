(function () {
    'use strict';

    function createModxButton(label) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'x-btn x-btn-small';

        const inner = document.createElement('span');
        inner.className = 'x-btn-inner';

        const text = document.createElement('span');
        text.className = 'x-btn-text';
        text.textContent = label;

        inner.appendChild(text);
        button.appendChild(inner);
        return button;
    }

    function findTvInput(tvId, tvName) {
        if (tvId) {
            const byId = document.querySelector('#tv' + tvId)
                || document.querySelector('[name="tv' + tvId + '"]');
            if (byId) {
                return byId;
            }
        }
        return document.querySelector('input[id*="' + tvName + '"]')
            || document.querySelector('textarea[id*="' + tvName + '"]');
    }

    function findTvRow(field, tvId) {
        const id = tvId || String(field.id || field.name || '').replace(/^tv/, '');
        if (id) {
            const byRow = document.getElementById('tv' + id + '-tr');
            if (byRow) {
                return byRow;
            }
        }
        return field.closest('.modx-tv');
    }

    function findTvValueColumn(field) {
        return field.closest('.modx-tv-form-element')
            || field.closest('.x-form-element');
    }

    function cleanupLegacyAddon(row, markerClass) {
        if (!row || !row.parentNode) {
            return;
        }
        const sibling = row.nextElementSibling;
        if (sibling && sibling.classList.contains(markerClass)) {
            sibling.remove();
        }
    }

    function mountInValueColumn(field, wrap, row, markerClass) {
        cleanupLegacyAddon(row, markerClass);
        const valueColumn = findTvValueColumn(field);
        if (valueColumn) {
            const existing = valueColumn.querySelector('.' + markerClass);
            if (existing) {
                return existing.parentElement === valueColumn;
            }
            valueColumn.appendChild(wrap);
            return true;
        }
        if (row && row.nextElementSibling === wrap) {
            return true;
        }
        if (row) {
            row.insertAdjacentElement('afterend', wrap);
            return true;
        }
        field.insertAdjacentElement('afterend', wrap);
        return true;
    }

    function boot() {
        const cfg = window.yandexMapsLocatorMgr;
        if (!cfg || !cfg.connectorUrl) {
            return false;
        }

        const tv = cfg.tvNames || {};
        const tvIds = cfg.tvIds || {};
        const addressTv = tv.address || 'yandexmaps_address';
        const latitudeTv = tv.latitude || 'yandexmaps_latitude';
        const longitudeTv = tv.longitude || 'yandexmaps_longitude';

        const addressField = findTvInput(tvIds.address, addressTv);
        if (!addressField) {
            return false;
        }

        const row = findTvRow(addressField, tvIds.address);
        const valueColumn = findTvValueColumn(addressField);
        if (valueColumn && valueColumn.querySelector('.yml-mgr-geocode')) {
            return true;
        }
        if (row && row.nextElementSibling?.classList?.contains('yml-mgr-geocode')) {
            cleanupLegacyAddon(row, 'yml-mgr-geocode');
        }

        if (addressField.dataset.ymlGeocodeBound === '1') {
            return true;
        }
        addressField.dataset.ymlGeocodeBound = '1';

        const wrap = document.createElement('div');
        wrap.className = 'yml-mgr-geocode yml-mgr-addon';
        wrap.appendChild(createModxButton('Получить координаты'));
        const button = wrap.querySelector('button');

        if (!mountInValueColumn(addressField, wrap, row, 'yml-mgr-geocode')) {
            return false;
        }

        button.addEventListener('click', async () => {
            const address = addressField.value.trim();
            if (!address) {
                alert('Введите адрес');
                return;
            }
            button.disabled = true;
            try {
                const body = new URLSearchParams({
                    action: 'YandexMapsLocator\\Processors\\Geocode\\Geocode',
                    address: address,
                });
                const modAuth = (window.MODx && (MODx.siteId || (MODx.config && MODx.config.modAuth))) || '';
                if (modAuth) {
                    body.set('auth', modAuth);
                    body.set('HTTP_MODAUTH', modAuth);
                }
                const response = await fetch(cfg.connectorUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        modAuth: modAuth,
                    },
                    body: body.toString(),
                    credentials: 'same-origin',
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message || 'Geocode failed');
                    return;
                }
                setTvValue(tvIds.latitude, latitudeTv, data.object.latitude);
                setTvValue(tvIds.longitude, longitudeTv, data.object.longitude);
            } catch (error) {
                alert(error.message || 'Network error');
            } finally {
                button.disabled = false;
            }
        });

        return true;
    }

    function setTvValue(tvId, name, value) {
        const input = findTvInput(tvId, name);
        if (input) {
            input.value = value;
        }
    }

    function start() {
        if (boot()) {
            return;
        }
        let attempts = 0;
        const timer = window.setInterval(function () {
            if (boot() || ++attempts > 120) {
                window.clearInterval(timer);
            }
        }, 250);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
