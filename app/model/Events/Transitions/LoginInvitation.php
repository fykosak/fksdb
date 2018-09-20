<?php

namespace Events\Transitions;

use Authentication\AccountManager;
use Events\Machine\Transition;
use Events\Model\Holder\BaseHolder;
use Mail\MailTemplateFactory;
use Nette\Object;
use ServicePerson;

/**
 * Sends email notification of account creation
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class LoginInvitation extends Object {

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct(MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, ServicePerson $servicePerson) {
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->servicePerson = $servicePerson;
    }

    public function __invoke(Transition $transition) {
        $this->send($transition);
    }

    private function send(Transition $transition) {
        $baseHolder = $transition->getBaseHolder();
        $person = $this->getPerson($baseHolder);

        if (!$person) {
            return;
        }
        $info = $person->getInfo();
        $email = $info ? $info->email : null;

        if (!$email) {
            return;
        }

        $login = $person->getLogin();
        if (!$login) {
            $template = $this->mailTemplateFactory->createLoginInvitation();
            $login = $this->accountManager->createLoginWithInvitation($template, $person, $email);
        }
    }

    private function getPerson(BaseHolder $baseHolder) {
        return $this->servicePerson->findByPrimary($baseHolder->getPersonId());
    }

}
