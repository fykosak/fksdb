<?php

namespace FKS\Components\Forms\Containers;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @note This interface may be later used for Containers.
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IWriteonly {

    public function setWriteonly($value = true);

    public function getWriteonly();
}
