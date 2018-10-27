<?php

namespace FKSDB\Components\Forms\Factories\Org;


use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

class OrderField extends SelectBox {
    public function __construct() {
        parent::__construct(_('Hodnost'));
        $this->setOption('description', _('Pro řazení v seznamu organizátorů'));
        $this->setItems([
            0 => '0 - org',
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4 - hlavní organizátor',
            9 => '9 - vedoucí semináře',
        ]);
        $this->setPrompt(_('Zvolit hodnost'));
        $this->addRule(Form::FILLED, _('Vyberte hodnost.'));
    }
}
