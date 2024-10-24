<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts;

use DateTime;
use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\ORM\Services\TaskService;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class SubmitsPerSeriesChart extends FrontEndComponent implements Chart
{
    private SubmitService $submitService;
    private TaskService $taskService;
    private ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container, 'chart.submits.per-series');
        $this->contestYear = $contestYear;
    }

    final public function injectService(SubmitService $submitService, TaskService $taskService): void
    {
        $this->submitService = $submitService;
        $this->taskService = $taskService;
    }

    /**
     * @phpstan-return array{deadlines: array<int,string>, submits: array<int, array{submittedOn:string, series:int}>}
     */
    public function getData(): array
    {
        $data = [
            'deadlines' => [],
            'submits' => [],
        ];

        $deadlineQuery = $this->taskService->getTable()
            ->select('series, MAX(submit_deadline) AS deadline')
            ->where('contest_id', $this->contestYear->contest_id)
            ->where('year', $this->contestYear->year)
            ->group('series')
            ->order('series');

        /** @var object{series:int, deadline: DateTime} $task*/
        foreach ($deadlineQuery as $task) {
            $data['deadlines'][$task->series] = $task->deadline->format('c');
        }

        $query = $this->submitService->getTable()
            ->where('task.contest_id', $this->contestYear->contest_id)
            ->where('task.year', $this->contestYear->year)
            ->order('task.series, submitted_on');

        /** @var SubmitModel $submit */
        foreach ($query as $submit) {
            $data['submits'][] = [
                'submittedOn' => $submit->submitted_on->format('c'),
                'series' => $submit->task->series
            ];
        }
        return $data;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Submits per series'), 'fas fa-chart-line');
    }

    public function getDescription(): string
    {
        return _('Graph shows the cumulative number of submits per series in time before deadline.');
    }
}
