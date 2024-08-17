<?php

declare(strict_types=1);

namespace FKSDB\Components\Inbox\PointsVariance;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

final class ChartComponent extends FrontEndComponent
{
    private ContestYearModel $contestYear;
    private int $series;

    public function __construct(Container $container, ContestYearModel $contestYear, int $series)
    {
        parent::__construct($container, 'points-variance-chart');
        $this->contestYear = $contestYear;
        $this->series = $series;
    }

    /**
     * @phpstan-return array<string,array<int,float>>
     */
    protected function getData(): array
    {
        $tasks = $this->contestYear->getTasks($this->series);
        $data = [];
        /** @var TaskModel $task */
        foreach ($tasks as $task) {
            $max = $task->points;
            $datum = [];
            /** @var SubmitModel $submit */
            foreach ($task->getSubmits() as $submit) {
                if (isset($submit->raw_points)) {
                    $datum[] = $submit->raw_points / $max;
                }
            }
            $data[$task->label] = $datum;
        }
        return $data;
    }
}
