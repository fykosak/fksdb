<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

class CaptchaBox extends SelectBox {

    private const VALUE_YES = 'xyz';
    private const VALUE_NO = 'pqrt';

    public function __construct() {
        parent::__construct(_('Are you a robot?'), [
            self::VALUE_NO => _('No'),
            self::VALUE_YES => _('Yes'),
        ]);

        $this->addRule(function (BaseControl $control): bool {
            return $control->getValue() == self::VALUE_NO;
        }, _('This form is for people only.'));

        $this->setDefaultValue(self::VALUE_YES);
    }
}
