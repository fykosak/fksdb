<?php

namespace FKSDB\Models\Results;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceTask;
use Nette\Application\BadRequestException;
use Nette\InvalidArgumentException;

/**
 * Fill calculated points into database.
 */
class SQLResultsCache
{

    private ServiceTask $serviceTask;

    public function __construct(ServiceTask $serviceTask)
    {
        $this->serviceTask = $serviceTask;
    }

    public function invalidate(ModelContestYear $contestYear): void
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

        $this->serviceTask->explorer->query($sql, $data);
    }

    /**
     * @throws BadRequestException
     * @throws \PDOException
     */
    public function recalculate(ModelContestYear $contestYear): void
    {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contestYear);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException(
                'Undefined evaluation strategy for ' . $contestYear->getContest()->name . '@' . $contestYear->year
            );
        }
// TODO related
        $tasks = $this->serviceTask->getTable()
            ->where([
                'contest_id' => $contestYear->getContest()->contest_id,
                'year' => $contestYear->year,
            ]);

        $this->serviceTask->explorer->getConnection()->beginTransaction();
        /** @var ModelTask $task */
        foreach ($tasks as $task) {
            $conditions = [];
            $conditions[] = 't.contest_id = ' . $contestYear->getContest()->contest_id;
            $conditions[] = 't.year = ' . $contestYear->year;
            $conditions[] = 's.task_id = ' . $task->task_id;
            $sql = '
            UPDATE submit s
            LEFT JOIN task t ON s.task_id = s.task_id
            LEFT JOIN v_contestant ct ON ct.ct_id = s.ct_id
            SET calc_points = (
                SELECT ' . $evaluationStrategy->getPointsColumn($task) . '
                FROM dual
            )
            WHERE (' . implode(') and (', $conditions) . ')';

            $this->serviceTask->explorer->query($sql);
        }
        $this->serviceTask->explorer->getConnection()->commit();
    }

    /**
     * Calculate points from form-based tasks, such as quizzes.
     */
    public function calculateQuizPoints(ModelContestYear $contestYear, int $series): void
    {
        $params = [];
        $params[] = 'contest_id=' . $contestYear->contest_id;
        $params[] = 'year=' . $contestYear->year;
        $params[] = 'series=' . $series;

        $sql = 'UPDATE submit s INNER JOIN (SELECT sq.ct_id, q.task_id, SUM(IF(sq.answer=q.answer, q.points, 0))
                AS "raw_points" FROM submit_quiz sq JOIN quiz q USING (question_id) JOIN task t USING (task_id)
                WHERE t.' . implode(' AND t.', $params) . ' GROUP BY ct_id, task_id ) r ON s.ct_id = r.ct_id AND
                s.task_id = r.task_id SET s.raw_points = r.raw_points, s.calc_points = r.raw_points';

        $this->serviceTask->explorer->query($sql);
    }
}
