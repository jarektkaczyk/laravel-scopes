<?php

use kahlan\Arg;
use Carbon\Carbon;
use kahlan\plugin\Stub;
use Sofa\LaravelScopes\Periods;
use Illuminate\Database\Query\Builder;

describe('Sofa\LaravelScopes\Periods', function () {

    before(function () {
        (new Periods)->apply();
    });

    beforeEach(function () {
        $this->query = Stub::create([
            'extends' => Builder::class,
            'methods' => ['__construct'], // override constructor
        ]);
    });

    context('->getPeriodRange()', function () {
        it('prepares valid period range', function () {
            $range = [
                Carbon::now()->startOfYear(),
                Carbon::now()->addYears(2)->endOfYear(),
            ];
            expect((new Periods)->getPeriodRange('year', 2, true))->toEqual($range);

            $range = [
                Carbon::parse('-1 hour')->minute(0)->second(0),
                Carbon::parse('-1 hour')->minute(59)->second(59)
            ];
            expect((new Periods)->getPeriodRange('hour', -1, false))->toEqual($range);

            $range = [
                Carbon::parse('+1 minute')->second(0),
                Carbon::parse('+1 minute')->second(59)
            ];
            expect((new Periods)->getPeriodRange('minute', 1, false))->toEqual($range);
        });
    });

    context('Query macros', function () {
        it('complains about invalid period unit', function () {
            expect(function () {
                $this->query->periods('invalid', 1);
            })->toThrow(new InvalidArgumentException);
        });

        it('->lastPeriods() with current inclusive', function () {
            (new Periods)->apply();
            $range = [
                Carbon::now()->subYears(2)->startOfYear(),
                Carbon::now()->endOfYear(),
            ];

            $this->query->periods('year', -2, true);

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->lastPeriods() current exclusive', function () {
            $range = [
                Carbon::now()->subYears(2)->startOfYear(),
                Carbon::now()->subYear()->endOfYear(),
            ];

            $this->query->periods('year', -2);

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->nextPeriods() with current inclusive', function () {
            $range = [
                Carbon::now()->startOfYear(),
                Carbon::now()->addYears(3)->endOfYear()
            ];

            $this->query->periods('year', 3, null, true);

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->nextPeriods() current exclusive', function () {
            $range = [
                Carbon::now()->addYear()->startOfYear(),
                Carbon::now()->addYears(3)->endOfYear()
            ];

            $this->query->periods('year', 3);

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->thisYear() with custom column', function () {
            $range = [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ];

            $this->query->thisYear('another_column');

            expect($this->query)->toReceive('whereBetween')->with('another_column', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->nextYear()', function () {
            $range = [
                Carbon::parse('+1 year')->startOfYear(),
                Carbon::parse('+1 year')->endOfYear()
            ];

            $this->query->nextYear();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->nextMonth()', function () {
            $range = [
                Carbon::parse('+1 month')->startOfMonth(),
                Carbon::parse('+1 month')->endOfMonth()
            ];

            $this->query->nextMonth();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->tomorrow()', function () {
            $range = [
                Carbon::parse('tomorrow')->startOfDay(),
                Carbon::parse('tomorrow')->endOfDay()
            ];

            $this->query->tomorrow();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->nextHour()', function () {
            $range = [
                Carbon::parse('+1 hour')->minute(0)->second(0),
                Carbon::parse('+1 hour')->minute(59)->second(59)
            ];

            $this->query->nextHour();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->nextMinute()', function () {
            $range = [
                Carbon::parse('+1 minute')->second(0),
                Carbon::parse('+1 minute')->second(59)
            ];

            $this->query->nextMinute();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->thisYear()', function () {
            $range = [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ];
            $this->query->thisYear();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->thisMonth()', function () {
            $range = [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ];
            $this->query->thisMonth();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->today()', function () {
            $range = [
                Carbon::today(),
                Carbon::today()->endOfDay()
            ];
            $this->query->today();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->thisHour()', function () {
            $range = [
                Carbon::now()->minute(0)->second(0),
                Carbon::now()->minute(59)->second(59)
            ];
            $this->query->thisHour();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->lastYear()', function () {
            $range = [
                Carbon::parse('-1 year')->startOfYear(),
                Carbon::parse('-1 year')->endOfYear()
            ];
            $this->query->lastYear();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->lastMonth()', function () {
            $range = [
                Carbon::parse('-1 month')->startOfMonth(),
                Carbon::parse('-1 month')->endOfMonth()
            ];
            $this->query->lastMonth();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->yesterday()', function () {
            $range = [
                Carbon::yesterday(),
                Carbon::yesterday()->endOfDay()
            ];
            $this->query->yesterday();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->lastHour()', function () {
            $range = [
                Carbon::parse('-1 hour')->minute(0)->second(0),
                Carbon::parse('-1 hour')->minute(59)->second(59)
            ];
            $this->query->lastHour();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });

        it('->lastMinute()', function () {
            $range = [
                Carbon::parse('-1 minute')->second(0),
                Carbon::parse('-1 minute')->second(59)
            ];
            $this->query->lastMinute();

            expect($this->query)->toReceive('whereBetween')->with('created_at', Arg::toBeAn('array'));
            expect($this->query->getBindings())->toEqual($range);
        });
    });
});
