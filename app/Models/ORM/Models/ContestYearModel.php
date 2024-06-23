<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Tests\ContestYear\InActiveContest;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read int $year
 * @property-read int $ac_year
 */
final class ContestYearModel extends Model
{
    /**
     * @phpstan-return TypedGroupedSelection<ContestantModel>
     */
    public function getContestants(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<ContestantModel> $selection */
        $selection = $this->contest->related(DbNames::TAB_CONTESTANT, 'contest_id')
            ->where('year', $this->year);
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrganizerModel>
     */
    public function getOrganizers(): TypedGroupedSelection
    {
        return $this->contest->getOrganizers()
            ->where('since<=?', $this->year)
            ->where('until IS NULL OR until >=?', $this->year);
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskModel>
     */
    public function getTasks(?int $series = null): TypedGroupedSelection
    {
        $query = $this->contest->getTasks()->where('year', $this->year);
        if (isset($series)) {
            $query->where('series', $series);
        }
        return $query;
    }

    public function isActive(): bool
    {
        return $this->getAvailableTasks()->count('*') > 0;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskModel>
     */
    public function getAvailableTasks(): TypedGroupedSelection
    {
        return $this->getTasks()
            ->where('submit_start IS NULL OR submit_start < NOW()')
            ->where('submit_deadline IS NULL OR submit_deadline >= NOW()');
    }

    public function getLastSeries(): int
    {
        return $this->getTasks()->max('series') ?? 1;
    }

    public function getTotalSeries(): int
    {
        return $this->hasHolidaySeries() ? 9 : 6;
    }

    /**
     * Check if specific year has a holiday series.
     * Made primarly for Výfuk contest.
     */
    public function hasHolidaySeries(): bool
    {
        return $this->contest_id === ContestModel::ID_VYFUK && $this->year >= 9;
    }

    /**
     * @phpstan-return Test<self>[]
     */
    public static function getTests(Container $container): array
    {
        return [
            new InActiveContest($container),
        ];
    }
}
