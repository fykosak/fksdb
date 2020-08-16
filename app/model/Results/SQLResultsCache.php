<?php

namespace FKSDB\Results;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Services\ServiceQuizQuestion;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceSubmitQuizQuestion;
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

    private ServiceQuizQuestion $serviceQuizQuestion;

    private ServiceSubmit $serviceSubmit;

    private ServiceSubmitQuizQuestion $serviceSubmitQuizQuestion;

    /**
     * FKSDB\Results\SQLResultsCache constructor.
     * @param Connection $connection
     * @param ServiceTask $serviceTask
     * @param ServiceQuizQuestion $serviceQuizQuestion
     * @param ServiceSubmit $serviceSubmit
     * @param ServiceSubmitQuizQuestion $serviceSubmitQuizQuestion
     */
    public function __construct(Connection $connection, ServiceTask $serviceTask, ServiceQuizQuestion $serviceQuizQuestion, ServiceSubmit $serviceSubmit, ServiceSubmitQuizQuestion $serviceSubmitQuizQuestion) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
        $this->serviceQuizQuestion = $serviceQuizQuestion;
        $this->serviceSubmit = $serviceSubmit;
        $this->serviceSubmitQuizQuestion = $serviceSubmitQuizQuestion;
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
    public function recalculate(ModelContest $contest, $year): void {
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
        $tasks = $this->serviceTask->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            'series' => $series,
        ]);
        foreach($tasks as $task) {
            $questions = $this->serviceQuizQuestion->getTable()->where([
                'task_id' => $task->task_id,
            ]);
            if (count($questions) == 0){
                continue;
            }
            $submits = $this->serviceSubmit->getTable()->where([
                'task_id' => $task->task_id,
            ]);
            foreach($submits as $submit) {
                $total = 0;
                foreach($questions as $question) {
                    $answer = $this->serviceSubmitQuizQuestion->findByContestant($submit->ct_id, $question->question_id)->answer;
                    $correct = $question->answer;
                    $points = $question->points;
                    if($answer == $correct) {
                        $total += $points;
                    }
                }
                $this->serviceSubmit->updateModel2($submit, ['raw_points' => +$total]);
            }
        }
    }

}
