<?php

namespace OrgModule;

use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\OptimisticForm;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
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

        $this['inboxForm']->setDefaults();
    }

    protected function createComponentInboxForm($name) {
        $form = new OptimisticForm(
                array($this, 'inboxFormDataFingerprint'), array($this, 'inboxFormDefaultValues')
        );

        $contestants = $this->getContestants();
        $tasks = $this->getTasks();


        $container = $form->addContainer('contestants');

        foreach ($contestants as $contestant) {
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $contestant->getPerson()->getFullname());
            $control->setClassName('inbox');

            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, 'submit');
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'inboxFormSuccess');

        return $form;
    }

    public function inboxFormSuccess(Form $form) {
        $values = $form->getValues();

        $this->serviceSubmit->getConnection()->beginTransaction();

        foreach ($values['contestants'] as $container) {
            $submits = $container['submit'];

            foreach ($submits as $submit) {
                // ACL granularity is very rough, we just check it in action* method
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

    /**
     * @internal
     * @return array
     */
    public function inboxFormDefaultValues() {
        $submitsTable = $this->getSubmitsTable();
        $contestants = $this->getContestants();
        $result = array();
        foreach ($contestants as $contestant) {
            $ctId = $contestant->ct_id;
            if (isset($submitsTable[$ctId])) {
                $result[$ctId] = array('submit' => $submitsTable[$ctId]);
            } else {
                $result[$ctId] = array('submit' => null);
            }
        }
        return array(
            'contestants' => $result
        );
    }

    /**
     * @internal
     * @return array
     */
    public function inboxFormDataFingerprint() {
        $fingerprint = '';
        foreach ($this->getSubmitsTable() as $submits) {
            foreach ($submits as $submit) {
                $fingerprint .= $submit->getFingerprint();
            }
        }
        return md5($fingerprint);
    }

}
