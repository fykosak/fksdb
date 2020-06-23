<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class TexSignatureRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TexSignatureRow extends AbstractOrgRowFactory {

    public function getTitle(): string {
        return _('Tex signature');
    }

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
        $control->addCondition(Form::FILLED)
            ->addRule(Form::PATTERN, sprintf(_('%s obsahuje nepovolené znaky.'), $this->getTitle()), '[a-z][a-z0-9._\-]*');
        return $control;
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
