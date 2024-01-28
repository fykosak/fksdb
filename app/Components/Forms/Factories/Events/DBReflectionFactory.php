<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\ReflectionFactory;
use Nette\Forms\Controls\BaseControl;

class DBReflectionFactory extends AbstractFactory
{
    private ReflectionFactory $tableReflectionFactory;

    public function __construct(ReflectionFactory $tableReflectionFactory)
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    public function createComponent(Field $field, BaseHolder $holder): BaseControl
    {
        $element = $this->tableReflectionFactory->loadColumnFactory('event_participant', $field->name)
            ->createField();
        if ($field->label) {
            $element->caption = $field->label;
        }
        if ($field->description) {
            $element->setOption('description', $field->description);
        }
        return $element;
    }

    protected function setDefaultValue(BaseControl $control, Field $field, BaseHolder $holder): void
    {
        $control->setDefaultValue($field->getDefault() ?? $field->getValue($holder));
    }
}
