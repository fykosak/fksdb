<?php

use FKSDB\ORM\Models\ModelContest;

/**
 * Fill caclulated points into database.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SQLResultsCache {

    /**
     * @var \Nette\Database\Connection
     */
    private $connection;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * SQLResultsCache constructor.
     * @param \Nette\Database\Connection $connection
     * @param ServiceTask $serviceTask
     */
    function __construct(\Nette\Database\Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     */
    public function invalidate(ModelContest $contest = null, $year = null) {
        $data = [
            'calc_points' => null,
        ];
        $conditions = ['1 = 1'];
        if ($contest !== null) {
            $conditions[] = 'contest_id = ' . $contest->contest_id;
        }
        if ($year !== null) {
            $conditions[] = 'year = ' . (int) $year;
        }

        $sql = '
            UPDATE submit s
            LEFT JOIN task t ON t.task_id = s.task_id
            SET ?
            WHERE (' . implode(') and (', $conditions) . ')';

        $this->connection->exec($sql, $data);
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     */
    public function recalculate(ModelContest $contest, $year) {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new Nette\InvalidArgumentException('Undefined evaluation strategy for ' . $contest->name . '@' . $year);
        }

        $tasks = $this->serviceTask->getTable()
                ->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
                ]);


        $this->connection->beginTransaction();
        foreach ($tasks as $task) {
            $conditions = [];
            $conditions[] = 't.contest_id = ' . $contest->contest_id;
            $conditions[] = 't.year = ' . (int) $year;
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

            $this->connection->exec($sql);
        }
        $this->connection->commit();
    }

}


