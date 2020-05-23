<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Grids\Fyziklani\ResultsCategoryGrid;
use FKSDB\Components\Grids\Fyziklani\ResultsTotalGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\DI\Container;
use Nette\Templating\FileTemplate;

/**
 * Class OrgResults
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class FinalResults extends BaseComponent {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FinalResults constructor.
     * @param ModelEvent $event
     * @param Container $container
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @return void
     */
    public function injectPrimary(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @param string $category
     * @return bool
     */
    public function isClosedCategory(string $category): bool {
        $count = (int)$this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('category', $category)
            ->where('rank_category IS NULL')
            ->count();
        return $count === 0;
    }

    /**
     * @return bool
     */
    public function isClosedTotal(): bool {
        $count = (int)$this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('rank_total IS NULL')
            ->count();
        return $count === 0;
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryAGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, 'A', $this->container);
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryBGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, 'B', $this->container);
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryCGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, 'C', $this->container);
    }

    /**
     * @return ResultsTotalGrid
     */
    public function createComponentResultsTotalGrid(): ResultsTotalGrid {
        return new ResultsTotalGrid($this->event, $this->container);
    }

    /**
     * @return void
     */
    public function render() {
        $this->template->that = $this;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'FinalResults.latte');
        $this->template->render();
    }
}
