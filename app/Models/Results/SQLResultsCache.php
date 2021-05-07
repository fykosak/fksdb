<?php

namespace FKSDB\Models\Results;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceTask;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;

/**
 * Fill calculated points into database.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SQLResultsCache {

    private ServiceTask $serviceTask;

    public function __construct(ServiceTask $serviceTask) {
        $this->serviceTask = $serviceTask;
    }

    /**
     *
     * @param ModelContest|null $contest
     * @param int|null $year
     * @throws \PDOException
     */
    public function invalidate(ModelContest $contest = null, ?int $year = null): void {
        $data = [
            'calc_points' => null,
        ];
        $conditions = ['1 = 1'];
        if ($contest !== null) {
            $conditions[] = 'contest_id = ' . $contest->contest_id;
        }
        if ($year !== null) {
            $conditions[] = 'year = ' . (int)$year;
        }

        $sql = '
            UPDATE submit s
            LEFT JOIN task t ON t.task_id = s.task_id
            SET ?
            WHERE (' . implode(') and (', $conditions) . ')';

        $this->serviceTask->explorer->query($sql, $data);
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @throws BadRequestException
     * @throws \PDOException
     */
    public function recalculate(ModelContest $contest, int $year): void {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined evaluation strategy for ' . $contest->name . '@' . $year);
        }
// TODO related
        $tasks = $this->serviceTask->getTable()
            ->where([
                'contest_id' => $contest->contest_id,
                'year' => $year,
            ]);

        $this->serviceTask->explorer->getConnection()->beginTransaction();
        /** @var ModelTask $task */
        foreach ($tasks as $task) {
            $conditions = [];
            $conditions[] = 't.contest_id = ' . $contest->contest_id;
            $conditions[] = 't.year = ' . (int)$year;
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
     *
     * @param ModelContest $contest
     * @param int $year
     * @param int $series
     * @throws \PDOException
     */
    public function calculateQuizPoints(ModelContest $contest, int $year, int $series): void {
        $params = [];
        $params[] = 'contest_id=' . $contest->contest_id;
        $params[] = 'year=' . $year;
        $params[] = 'series=' . $series;

        $sql = 'UPDATE submit s INNER JOIN (SELECT sq.ct_id, q.task_id, SUM(IF(sq.answer=q.answer, q.points, 0))
                AS "raw_points" FROM submit_quiz sq JOIN quiz q USING (question_id) JOIN task t USING (task_id)
                WHERE t.' . implode(' AND t.', $params) . ' GROUP BY ct_id, task_id ) r ON s.ct_id = r.ct_id AND
                s.task_id = r.task_id SET s.raw_points = r.raw_points';

        $this->serviceTask->explorer->query($sql);
    }
}
