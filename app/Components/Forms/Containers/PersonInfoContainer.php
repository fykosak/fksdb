<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use Fykosak\NetteORM\Model;

class PersonInfoContainer extends ModelContainer
{

    /**
     * @param mixed|iterable $data
     * @return static
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof Model) { //assert its from person info table
            $data['agreed'] = (bool)$data['agreed'];
        }

        return parent::setValues($data, $erase);
    }
}
