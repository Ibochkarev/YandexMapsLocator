(function () {
    'use strict';

    const cfg = window.yandexMapsLocatorMgr;
    if (!cfg || !cfg.connectorUrl) {
        return;
    }

    const tv = cfg.tvNames || {};
    const addressTv = tv.address || 'yandexmaps_address';
    const latitudeTv = tv.latitude || 'yandexmaps_latitude';
    const longitudeTv = tv.longitude || 'yandexmaps_longitude';

    const addressField = document.querySelector('[name="tv' + getTvId(addressTv) + '"]')
        || document.querySelector('input[id*="' + addressTv + '"]');

    if (!addressField) {
        return;
    }

    const wrap = document.createElement('div');
    wrap.className = 'yml-mgr-geocode';
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'x-btn x-btn-small';
    button.textContent = 'Получить координаты';
    wrap.appendChild(button);
    addressField.parentNode?.insertBefore(wrap, addressField.nextSibling);

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
                address,
            });
            const response = await fetch(cfg.connectorUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body.toString(),
                credentials: 'same-origin',
            });
            const data = await response.json();
            if (!data.success) {
                alert(data.message || 'Geocode failed');
                return;
            }
            setTvValue(latitudeTv, data.object.latitude);
            setTvValue(longitudeTv, data.object.longitude);
        } catch (error) {
            alert(error.message || 'Network error');
        } finally {
            button.disabled = false;
        }
    });

    function setTvValue(name, value) {
        const input = document.querySelector('[name="tv' + getTvId(name) + '"]')
            || document.querySelector('input[id*="' + name + '"]');
        if (input) {
            input.value = value;
        }
    }

    function getTvId(name) {
        const input = document.querySelector('input[id*="' + name + '"]');
        const match = input?.name?.match(/^tv(\d+)/);
        return match ? match[1] : '';
    }
})();
