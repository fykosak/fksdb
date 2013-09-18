<?php

namespace OrgModule;

use FKSDB\Components\Forms\Controls\ContestantSubmits;
use ModelSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Security\Permission;
use ServiceSubmit;

class InboxPresenter extends TaskTimesContestantPresenter {

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    public function injectSubmitService(ServiceSubmit $submitService) {
        $this->serviceSubmit = $submitService;
    }

    public function actionDefault() {
        if (!$this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function renderDefault() {
        $this->template->contestants = $this->getContestants();
        $this->template->tasks = $this->getTasks()->fetchPairs('task_id');
    }

    protected function createComponentInboxForm($name) {
        $form = new Form($this, $name); //TODO use OptimisticForm

        $contestants = $this->getContestants();
        $tasks = $this->getTasks();
        $submitsTable = $this->getSubmitsTable();

        $container = $form->addContainer('contestants');

        foreach ($contestants as $contestant) {
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $contestant->getPerson()->getFullname());
            $control->setValue(isset($submitsTable[$contestant->ct_id]) ? $submitsTable[$contestant->ct_id] : null);
            $control->setClassName('inbox');

            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, 'submit');
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'inboxFormSuccess');
    }

    public function inboxFormSuccess(Form $form) {
        $values = $form->getValues();

        $this->serviceSubmit->getConnection()->beginTransaction();

        foreach ($values['contestants'] as $container) {
            $submits = $container['submit'];

            foreach ($submits as $submit) {
                //TODO práva??
                if ($submit->isEmpty()) {
                    $this->serviceSubmit->dispose($submit);
                } else {
                    $this->serviceSubmit->save($submit);
                }
            }
        }
        $this->serviceSubmit->getConnection()->commit();
        $this->flashMessage('Informace o řešeních uložena.');
        $this->redirect('this');
    }

}
