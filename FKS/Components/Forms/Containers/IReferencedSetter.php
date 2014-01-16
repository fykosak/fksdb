<?php

namespace FKS\Components\Forms\Containers;

use ORM\IModel;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedSetter {

    public function setModel(ReferencedContainer $container, IModel $model);
}
