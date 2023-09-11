<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\SubmitService;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Template;
use Nette\DI\Container;

abstract class SeriesTableComponent extends BaseComponent
{
    protected SubmitService $submitService;
    private bool $displayAll;
    protected ContestYearModel $contestYear;
    protected int $series;
    /**
     * @phpstan-var (callable(TypedGroupedSelection<TaskModel>):void)|null
     */
    public $taskFilter = null;

    /**
     * @phpstan-param (callable(TypedGroupedSelection<TaskModel>):void)|null $taskFilter
     */
    public function __construct(
        Container $context,
        ContestYearModel $contestYear,
        int $series,
        bool $displayAll = false,
        ?callable $taskFilter = null
    ) {
        parent::__construct($context);
        $this->displayAll = $displayAll;
        $this->contestYear = $contestYear;
        $this->taskFilter = $taskFilter;
        $this->series = $series;
    }

    public function injectSubmitService(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->submitService = $this->submitService;
        $template->displayAll = $this->displayAll;
        $template->contestYear = $this->contestYear;
        $template->lang = $this->translator->lang;
        $template->series = $this->series;
        return $template;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskModel>
     */
    final public function getTasks(): TypedGroupedSelection
    {
        $tasks = $this->contestYear->getTasks($this->series);
        if (isset($this->taskFilter)) {
            ($this->taskFilter)($tasks);
        }
        return $tasks->order('tasknr');
    }

    /**
     * @phpstan-return array<int,array<int,SubmitModel>>
     */
    final public function getSubmitsTable(): array
    {
        // store submits in 2D hash for better access
        $submitsTable = [];
        /** @var SubmitModel $submit */
        foreach ($this->submitService->getForContestYear($this->contestYear, $this->series) as $submit) {
            $submitsTable[$submit->contestant_id] = $submitsTable[$submit->contestant_id] ?? [];
            $submitsTable[$submit->contestant_id][$submit->task_id] = $submit;
        }
        return $submitsTable;
    }
}
