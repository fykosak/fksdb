<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

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
        27 => '(27) UNION zdravotná poisťovňa, a. s.'
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
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setItems(self::ID_MAPPING);
        $control->setPrompt(_('Vybete zdravotní pojišťovnu'));
        return $control;
    }
}
