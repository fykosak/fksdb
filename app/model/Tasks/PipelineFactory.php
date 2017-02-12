<?php

namespace Tasks;

use FKS\Logging\MemoryLogger;
use Nette\InvalidStateException;
use Pipeline\Pipeline;
use ServicePerson;
use ServiceStudyYear;
use ServiceTask;
use ServiceTaskContribution;
use ServiceTaskStudyYear;

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
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * @var ServiceTaskContribution
     */
    private $serviceTaskContribution;

    /**
     * @var ServiceTaskStudyYear
     */
    private $serviceTaskStudyYear;

    /**
     * @var ServiceStudyYear
     */
    private $serviceStudyYear;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct($columnMappings, $contributionMappings, $defaultStudyYears, ServiceTask $serviceTask, ServiceTaskContribution $serviceTaskContribution, ServiceTaskStudyYear $serviceTaskStudyYear, ServiceStudyYear $serviceStudyYear, ServicePerson $servicePerson) {
        $this->columnMappings = $columnMappings;
        $this->contributionMappings = $contributionMappings;
        $this->defaultStudyYears = $defaultStudyYears;
        $this->serviceTask = $serviceTask;
        $this->serviceTaskContribution = $serviceTaskContribution;
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
        $this->serviceStudyYear = $serviceStudyYear;
        $this->servicePerson = $servicePerson;
    }

    /**
     * 
     * @param string $language ISO 639-1
     * @return \Pipeline\Pipeline
     * @throws InvalidStateException
     */
    public function create($language) {
        if (!array_key_exists($language, $this->columnMappings)) {
            throw new InvalidStateException("Missing mapping specification for language '$language'.");
        }

        $pipeline = new Pipeline();
        $pipeline->setLogger(new MemoryLogger());

        // common stages
        $metadataStage = new TasksFromXML($this->columnMappings[$language], $this->serviceTask);
        $pipeline->addStage($metadataStage);

        // NOTE: There's no need to store content of the tasks in the database.
        // language customizations
        switch ($language) {
            case 'cs':
                $this->appendCzech($pipeline);
                break;
            default:
                break;
        }

        return $pipeline;
    }

    protected function appendCzech(Pipeline $pipeline) {
        $deadlineStage = new DeadlineFromXML($this->serviceTask);
        $pipeline->addStage($deadlineStage);

        $contributionStage = new ContributionsFromXML($this->contributionMappings, $this->serviceTaskContribution, $this->servicePerson);
        $pipeline->addStage($contributionStage);

        $studyYearStage = new StudyYearsFromXML($this->defaultStudyYears, $this->serviceTaskStudyYear, $this->serviceStudyYear);
        $pipeline->addStage($studyYearStage);
    }

}
