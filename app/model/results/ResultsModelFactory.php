<?php

/**
 * Description of ResultsModelFactory
 *
 * @author michal
 */
class ResultsModelFactory {

    /**
     * @var \Nette\Database\Connection
     */
    private $connection;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    public function __construct(\Nette\Database\Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    /**
     * 
     * @param ModelContest $contest
     * @param int $year
     * @return IResultsModel
     */
    public function createCumulativeResultsModel(ModelContest $contest, $year) {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new Nette\InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new CumulativeResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     * 
     * @param ModelContest $contest
     * @param int $year
     * @return IResultsModel
     */
    public function createDetailResultsModel(ModelContest $contest, $year) {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new Nette\InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new DetailResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     * 
     * @param ModelContest $contest
     * @param int $year
     * @return IResultsModel
     */
    public function createBrojureResultsModel(ModelContest $contest, $year) {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new Nette\InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new BrojureResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    public static function findEvaluationStrategy(ModelContest $contest, $year) {
        if ($contest->contest_id == ModelContest::ID_FYKOS) {
            if ($year >= 25) {
                return new EvaluationFykos2011();
            } else {
                return new EvaluationFykos2001();
            }
        } else if ($contest->contest_id == ModelContest::ID_VYFUK) {
            if($year >= 2) {
                return new EvaluationVyfuk2012();
            } else {
                return new EvaluationVyfuk2011();
            }
        }
        return null;
    }

}

?>
