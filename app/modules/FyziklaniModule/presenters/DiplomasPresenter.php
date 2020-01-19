<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\FinalResults;
use FKSDB\model\Fyziklani\CloseStrategy;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class DiplomasPresenter
 * @package FyziklaniModule
 */
class DiplomasPresenter extends BasePresenter {

    public function titleResults() {
        $this->setTitle(_('Final results'));
        $this->setIcon('fa fa-trophy');
    }

    public function titleDefault() {
        $this->setTitle(_('Close submitting'));
        $this->setIcon('fa fa-check');
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedResults() {
        $this->setAuthorized($this->isContestsOrgAllowed('fyziklani.diplomas', 'results'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizeDefault() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.diplomas', 'default'));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDefault() {
        $items = [];
        foreach (['A', 'B', 'C'] as $category) {
            $items[$category] = [
                'closed' => $this->getServiceFyziklaniTeam()
                    ->findParticipating($this->getEvent())
                    ->where('category', $category)
                    ->where('points IS NOT NULL')
                    ->count(),
                'opened' => $this->getServiceFyziklaniTeam()
                    ->findParticipating($this->getEvent())
                    ->where('category', $category)
                    ->where('points IS NULL')
                    ->count(),
            ];
        }
        $this->template->items = $items;
    }

    /**
     * @param string|null $category
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleCloseAll(string $category = null) {
        $closeStrategy = new CloseStrategy($this->getEvent(), $this->getServiceFyziklaniTeam());
        $log = $closeStrategy($category);
        $this->flashMessage(Html::el()->addHtml(Html::el('h3')->addHtml('Rankin has been saved.'))->addHtml(Html::el('ul')->addHtml($log)), \BasePresenter::FLASH_SUCCESS);
        $this->redirect('this');
    }

    /**
     * @param string $category
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    public function isReadyAllToClose(string $category = null): bool {
        return $this->getServiceFyziklaniTeam()->isCategoryReadyForClosing($this->getEvent(), $category);
    }

    /**
     * @return FinalResults
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentResults(): FinalResults {
        return new FinalResults($this->getEvent(), $this->getServiceFyziklaniTeam(), $this->translator, $this->getTableReflectionFactory());
    }
}
