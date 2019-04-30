<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonInfo;
use FKSDB\ValidationTest\ValidationLog;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class HealthInsuranceField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class HealthInsuranceRow extends AbstractRow implements ITestedRowFactory {
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
    public function getModelAccessKey(): string {
        return 'health_insurance';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Health insurance');
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
        return Html::el('span')->addText($model->health_insurance);
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

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return ValidationLog
     */
    public function runTest(AbstractModelSingle $model): ValidationLog {
        $testName = 'person_info__health_insurance';
        if (\is_null($model->health_insurance)) {
            return new ValidationLog($testName, _('Health insurance is not set'), ValidationLog::LVL_INFO);
        }
        if (\array_key_exists($model->health_insurance, self::ID_MAPPING)) {
            return new ValidationLog($testName, _('Health insurance is valid'), ValidationLog::LVL_SUCCESS);
        }
        return new ValidationLog($testName, _('Undefined Health insurance'), ValidationLog::LVL_DANGER);
    }
}
