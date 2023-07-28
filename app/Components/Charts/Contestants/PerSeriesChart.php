<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Contestants;

use FKSDB\Models\ORM\Services\SubmitService;
use Fykosak\Utils\UI\Title;

class PerSeriesChart extends AbstractPerSeriesChart
{

    private SubmitService $submitService;

    public function injectSecondary(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    protected function getData(): array
    {
        $query = $this->submitService->getTable()
            ->where('task.contest_id', $this->contest->contest_id)
            ->group('task.series, task.year')
            ->select('COUNT(DISTINCT contestant_id) AS count,task.series, task.year');
        $data = [];
        foreach ($query as $row) {
            $year = $row->year;
            $series = $row->series;
            $data[$year] = $data[$year] ?? [];
            $data[$year][$series] = $row->count;
        }
        return $data;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Contestants per series'), 'fas fa-chart-column');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
