<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\Types\StringColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @phpstan-extends StringColumnFactory<TeamModel2,never>
 */
class NameColumnFactory extends StringColumnFactory
{
    public function createFormControl(...$args): BaseControl
    {
        $control = parent::createFormControl(...$args);
        $control->setRequired();
        $control->addRule(
            Form::PATTERN,
            _(
                'Název týmu smí obsahovat pouze latinku, řečtinu, cyrilici
       a ASCII znaky.'
            ),
            '^[\p{Latin}\p{Greek}\p{Cyrillic}\x{0020}-\x{00FF}]+$'
        );
        return $control;
    }
}
