<?php

use \Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

/**
 * Description of PersonPresenter
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class PersonPresenter extends AuthenticatedPresenter {

    const CONT_PERSON = 'person';
    const CONT_LOGIN = 'login';

    public function actionRegister() {
        
    }

    public function createComponentFormPublicRegistration($name) {
        $form = new Form($this, $name);

        $form->addComponent(new FormContainerPerson(), self::CONT_PERSON);
        $form->addComponent(new FormContainerLogin(FormContainerLogin::PWD_NEW_VERIFIED), self::CONT_LOGIN);

        $form->addSubmit('send', 'Odeslat');
        $form->onSuccess[] = array($this, 'formPublicRegistrationSuccess');
    }

    public function formPublicRegistrationSuccess($form) {
        $data = $form->getValues();

        $servicePerson = $this->getService('ServicePerson');
        $serviceLogin = $this->getService('ServiceLogin');

        $connection = $servicePerson->getConnection();

        $person = $servicePerson->createNew($data[self::CONT_PERSON]);
        $login = $serviceLogin->createNew($data[self::CONT_LOGIN]);

        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            // store person
            $person->inferGender();
            $servicePerson->save($person);

            // store login
            $login->person_id = $person->person_id;
            $login->active = 1;
            $login->setHash($data[self::CONT_LOGIN]['password']);
            $serviceLogin->save($login);


            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage('Účet zaregistrován.');


            $this->redirect('Dashboard:default'); //TODO redirect to login/home page
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Účet nebyl registrován. Pravděpodobně kolize loginu nebo e-mailu.', 'error');
            $this->redirect('Dashboard:default'); //TODO redirect to login/home page
        }
    }

}

?>
