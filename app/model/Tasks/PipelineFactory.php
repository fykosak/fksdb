<?php

namespace Tasks;

use Nette\InvalidStateException;
use Pipeline\Pipeline;
use ServiceOrg;
use ServiceTask;
use ServiceTaskContribution;

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
     * @var ServiceTask
     */
    private $taskService;

    /**
     * @var ServiceTaskContribution
     */
    private $taskContributionService;

    /**
     * @var ServiceOrg
     */
    private $orgService;

    public function __construct($columnMappings, $contributionMappings, ServiceTask $taskService, ServiceTaskContribution $taskContributionService, ServiceOrg $orgService) {
        $this->columnMappings = $columnMappings;
        $this->contributionMappings = $contributionMappings;
        $this->taskService = $taskService;
        $this->taskContributionService = $taskContributionService;
        $this->orgService = $orgService;
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

        // common stages
        $metadataStage = new TasksFromXML($this->columnMappings[$language], $this->taskService);
        $pipeline->addStage($metadataStage);

        //TODO data stage (content)
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
        $deadlineStage = new DeadlineFromXML($this->taskService);
        $pipeline->addStage($deadlineStage);

        $contributionStage = new ContributionsFromXML($this->contributionMappings, $this->taskContributionService, $this->orgService);
        $pipeline->addStage($contributionStage);
    }

}
