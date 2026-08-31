/**
 * @param {unknown} raw
 * @returns {string}
 */
export function formatWorkingHours(raw) {
    if (raw == null) {
        return '';
    }

    const text = String(raw).trim();
    if (text === '') {
        return '';
    }

    if (!text.startsWith('{') && !text.startsWith('[')) {
        return text;
    }

    let data;
    try {
        data = JSON.parse(text);
    } catch {
        return text;
    }

    if (!data || typeof data !== 'object') {
        return text;
    }

    const dayKeys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    const dayLabels = {
        mon: 'Пн',
        tue: 'Вт',
        wed: 'Ср',
        thu: 'Чт',
        fri: 'Пт',
        sat: 'Сб',
        sun: 'Вс',
    };

    const normalizeSlots = (slots) => {
        if (!Array.isArray(slots)) {
            return [];
        }
        return slots.map((slot) => String(slot).trim()).filter(Boolean);
    };

    const isAlwaysOpen = dayKeys.every((key) => {
        const slots = normalizeSlots(data[key]);
        return slots.length === 1 && (slots[0] === '00:00-23:59' || slots[0] === '00:00-24:00');
    });
    if (isAlwaysOpen) {
        return 'Круглосуточно';
    }

    const isAlwaysClosed = dayKeys.every((key) => normalizeSlots(data[key]).length === 0);
    if (isAlwaysClosed) {
        return '';
    }

    const lines = [];
    dayKeys.forEach((key) => {
        const slots = normalizeSlots(data[key]);
        if (slots.length === 0) {
            return;
        }
        lines.push(`${dayLabels[key]}: ${slots.join(', ')}`);
    });

    return lines.length > 0 ? lines.join(' · ') : text;
}

const DAY_KEYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
const DAY_LABELS = {
    mon: 'Пн',
    tue: 'Вт',
    wed: 'Ср',
    thu: 'Чт',
    fri: 'Пт',
    sat: 'Сб',
    sun: 'Вс',
};

/**
 * @param {unknown} raw
 * @returns {string}
 */
export function formatWorkingHoursCompact(raw) {
    if (raw == null) {
        return '';
    }

    const text = String(raw).trim();
    if (text === '') {
        return '';
    }

    if (!text.startsWith('{') && !text.startsWith('[')) {
        return text;
    }

    let data;
    try {
        data = JSON.parse(text);
    } catch {
        return text;
    }

    if (!data || typeof data !== 'object') {
        return text;
    }

    const normalizeSlots = (slots) => {
        if (!Array.isArray(slots)) {
            return [];
        }
        return slots.map((slot) => String(slot).trim()).filter(Boolean);
    };

    const isAlwaysOpen = DAY_KEYS.every((key) => {
        const slots = normalizeSlots(data[key]);
        return slots.length === 1 && (slots[0] === '00:00-23:59' || slots[0] === '00:00-24:00');
    });
    if (isAlwaysOpen) {
        return 'Круглосуточно';
    }

    const isAlwaysClosed = DAY_KEYS.every((key) => normalizeSlots(data[key]).length === 0);
    if (isAlwaysClosed) {
        return '';
    }

    /** @type {Record<string, string>} */
    const dayHours = {};
    DAY_KEYS.forEach((key) => {
        const slots = normalizeSlots(data[key]);
        if (slots.length === 0) {
            return;
        }
        dayHours[key] = slots.join(', ');
    });

    if (Object.keys(dayHours).length === 0) {
        return text;
    }

    /** @type {Array<{start: string, end: string, value: string|null}>} */
    const groups = [];
    /** @type {{start: string, end: string, value: string|null}|null} */
    let current = null;

    DAY_KEYS.forEach((key) => {
        const value = dayHours[key] ?? null;
        if (current === null || current.value !== value) {
            if (current !== null) {
                groups.push(current);
            }
            current = { start: key, end: key, value };
            return;
        }
        current.end = key;
    });

    if (current !== null) {
        groups.push(current);
    }

    const segments = groups
        .filter((group) => group.value !== null)
        .map((group) => {
            const startLabel = DAY_LABELS[group.start] || group.start;
            const endLabel = DAY_LABELS[group.end] || group.end;
            const rangeLabel = group.start === group.end ? startLabel : `${startLabel}–${endLabel}`;
            return `${rangeLabel} ${group.value}`;
        });

    return segments.length > 0 ? segments.join(' · ') : text;
}

/**
 * Escape plain schedule text and wrap day/range labels for store-card emphasis.
 * @param {string} text
 * @returns {string}
 */
export function emphasizeWorkingHoursDays(text) {
    const plain = String(text ?? '');
    if (plain === '') {
        return '';
    }

    const escaped = plain
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    return escaped.replace(
        /((?:Пн|Вт|Ср|Чт|Пт|Сб|Вс|Mon|Tue|Wed|Thu|Fri|Sat|Sun)(?:[–-](?:Пн|Вт|Ср|Чт|Пт|Сб|Вс|Mon|Tue|Wed|Thu|Fri|Sat|Sun))?)/g,
        '<span class="yml-store__hours-day">$1</span>',
    );
}
