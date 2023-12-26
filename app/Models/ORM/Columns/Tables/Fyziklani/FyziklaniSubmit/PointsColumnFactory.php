<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniSubmit;

use FKSDB\Models\ORM\Columns\Types\IntColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\UI\NumberPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-extends IntColumnFactory<SubmitModel,never>
 */
class PointsColumnFactory extends IntColumnFactory
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setNumberFactory(NumberPrinter::NULL_VALUE_NOT_SET, null, null);
    }

    /**
     * @param SubmitModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $el = Html::el('span');
        if (!\is_null($model->points)) {
            return $el->addText($model->points);
        }
        return $el->addAttributes(['class' => 'badge bg-warning'])->addText(_('revoked'));
    }
}
