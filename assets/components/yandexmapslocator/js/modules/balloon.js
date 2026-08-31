import { formatWorkingHours, formatWorkingHoursCompact } from './workingHours.js';

import { BEM } from './dom.js';



/**

 * @param {unknown} value

 * @returns {string}

 */

export function escapeHtml(value) {

    return String(value)

        .replace(/&/g, '&amp;')

        .replace(/</g, '&lt;')

        .replace(/>/g, '&gt;')

        .replace(/"/g, '&quot;');

}



/**

 * @param {unknown} value

 * @returns {string}

 */

export function escapeAttr(value) {

    return String(value).replace(/"/g, '&quot;');

}



/**

 * @param {object} store

 * @param {object} [config]

 * @returns {string}

 */

export function resolveBalloonImage(store, config = {}) {

    const field = config.balloonImageField || 'balloon_image';

    const fromStore = store?.[field] ?? store?.balloon_image ?? '';

    if (fromStore) {

        return String(fromStore).trim();

    }



    const fallback = config.balloon?.defaultImage ?? config.defaultBalloonImage ?? '';

    return String(fallback).trim();

}



/**

 * @param {object} store

 * @param {object} [config]

 * @returns {string}

 */

export function resolveMarkerIcon(store, config = {}) {

    const field = config.markerIconField || 'marker_icon';

    const icon = store?.[field] ?? store?.marker_icon ?? '';

    return icon ? String(icon).trim() : '';

}



/**

 * @param {string} url

 * @param {string} [alt]

 * @returns {string}

 */

function buildBalloonImageHtml(url, alt = '') {

    const safeUrl = escapeAttr(url);

    const safeAlt = escapeHtml(alt);



    return `<figure class="${BEM.balloonMedia}"><img class="${BEM.balloonImage}" src="${safeUrl}" alt="${safeAlt}" loading="lazy" decoding="async" /></figure>`;

}



/**

 * @param {object} store

 * @param {Record<string, string>} [i18n]

 * @param {object} [config]

 * @returns {{ balloonContentHeader: string, balloonContentBody: string, balloonContentFooter: string, hintContent: string }}

 */

export function buildBalloonProperties(store, i18n = {}, config = {}) {

    const title = store.pagetitle || store.title || `#${store.id}`;

    const address = store.address ? String(store.address).trim() : '';

    const phone = store.phone ? String(store.phone).trim() : '';

    const distance = store.distance_formatted ? String(store.distance_formatted).trim() : '';

    const hoursRaw =
        store.working_hours_compact
        || store.working_hours_formatted
        || formatWorkingHoursCompact(store.working_hours)
        || formatWorkingHours(store.working_hours)
        || '';
    const hours = String(hoursRaw).trim();
    const closedDup = new Set(['закрыто', 'closed', 'открыто', 'open']);
    const hoursUseful = hours && !closedDup.has(hours.toLowerCase());

    const bodyParts = [];
    const imageUrl = resolveBalloonImage(store, config);
    if (imageUrl) {
        bodyParts.push(buildBalloonImageHtml(imageUrl, title));
    }

    if (address) {
        bodyParts.push(`<p class="${BEM.balloonAddress}">${escapeHtml(address)}</p>`);
    }

    if (distance) {
        bodyParts.push(`<p class="${BEM.balloonDistance}">${escapeHtml(distance)}</p>`);
    }

    if (phone) {
        const tel = phone.replace(/[^\d+]/g, '');
        bodyParts.push(
            `<p class="${BEM.balloonPhone}"><a href="tel:${escapeAttr(tel)}">${escapeHtml(phone)}</a></p>`,
        );
    }

    if (hoursUseful) {
        bodyParts.push(`<p class="${BEM.balloonHours}">${escapeHtml(hours)}</p>`);
    }



    let footer = '';

    if (store.latitude != null && store.longitude != null) {

        const routeLabel = i18n.route || 'Get directions';

        const href = `https://yandex.ru/maps/?rtext=~${encodeURIComponent(store.latitude)},${encodeURIComponent(store.longitude)}&rtt=auto`;

        footer = `<a class="${BEM.balloonRoute}" href="${href}" target="_blank" rel="noopener noreferrer">${escapeHtml(routeLabel)}</a>`;

    }



    const pageUrl = store.url ? String(store.url).trim() : '';

    let header = escapeHtml(title);

    if (pageUrl && pageUrl !== '#') {

        header = `<a class="${BEM.balloonTitle}" href="${escapeAttr(pageUrl)}">${escapeHtml(title)}</a>`;

    } else {

        header = `<span class="${BEM.balloonTitle}">${escapeHtml(title)}</span>`;

    }



    return {

        balloonContentHeader: header,

        balloonContentBody: bodyParts.length > 0 ? `<div class="${BEM.balloon}">${bodyParts.join('')}</div>` : '',

        balloonContentFooter: footer,

        hintContent: address || title,

    };

}



/**

 * @param {object} store

 * @param {object} [config]

 * @returns {object}

 */

export function resolveMarkerOptions(store, config = {}) {

    const base = { ...(config.markerOptions || { preset: 'islands#redDotIcon' }) };

    const iconUrl = resolveMarkerIcon(store, config);

    if (!iconUrl) {

        return base;

    }



    const size = Array.isArray(config.markerIconSize) ? config.markerIconSize : [32, 32];

    const width = Number(size[0]) > 0 ? Number(size[0]) : 32;

    const height = Number(size[1]) > 0 ? Number(size[1]) : 32;



    return {

        iconLayout: 'default#image',

        iconImageHref: iconUrl,

        iconImageSize: [width, height],

        iconImageOffset: [-Math.round(width / 2), -height],

    };

}

