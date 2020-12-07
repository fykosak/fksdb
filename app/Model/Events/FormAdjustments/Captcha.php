<?php

namespace FKSDB\Model\Events\FormAdjustments;

use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Model\Events\Machine\BaseMachine;
use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Utils\FormUtils;
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

    protected const CONTROL_NAME = 'c_a_p_t_cha';

    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function adjust(Form $form, Machine $machine, Holder $holder): void {
        if ($holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT || $this->user->isLoggedIn()) {
            return;
        }
        $control = new CaptchaBox();

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }
}
