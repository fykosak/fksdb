<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Org;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\EmailPrinter;
use FKSDB\Models\Exceptions\ContestNotFoundException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelOrg;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class DomainAliasRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DomainAliasRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @return Html
     * @throws ContestNotFoundException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        switch ($model->contest_id) {
            case ModelContest::ID_FYKOS:
                return (new EmailPrinter())($model->domain_alias . '@fykos.cz');
            case ModelContest::ID_VYFUK:
                return (new EmailPrinter())($model->domain_alias . '@vyfuk.mff.cuni.cz');
            default:
                throw new ContestNotFoundException($model->contest_id);
        }
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addRule(Form::MAX_LENGTH, null, 32);
        $control->addCondition(Form::FILLED);
        $control->addRule(Form::PATTERN, sprintf(_('%s obsahuje nepovolené znaky.'), $this->getTitle()), '[a-z][a-z0-9._\-]*');
        return $control;
    }
}
