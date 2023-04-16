<?php

declare(strict_types=1);

namespace FKSDB\Models\Results;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\SubmitService;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * Fill calculated points into database.
 */
class SQLResultsCache
{
    private SubmitService $submitService;
    private Container $container;

    public function __construct(SubmitService $submitService, Container $container)
    {
        $this->submitService = $submitService;
        $this->container = $container;
    }

    public function invalidate(ContestYearModel $contestYear): void
    {
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks() as $task) {
            /** @var SubmitModel $submit */
            foreach ($task->getSubmits() as $submit) {
                $this->submitService->storeModel(['calc_points' => null], $submit);
            }
        }
    }

    /**
     * @throws BadRequestException
     * @throws \PDOException
     */
    public function recalculate(ContestYearModel $contestYear): void
    {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->container, $contestYear);
        $this->submitService->explorer->getConnection()->beginTransaction();
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks() as $task) {
            /** @var SubmitModel $submit */
            foreach ($task->getSubmits() as $submit) {
                $this->submitService->storeModel(
                    [
                        'calc_points' => $evaluationStrategy->getSubmitPoints($submit),
                    ],
                    $submit
                );
            }
        }
        $this->submitService->explorer->getConnection()->commit();
    }

    /**
     * Calculate points from form-based tasks, such as quizzes.
     */
    public function calculateQuizPoints(ContestYearModel $contestYear, int $series): void
    {
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks()->where('series', $series) as $task) {
            /** @var SubmitModel $submit */
            foreach ($task->getSubmits() as $submit) {
                $sum = $submit->calculateQuestionSum();
                if (isset($sum)) {
                    $this->submitService->storeModel([
                        'raw_points' => $sum,
                        'corrected' => true,
                    ], $submit);
                }
            }
        }
        $this->recalculate($contestYear);
    }
}
