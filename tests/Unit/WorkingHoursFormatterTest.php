<?php

declare(strict_types=1);

use YandexMapsLocator\Support\WorkingHoursFormatter;

describe('WorkingHoursFormatter', function () {
    it('returns plain text unchanged', function () {
        expect(WorkingHoursFormatter::format('выходной'))->toBe('выходной');
    });

    it('formats 24/7 json', function () {
        $json = '{"mon":["00:00-23:59"],"tue":["00:00-23:59"],"wed":["00:00-23:59"],"thu":["00:00-23:59"],"fri":["00:00-23:59"],"sat":["00:00-23:59"],"sun":["00:00-23:59"]}';

        expect(WorkingHoursFormatter::format($json))->toBe('Круглосуточно');
    });

    it('formats always closed json as empty (status badge owns that signal)', function () {
        $json = '{"mon":[],"tue":[],"wed":[],"thu":[],"fri":[],"sat":[],"sun":[]}';

        expect(WorkingHoursFormatter::format($json))->toBe('');
        expect(WorkingHoursFormatter::formatCompact($json))->toBe('');
    });

    it('formats weekday schedule', function () {
        $json = '{"mon":["09:00-21:00"],"tue":["09:00-21:00"],"wed":["09:00-21:00"],"thu":["09:00-21:00"],"fri":["09:00-22:00"],"sat":["10:00-22:00"],"sun":["10:00-20:00"]}';

        expect(WorkingHoursFormatter::format($json))
            ->toContain('Пн: 09:00-21:00')
            ->toContain('Вс: 10:00-20:00');
    });

    it('formats compact weekday ranges', function () {
        $json = '{"mon":["09:00-21:00"],"tue":["09:00-21:00"],"wed":["09:00-21:00"],"thu":["09:00-21:00"],"fri":["09:00-22:00"],"sat":["10:00-22:00"],"sun":["10:00-20:00"]}';

        expect(WorkingHoursFormatter::formatCompact($json))
            ->toBe('Пн–Чт 09:00-21:00 · Пт 09:00-22:00 · Сб 10:00-22:00 · Вс 10:00-20:00');
    });

    it('emphasizes day labels in compact html', function () {
        $json = '{"mon":["09:00-21:00"],"tue":["09:00-21:00"],"wed":["09:00-21:00"],"thu":["09:00-21:00"],"fri":["09:00-22:00"],"sat":["10:00-22:00"],"sun":["10:00-20:00"]}';

        $html = WorkingHoursFormatter::formatCompactHtml($json);
        expect($html)
            ->toContain('<span class="yml-store__hours-day">Пн–Чт</span>')
            ->toContain('<span class="yml-store__hours-day">Пт</span>')
            ->toContain('09:00-21:00')
            ->not->toContain('<script');
    });
});
