<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniGameSetup;

use FKSDB\Models\ORM\Columns\Types\StringColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\GameSetupModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends StringColumnFactory<GameSetupModel,never>
 */
class AvailablePointsColumnFactory extends StringColumnFactory
{
    /**
     * @param GameSetupModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $container = Html::el('span');
        foreach ($model->getAvailablePoints() as $points) {
            $container->addHtml(
                Html::el('span')
                    ->addAttributes(['class' => 'badge bg-secondary me-1'])
                    ->addText($points)
            );
        }
        return $container;
    }
}
