<?php

use Nette\Diagnostics\Debugger;
use Nette\DateTime;

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

    protected function createComponentGridContestants($name) {
        $grid = new GridContestants();

        return $grid;
    }

    public function processWizard(WizardCreateContestant $wizard) {
        $servicePerson = $this->getService('ServicePerson');
        $connection = $servicePerson->getConnection();

        // create person
        $person = $wizard->getPerson();

        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }


            $servicePerson->save($person);


            // update post contacts
            $servicePostContact = $this->getService('ServiceMPostContact');

            $dataPostContacts = $wizard->getData(WizardCreateContestant::STEP_POST_CONTACTS);
            foreach ($dataPostContacts['post_contacts'] as $dataPostContact) {
                $postContact = $servicePostContact->createNew(FormUtils::emptyStrToNull((array) $dataPostContact));
                $postContact->getPostContact()->person_id = $person->person_id;
                if (!$postContact->getAddress()->inferRegion()) {
                    $this->flashMessage(sprintf('Nezdařilo se přiřadit region dle PSČ %s.', $postContact->getAddress()->postal_code));
                }
                //TODO mazat staré adresy nebo je přímo upravovat
                $servicePostContact->save($postContact);
            }



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
                $login->created = DateTime::from(time());

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
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage($person->gender == 'F' ? 'Řešitel nebyl založen, došlo k chybě.' : 'Řešitelka nebyla založena, došlo k chybě.', 'error');
            $this->restoreRequest($this->backlink);
            $this->redirect('Contestants:default');
        }
    }

}
