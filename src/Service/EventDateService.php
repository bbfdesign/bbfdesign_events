<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use Plugin\bbfdesign_events\src\Model\EventDate;

class EventDateService
{
    /**
     * @param EventDate[] $dates
     */
    public function computeStatus(array $dates): string
    {
        if (empty($dates)) {
            return 'upcoming';
        }

        $now = new \DateTimeImmutable('today');
        $earliest = null;
        $latest = null;

        foreach ($dates as $date) {
            $start = $date->dateStart;
            $end = $date->dateEnd ?? $date->dateStart;

            if ($earliest === null || $start < $earliest) {
                $earliest = $start;
            }
            if ($latest === null || $end > $latest) {
                $latest = $end;
            }
        }

        if ($now > $latest) {
            return 'past';
        }
        if ($now >= $earliest && $now <= $latest) {
            return 'running';
        }
        return 'upcoming';
    }

    /**
     * @param EventDate[] $dates
     */
    public function getNextDate(array $dates): ?\DateTimeImmutable
    {
        $now = new \DateTimeImmutable('today');
        $next = null;

        foreach ($dates as $date) {
            if ($date->dateStart >= $now) {
                if ($next === null || $date->dateStart < $next) {
                    $next = $date->dateStart;
                }
            }
        }

        return $next;
    }

    /**
     * @param EventDate[] $dates
     */
    public function getEarliestDate(array $dates): ?\DateTimeImmutable
    {
        $earliest = null;
        foreach ($dates as $date) {
            if ($earliest === null || $date->dateStart < $earliest) {
                $earliest = $date->dateStart;
            }
        }
        return $earliest;
    }

    /**
     * @param EventDate[] $dates
     */
    public function getLatestDate(array $dates): ?\DateTimeImmutable
    {
        $latest = null;
        foreach ($dates as $date) {
            $end = $date->dateEnd ?? $date->dateStart;
            if ($latest === null || $end > $latest) {
                $latest = $end;
            }
        }
        return $latest;
    }
}
