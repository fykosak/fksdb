<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class AvailablePointsRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AvailablePointsRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Available points');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniGameSetup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $container = Html::el('span');
        foreach ($model->getAvailablePoints() as $points) {
            $container->addHtml(Html::el('span')
                ->addAttributes(['class' => 'badge badge-secondary mr-1'])
                ->addText($points));
        }
        return $container;
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    public function createField(...$args): BaseControl {
        throw new OmittedControlException();
    }
}
