<?php

namespace Events\FormAdjustments;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FormUtils;
use Nette\Forms\Form;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editations).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Captcha implements IFormAdjustment {

    use SmartObject;

    const CONTROL_NAME = 'c_a_p_t_cha';

    /**
     * @var User
     */
    private $user;

    /**
     * Captcha constructor.
     * @param User $user
     */
    function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * @param Form $form
     * @param Machine $machine
     * @param Holder $holder
     */
    public function adjust(Form $form, Machine $machine, Holder $holder) {
        if ($machine->getPrimaryMachine()->getState() != BaseMachine::STATE_INIT || $this->user->isLoggedIn()) {
            return;
        }

        $control = new CaptchaBox();

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }

}
