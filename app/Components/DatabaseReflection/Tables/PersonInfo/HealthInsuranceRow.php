<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class HealthInsuranceField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class HealthInsuranceRow extends AbstractRow {
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

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Zdravotní pojišťovna');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        if (\array_key_exists($model->health_insurance, self::ID_MAPPING)) {
            return Html::el('span')->addText(self::ID_MAPPING[$model->health_insurance]);
        }
        if (\is_null($model->health_insurance)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addAttributes(['class' => 'text-danger'])->addHtml($model->health_insurance);
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setItems(self::ID_MAPPING);
        $control->setPrompt(_('Vybete zdravotní pojišťovnu'));
        return $control;
    }
}
