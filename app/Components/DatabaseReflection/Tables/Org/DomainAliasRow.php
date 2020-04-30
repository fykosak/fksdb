<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EmailPrinter;
use FKSDB\Exceptions\ContestNotFoundException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelOrg;
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
     * @return Html
     * @throws ContestNotFoundException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        switch ($model->contest_id) {
            case ModelContest::ID_FYKOS:
                return (new EmailPrinter)($model->domain_alias . '@fykos.cz');
            case ModelContest::ID_VYFUK:
                return (new EmailPrinter)($model->domain_alias . '@vyfuk.mff.cuni.cz');
            default:
                throw new ContestNotFoundException($model->contest_id);
        }
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED);
        $control->addRule(Form::PATTERN, _('%l obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');
        return $control;
    }
}
