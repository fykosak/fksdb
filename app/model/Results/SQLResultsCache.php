<?php

namespace FKSDB\Results;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceTask;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;

/**
 * Fill calculated points into database.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SQLResultsCache {

    private Connection $connection;

    private ServiceTask $serviceTask;

    /**
     * FKSDB\Results\SQLResultsCache constructor.
     * @param Connection $connection
     * @param ServiceTask $serviceTask
     */
    public function __construct(Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     */
    public function invalidate(ModelContest $contest = null, $year = null): void {
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

        $this->connection->query($sql, $data);
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @throws BadRequestException
     */
    public function recalculate(ModelContest $contest, int $year): void {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined evaluation strategy for ' . $contest->name . '@' . $year);
        }

        $tasks = $this->serviceTask->getTable()
            ->where([
                'contest_id' => $contest->contest_id,
                'year' => $year,
            ]);


        $this->connection->beginTransaction();
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

            $this->connection->query($sql);
        }
        $this->connection->commit();
    }

    /**
     * Calculate points from form-based tasks, such as quizzes.
     *
     * @param ModelContest $contest
     * @param int $year
     * @param int $series
     */
    public function calculateQuizPoints(ModelContest $contest, int $year, int $series): void {
        $params = [];
        $params[] = 'contest_id=' . $contest->contest_id;
        $params[] = 'year=' . $year;
        $params[] = 'series=' . $series;
        $sql = 'UPDATE submit s, (SELECT sq.ct_id, sq.question_id, sq.answer, q.task_id, q.points,
        q.answer AS "corr_answer", t.contest_id, t.year, t.series,
        SUM(IF(sq.answer=q.answer, q.points, 0)) AS "total" FROM submit_quiz sq
        JOIN quiz q USING (question_id) JOIN task t USING (task_id)
        WHERE t.' . implode(' AND t.', $params) . ' GROUP BY task_id, ct_id
        ) as T SET s.raw_points = T.total
        WHERE T.' . implode(' AND T.', $params) . ' AND s.ct_id = T.ct_id AND s.task_id = T.task_id';
        $this->connection->query($sql);
    }
}
