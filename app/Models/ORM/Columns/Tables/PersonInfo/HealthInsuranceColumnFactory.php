<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class HealthInsuranceColumnFactory extends ColumnFactory
{
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
     * @param ModelPersonInfo $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (\array_key_exists($model->health_insurance, self::ID_MAPPING)) {
            return Html::el('span')->addText(self::ID_MAPPING[$model->health_insurance]);
        }
        return Html::el('span')->addText($model->health_insurance);
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new SelectBox($this->getTitle());
        $control->setItems(self::ID_MAPPING);
        $control->setPrompt(_('Choose the insurance company'));
        return $control;
    }
}
