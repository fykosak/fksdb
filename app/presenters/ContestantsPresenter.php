<?php

class ContestantsPresenter extends AuthenticatedPresenter {

    /**
     * @var str
     * @persistent
     */
    public $backlink = '';

    protected function createComponentContestantWizard($name) {
        $wizard = new WizardCreateContestant($this, $name);
        $wizard->onProcess[] = array($this, 'processWizard');

        return $wizard;
    }

    public function processWizard(WizardCreateContestant $wizard) {
        $servicePerson = $this->getService('ServicePerson');
        $connection = $servicePerson->getConnection();

        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            // create person
            $person = $wizard->getPerson();
            $servicePerson->save($person);


            // update post contacts
            // TODO
            $serviceAddress = $this->getService('ServiceAddress');
            $dataAddress = $wizard->getData(WizardCreateContestant::STEP_POST_CONTACTS);
            
            $serviceContact = $this->getService('ServiceMPostContact');
            $postContact = $serviceContact->createNew(FormUtils::emptyStrToNull($dataAddress));
            $postContact->getPostContact()->person_id = $person->person_id;
            $postContact->getPostContact()->type = 'P';
            
            $serviceContact->save($postContact);
            
            // create contestant
            $serviceContestant = $this->getService('ServiceContestant');
            $dataContestant = $wizard->getData(WizardCreateContestant::STEP_CREATE_CONTESTANT);
            
            $contestant = $serviceContestant->createNew(FormUtils::emptyStrToNull($dataContestant));

            $contestant->person_id = $person->person_id;
            $contestant->contest_id = $this->getSelectedContest()->contest_id;
            $contestant->year = $this->getSelectedYear();

            $serviceContestant->save($contestant);


            // create login
            $dataLogin = $wizard->getData(WizardCreateContestant::STEP_CREATE_LOGIN);
            if ($dataLogin) {
                $serviceLogin = $this->getService('ServiceLogin');
                $login = $serviceLogin->createNew(FormUtils::emptyStrToNull($dataLogin));

                $login->person_id = $person->person_id;
                $login->created = NDateTime53::from(time());

                $serviceLogin->save($login);
                //TODO reset pwd & send notification
            }

            // store personal info
            $dataPersonInfo = $wizard->getData(WizardCreateContestant::STEP_PERSON_INFO);
            if ($dataPersonInfo) {
                $servicePersonInfo = $this->getService('ServicePersonInfo');
                $personInfo = $servicePersonInfo->createNew(FormUtils::emptyStrToNull($dataPersonInfo));

                $personInfo->person_id = $person->person_id;

                $servicePersonInfo->save($personInfo);
            }


            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage($person->gender == 'F' ? 'Řešitelka úspěšně založena.' : 'Řešitel úspěšně založen.');
            $this->restoreRequest($this->backlink);
            $this->redirect('Contestants:default');
        } catch (ModelException $e) {
            $connection->rollBack();
            $this->flashMessage($person->gender == 'F' ? 'Řešitel nebyl založen, došlo k chybě.' : 'Řešitelka nebyla založena, došlo k chybě.', 'error');
            $this->restoreRequest($this->backlink);
            $this->redirect('Contestants:default');
        }
    }

}
