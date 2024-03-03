<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use Nette\DI\Container;

class SousApplicationForm extends ApplicationComponent
{
    public function createFormContainer(): ContainerWithOptions
    {
        $container = new ContainerWithOptions($this->getContext());
        $container->setOption('label', _('Participant'));

        foreach ($this->fields as $name => $field) {
            if (!$field->isVisible($this)) {
                continue;
            }
            $component = $field->createFormComponent($this);
            $container->addComponent($component, $name);
            $field->setFieldDefaultValue($component, $this);
        }
        return $container;
    }
}
