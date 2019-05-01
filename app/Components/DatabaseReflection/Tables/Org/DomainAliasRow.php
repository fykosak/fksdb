<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EmailPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class DomainAliasRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class DomainAliasRow extends AbstractOrgRowFactory {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Domain alias');
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @param string $fieldName
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        switch ($model->contest_id) {
            case 1:
                return (new EmailPrinter)($model->domain_alias . '@fykos.cz');
            case 2:
                return (new EmailPrinter)($model->domain_alias . '@vyfuk.mff.cuni.cz');
            default:
                throw new BadRequestException();
        }
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED);
        $control->addRule(Form::REGEXP, _('%l obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');
        return $control;
    }
}
