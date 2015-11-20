<?php

namespace Sofa\LaravelScopes;

use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Database\Query\Builder;

class Periods
{
    /**
     * Register macros on the Query Builder.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return void
     */
    public function apply()
    {
        $this->registerMacro();

        $this->registerHelpers();
    }

    /**
     * Register periods macro.
     *
     * @return void
     */
    protected function registerMacro()
    {
        $that = $this;

        /**
         * Query scope periods - filter this or last/next N periods
         *
         * @param  \Illuminate\Database\Query\Builder $query
         * @param  string  $unit                                 Period type [minute|hour|day|week|month|year]
         * @param  integer $periods                              Number of past periods
         * @param  string  $column                               Column to match against
         * @param  integer $includeCurrent                       Whether to include current period in filter (additionally)
         * @return \Illuminate\Database\Query\Builder
         *
         * @throws \InvalidArgumentException
         */
        $macro = function ($unit, $periods, $column = null, $includeCurrent = false) use ($that) {
            if (!in_array($unit, ['minute', 'hour', 'day', 'week', 'month', 'year'])) {
                throw new InvalidArgumentException('Invalid period type');
            }

            // Developer may pass $includeCurrent instead of $column
            if (func_num_args() == 3 && is_bool($column)) {
                $includeCurrent = $column;

                $column = null;
            }

            $column = $column ?: 'created_at';

            $range = $that->getPeriodRange($unit, $periods, $includeCurrent);

            return $this->whereBetween($column, $range);
        };

        Builder::macro('periods', $macro);
    }

    /**
     * Get dates range for the where between clause.
     *
     * @param  string  $unit
     * @param  integer $periods
     * @param  boolean $includeCurrent
     * @return \Carbon\Carbon[]
     */
    public function getPeriodRange($unit, $periods, $includeCurrent)
    {
        // Here we have 2 timestamps - one is closer to now, the other is further
        // from now. Depending on whether a developer wants to include current
        // period in the filter or not, let's parse the params accordingly.
        $future = ($periods >= 0);

        if ($includeCurrent) {
            $closerDate = Carbon::now();
        } else {
            $keyword = ($future) ? 'next' : 'last';

            $closerDate = Carbon::parse("{$keyword} {$unit}");
        }

        $furtherDate = Carbon::now()->{'add'.$unit}($periods);

        $range = [
            $this->adjustTimestamp($closerDate, $unit, !$future),
            $this->adjustTimestamp($furtherDate, $unit, $future),
        ];

        usort($range, function ($closer, $further) {
            return $closer->format('U') > $further->format('U');
        });

        return $range;
    }

    /**
     * Adjust timestamps to make them beginning or end of the period.
     *
     * @param  \Carbon\Carbon $timestamp
     * @param  string  $unit
     * @param  boolean $endOf
     * @return \Carbon\Carbon
     */
    protected function adjustTimestamp(Carbon $timestamp, $unit, $endOf = false)
    {
        $ending = ($endOf) ? 59 : 0;

        $method = ($endOf) ? 'endOf' : 'startOf';

        switch ($unit) {
            case 'minute':
                $timestamp->second($ending);
                break;

            case 'hour':
                $timestamp->minute($ending)->second($ending);
                break;

            default:
                $timestamp->{$method.ucfirst($unit)}();
        }

        return $timestamp;
    }

    /**
     * Register handy helper macros.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return void
     */
    protected function registerHelpers()
    {
        Builder::macro('thisYear', function ($column = null) {
            return $this->thisPeriod('year', $column);
        });

        Builder::macro('thisMonth', function ($column = null) {
            return $this->thisPeriod('month', $column);
        });

        Builder::macro('thisWeek', function ($column = null) {
            return $this->thisPeriod('week', $column);
        });

        Builder::macro('today', function ($column = null) {
            return $this->thisPeriod('day', $column);
        });

        Builder::macro('thisHour', function ($column = null) {
            return $this->thisPeriod('hour', $column);
        });

        Builder::macro('thisMinute', function ($column = null) {
            return $this->thisPeriod('minute', $column);
        });

        Builder::macro('nextYear', function ($column = null) {
            return $this->nextPeriod('year', $column);
        });

        Builder::macro('nextMonth', function ($column = null) {
            return $this->nextPeriod('month', $column);
        });

        Builder::macro('nextWeek', function ($column = null) {
            return $this->nextPeriod('week', $column);
        });

        Builder::macro('tomorrow', function ($column = null) {
            return $this->nextPeriod('day', $column);
        });

        Builder::macro('nextHour', function ($column = null) {
            return $this->nextPeriod('hour', $column);
        });

        Builder::macro('nextMinute', function ($column = null) {
            return $this->nextPeriod('minute', $column);
        });

        Builder::macro('lastYear', function ($column = null) {
            return $this->lastPeriod('year', $column);
        });

        Builder::macro('lastMonth', function ($column = null) {
            return $this->lastPeriod('month', $column);
        });

        Builder::macro('lastWeek', function ($column = null) {
            return $this->lastPeriod('week', $column);
        });

        Builder::macro('yesterday', function ($column = null) {
            return $this->lastPeriod('day', $column);
        });

        Builder::macro('lastHour', function ($column = null) {
            return $this->lastPeriod('hour', $column);
        });

        Builder::macro('lastMinute', function ($column = null) {
            return $this->lastPeriod('minute', $column);
        });

        Builder::macro('nextPeriod', function ($unit, $column = null) {
            return $this->periods($unit, 1, $column, false);
        });

        Builder::macro('thisPeriod', function ($unit, $column = null) {
            return $this->periods($unit, 0, $column, true);
        });

        Builder::macro('lastPeriod', function ($unit, $column = null) {
            return $this->periods($unit, -1, $column, false);
        });
    }
}
