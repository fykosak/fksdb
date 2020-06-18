<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class HealthInsuranceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class HealthInsuranceRow extends AbstractColumnFactory {
    const ID_MAPPING = [
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

    public function getModelAccessKey(): string {
        return 'health_insurance';
    }

    public function getTitle(): string {
        return _('Health insurance');
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model): Html {
        if (\array_key_exists($model->health_insurance, self::ID_MAPPING)) {
            return Html::el('span')->addText(self::ID_MAPPING[$model->health_insurance]);
        }
        return Html::el('span')->addText($model->health_insurance);
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setItems(self::ID_MAPPING);
        $control->setPrompt(_('Vybete zdravotní pojišťovnu'));
        return $control;
    }
}
