<?php

declare(strict_types=1);

namespace FKSDB\Models\Results;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\SubmitService;
use Nette\Application\BadRequestException;

/**
 * Fill calculated points into database.
 */
class SQLResultsCache
{
    private SubmitService $submitService;

    public function __construct(SubmitService $submitService)
    {
        $this->submitService = $submitService;
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
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contestYear);
        $this->submitService->explorer->getConnection()->beginTransaction();
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks() as $task) {
            /** @var SubmitModel $submit */
            foreach ($task->getSubmits() as $submit) {
                $category = $evaluationStrategy->studyYearsToCategory(
                    $submit->contestant->getPersonHistory()->study_year
                );
                $this->submitService->storeModel(
                    ['calc_points' => $evaluationStrategy->getSubmitPoints($submit, $category)],
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
    }
}
