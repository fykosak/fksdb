<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\PersonInfo;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class HealthInsuranceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class HealthInsuranceRow extends DefaultColumnFactory {
    protected const ID_MAPPING = [
        111 => '(111) Všeobecná zdravotní pojišťovna ČR',
        201 => '(201) Vojenská zdravotní pojišťovna ČR',
        205 => '(205) Česká průmyslová zdravotní pojišťovna',
        207 => '(207) Oborová zdravotní poj. zam. bank, poj. a stav.',
        209 => '(209) Zaměstnanecká pojišťovna Škoda',
        211 => '(211) Zdravotní pojišťovna ministerstva vnitra ČR',
        213 => '(213) Revírní bratrská pokladna, zdrav. pojišťovna',
        24 => '(24) DÔVERA zdravotná poisťovňa, a. s.',
        25 => '(25) VŠEOBECNÁ zdravotná poisťovňa, a. s.',
        27 => '(27) UNION zdravotná poisťovňa, a. s.',
    ];

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (\array_key_exists($model->health_insurance, self::ID_MAPPING)) {
            return Html::el('span')->addText(self::ID_MAPPING[$model->health_insurance]);
        }
        return Html::el('span')->addText($model->health_insurance);
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    protected function createFormControl(...$args): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setItems(self::ID_MAPPING);
        $control->setPrompt(_('Vybete zdravotní pojišťovnu'));
        return $control;
    }
}