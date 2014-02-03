<?php

namespace Events\FormAdjustments;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use FKS\Components\Forms\Controls\CaptchaBox;
use FormUtils;
use Nette\Forms\Form;
use Nette\Object;
use Nette\Security\User;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editations).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Captcha extends Object implements IFormAdjustment {

    const CONTROL_NAME = 'c_a_p_t_cha';

    /**
     * @var User
     */
    private $user;

    function __construct(User $user) {
        $this->user = $user;
    }

        public function adjust(Form $form, Machine $machine, Holder $holder) {
        if ($machine->getPrimaryMachine()->getState() != BaseMachine::STATE_INIT || $this->user->isLoggedIn()) {
            return;
        }

        $control = new CaptchaBox();

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

}
