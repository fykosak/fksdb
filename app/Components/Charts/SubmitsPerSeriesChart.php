<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Services\SubmitService;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class SubmitsPerSeriesChart extends FrontEndComponent implements Chart
{

    private SubmitService $submitService;
    private ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container, 'chart.submits.per-series');
        $this->contestYear = $contestYear;
    }

    final public function injectServiceSubmit(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    /**
     * @phpstan-return array<int,array{submitted_on:string,series:int}>
     */
    public function getData(): array
    {
        $query = $this->submitService->getTable()
            ->where('task.contest_id', $this->contestYear->contest_id)
            ->where('task.year', $this->contestYear->year)
            ->order('task.series, submitted_on');
        $data = [];
        /** @var SubmitModel $submit */
        foreach ($query as $submit) {
            $data[] = [
                'submitted_on' => $submit->submitted_on->format('c'),
                'series' => $submit->task->series
            ];
        }
        return $data;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Submits per series'), 'fas fa-chart-line');
    }

    public function getDescription(): ?string
    {
        return _('Graph shows the cummulative number of submits per series in time before deadline.');
    }
}
