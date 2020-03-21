<?php

namespace Events\Transitions;

use FKSDB\Authentication\AccountManager;
use Events\Machine\Transition;
use Events\Model\Holder\BaseHolder;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use Mail\MailTemplateFactory;
use Nette\SmartObject;

/**
 * Sends email notification of account creation
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class LoginInvitation {
    use SmartObject;
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

    /**
     * LoginInvitation constructor.
     * @param MailTemplateFactory $mailTemplateFactory
     * @param AccountManager $accountManager
     * @param ServicePerson $servicePerson
     */
    function __construct(MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, ServicePerson $servicePerson) {
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param Transition $transition
     * @throws \Exception
     */
    public function __invoke(Transition $transition) {
        $this->send($transition);
    }

    /**
     * @param Transition $transition
     * @throws \Exception
     */
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
            $login = $this->accountManager->createLoginWithInvitation($person, $email);
        }
    }

    /**
     * @param BaseHolder $baseHolder
     * @return AbstractModelSingle|null|ModelPerson
     */
    private function getPerson(BaseHolder $baseHolder) {
        return $this->servicePerson->findByPrimary($baseHolder->getPersonId());
    }

}
