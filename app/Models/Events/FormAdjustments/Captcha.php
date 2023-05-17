<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Utils\FormUtils;
use Nette\Forms\Form;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Creates required checkbox for whole application and then
 * sets agreed bit in all person_info containers found (even for editations).
 */
class Captcha implements FormAdjustment
{
    use SmartObject;

    protected const CONTROL_NAME = 'c_a_p_t_cha';
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param BaseHolder $holder
     */
    public function adjust(Form $form, ModelHolder $holder): void
    {
        if ($holder->getModelState() != Machine::STATE_INIT || $this->user->isLoggedIn()) {
            return;
        }
        $control = new CaptchaBox();

        $firstSubmit = FormUtils::findFirstSubmit($form);
        $form->addComponent($control, self::CONTROL_NAME, $firstSubmit->getName());
    }
}
