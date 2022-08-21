<?php

declare(strict_types=1);

namespace FKSDB\Models\Results;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskService;
use Nette\Application\BadRequestException;
use Nette\InvalidArgumentException;

/**
 * Fill calculated points into database.
 */
class SQLResultsCache
{

    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function invalidate(ContestYearModel $contestYear): void
    {
        $data = [
            'calc_points' => null,
        ];
        $conditions[] = 'contest_id = ' . $contestYear->contest_id;
        $conditions[] = 'year = ' . $contestYear->year;

        $sql = '
            UPDATE submit s
            LEFT JOIN task t ON t.task_id = s.task_id
            SET ?
            WHERE (' . implode(') and (', $conditions) . ')';

        $this->taskService->explorer->query($sql, $data);
    }

    /**
     * @throws BadRequestException
     * @throws \PDOException
     */
    public function recalculate(ContestYearModel $contestYear): void
    {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contestYear);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException(
                'Undefined evaluation strategy for ' . $contestYear->contest->name . '@' . $contestYear->year
            );
        }
        $tasks = $contestYear->contest->related(DbNames::TAB_TASK)->where('year', $contestYear->year);

        $this->taskService->explorer->getConnection()->beginTransaction();
        /** @var TaskModel $task */
        foreach ($tasks as $task) {
            $conditions = [];
            $conditions[] = 't.contest_id = ' . $contestYear->contest->contest_id;
            $conditions[] = 't.year = ' . $contestYear->year;
            $conditions[] = 's.task_id = ' . $task->task_id;
            $sql = '
            UPDATE submit s
            LEFT JOIN task t ON s.task_id = s.task_id
            LEFT JOIN v_contestant ct ON ct.contestant_id = s.contestant_id
            SET calc_points = (
                SELECT ' . $evaluationStrategy->getPointsColumn($task) . '
                FROM dual
            )
            WHERE (' . implode(') and (', $conditions) . ')';

            $this->taskService->explorer->query($sql);
        }
        $this->taskService->explorer->getConnection()->commit();
    }

    /**
     * Calculate points from form-based tasks, such as quizzes.
     */
    public function calculateQuizPoints(ContestYearModel $contestYear, int $series): void
    {
        $params = [];
        $params[] = 'contest_id=' . $contestYear->contest_id;
        $params[] = 'year=' . $contestYear->year;
        $params[] = 'series=' . $series;

        $sql = 'UPDATE submit s INNER JOIN (SELECT sq.contestant_id, q.task_id, SUM(IF(sq.answer=q.answer, q.points, 0))
                AS "raw_points" FROM submit_quiz sq JOIN quiz q USING (question_id) JOIN task t USING (task_id)
                WHERE t.' . implode(' AND t.', $params) .
            ' GROUP BY contestant_id, task_id ) r ON s.contestant_id = r.contestant_id AND
                s.task_id = r.task_id SET s.raw_points = r.raw_points, s.calc_points = r.raw_points';

        $this->taskService->explorer->query($sql);
    }
}
