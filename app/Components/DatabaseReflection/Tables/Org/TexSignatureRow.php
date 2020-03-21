<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * Class TexSignatureRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class TexSignatureRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Tex signature');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'tex_signature';
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new TextInput($this->getTitle());

        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED);
        $control->addRule(Form::REGEXP, _('%label obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');
        return $control;
    }
}
