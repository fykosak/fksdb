<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 */
class ModelContainer extends ContainerWithOptions
{
    /**
     * @param Model|iterable $data
     * @return static
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof Model) {
            $data = $data->toArray();
        }
        return parent::setValues($data, $erase);
    }

    public function setDisabled(bool $value = true): void
    {
        /** @var BaseControl $component */
        foreach ($this->getComponents() as $component) {
            $component->setDisabled($value);
        }
    }

    /**
     * @param mixed $value
     */
    public function setHtmlAttribute(string $name, $value = true): self
    {
        foreach ($this->getComponents() as $component) {
            $component->setHtmlAttribute($name, $value);
        }
        return $this;
    }
}
