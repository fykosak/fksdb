<?php

namespace Tasks;

use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\ORM\Services\ServiceStudyYear;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\ORM\Services\ServiceTaskStudyYear;
use FKSDB\ORM\Services\ServiceQuest;
use Pipeline\Pipeline;
use Tasks\Legacy\ContributionsFromXML;
use Tasks\Legacy\DeadlineFromXML;
use Tasks\Legacy\StudyYearsFromXML;
use Tasks\Legacy\TasksFromXML;

/**
 * This is not real factory, it's only used as an internode for defining
 * pipelines inside Neon and inject them into presenters at once.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PipelineFactory {

    /**
     * @see TasksFromXML
     * @var array
     */
    private $columnMappings;

    /**
     * @see ContributionsFromXML
     * @var array
     */
    private $contributionMappings;

    /**
     * @see StudyYearsFromXML
     * @var array
     */
    private $defaultStudyYears;
    
    /**
     * 
     * @var \FKSDB\ORM\Services\ServiceQuest
     */
    private $serviceQuest;

    /**
     * @var \FKSDB\ORM\Services\ServiceTask
     */
    private $serviceTask;

    /**
     * @var \FKSDB\ORM\Services\ServiceTaskContribution
     */
    private $serviceTaskContribution;

    /**
     * @var ServiceTaskStudyYear
     */
    private $serviceTaskStudyYear;

    /**
     * @var \FKSDB\ORM\Services\ServiceStudyYear
     */
    private $serviceStudyYear;

    /**
     * @var \FKSDB\ORM\Services\ServiceOrg
     */
    private $serviceOrg;

    /**
     * PipelineFactory constructor.
     * @param $columnMappings
     * @param $contributionMappings
     * @param $defaultStudyYears
     * @param \FKSDB\ORM\Services\ServiceQuest $serviceQuest
     * @param \FKSDB\ORM\Services\ServiceTask $serviceTask
     * @param ServiceTaskContribution $serviceTaskContribution
     * @param ServiceTaskStudyYear $serviceTaskStudyYear
     * @param ServiceStudyYear $serviceStudyYear
     * @param \FKSDB\ORM\Services\ServiceOrg $serviceOrg
     */
    function __construct($columnMappings, $contributionMappings, $defaultStudyYears, ServiceQuest $questService, ServiceTask $serviceTask, ServiceTaskContribution $serviceTaskContribution, ServiceTaskStudyYear $serviceTaskStudyYear, ServiceStudyYear $serviceStudyYear, ServiceOrg $serviceOrg) {
        $this->columnMappings = $columnMappings;
        $this->contributionMappings = $contributionMappings;
        $this->defaultStudyYears = $defaultStudyYears;
        $this->questService = $questService;
        $this->serviceTask = $serviceTask;
        $this->serviceTaskContribution = $serviceTaskContribution;
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
        $this->serviceStudyYear = $serviceStudyYear;
        $this->serviceOrg = $serviceOrg;
    }

    /**
     *
     * @param $language
     * @return \Pipeline\Pipeline
     */
    public function create($language) {
        $pipeline = new Pipeline();
        $pipeline->setLogger(new MemoryLogger());


        // common stages
        $metadataStage = new TasksFromXML($this->columnMappings[$language], $this->serviceTask, $this->serviceQuest);
        $pipeline->addStage($metadataStage);

        if ($language == 'cs') {
            $deadlineStage = new DeadlineFromXML($this->serviceTask);
            $pipeline->addStage($deadlineStage);

            $contributionStage = new ContributionsFromXML($this->contributionMappings, $this->serviceTaskContribution, $this->serviceOrg);
            $pipeline->addStage($contributionStage);

            $studyYearStage = new StudyYearsFromXML($this->defaultStudyYears, $this->serviceTaskStudyYear, $this->serviceStudyYear);
            $pipeline->addStage($studyYearStage);
        }

        return $pipeline;
    }

    /**
     *
     * @return \Pipeline\Pipeline
     */
    public function create2() {
        $pipeline = new Pipeline();
        $pipeline->setLogger(new MemoryLogger());

        // common stages
        $metadataStage = new TasksFromXML2($this->serviceTask, $this->serviceQuest);
        $pipeline->addStage($metadataStage);

        $deadlineStage = new DeadlineFromXML2($this->serviceTask);
        $pipeline->addStage($deadlineStage);

        $contributionStage = new ContributionsFromXML2($this->serviceTaskContribution, $this->serviceOrg);
        $pipeline->addStage($contributionStage);

        $studyYearStage = new StudyYearsFromXML2($this->defaultStudyYears, $this->serviceTaskStudyYear, $this->serviceStudyYear);
        $pipeline->addStage($studyYearStage);

        return $pipeline;
    }

}
