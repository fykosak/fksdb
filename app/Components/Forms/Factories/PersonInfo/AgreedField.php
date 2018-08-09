<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

class AgreedField extends Checkbox implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Souhlasím se zpracováním osobních údajů'));
        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->addAttributes(['href' => _("http://fykos.cz/doc/souhlas.pdf")]);
        $this->setOption('description', $link);
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
