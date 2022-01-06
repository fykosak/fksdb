<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Charts\Contestants;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\ServiceSubmit;
use Nette\DI\Container;

class PerYearsChart extends ReactComponent implements Chart
{

    private ServiceSubmit $serviceSubmit;
    protected ModelContest $contest;

    public function __construct(Container $container, ModelContest $contest)
    {
        parent::__construct($container, 'chart.contestants.per-years');
        $this->contest = $contest;
    }

    public function injectSecondary(ServiceSubmit $serviceSubmit): void
    {
        $this->serviceSubmit = $serviceSubmit;
    }

    protected function getData(): array
    {
        $seriesQuery = $this->serviceSubmit->getTable()
            ->where('task.contest_id', $this->contest->contest_id)
            ->group('task.series, task.year')
            ->select('COUNT(DISTINCT ct_id) AS count,task.series, task.year');

        $yearsQuery = $this->serviceSubmit->getTable()
            ->where('task.contest_id', $this->contest->contest_id)
            ->group('task.year')
            ->select('COUNT(DISTINCT ct_id) AS count, task.year');

        $data = [];
        foreach ($seriesQuery as $row) {
            $year = $row->year;
            $series = $row->series;
            $data[$year] = $data[$year] ?? [];
            $data[$year][$series] = $row->count;
        }
        foreach ($yearsQuery as $row) {
            $year = $row->year;
            $data[$year] = $data[$year] ?? [];
            $data[$year]['year'] = $row->count;
        }
        return $data;
    }

    public function getTitle(): string
    {
        return _('Contestants per years');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
