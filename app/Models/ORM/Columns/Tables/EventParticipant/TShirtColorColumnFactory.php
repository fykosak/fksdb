<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\EventParticipant;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<EventParticipantModel>
 */
class TShirtColorColumnFactory extends ColumnFactory
{
    /**
     * @param EventParticipantModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $value = $model->tshirt_color;
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        $container = Html::el('span');
        $container->addHtml(
            Html::el('i')->addAttributes([
                'style' => 'background-color: ' . $value,
                'class' => 't-shirt-color',
            ])
        );
        $container->addText($value);
        return $container;
    }
}
