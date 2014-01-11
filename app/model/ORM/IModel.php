<?php

namespace ORM;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IModel {

    public function isNew();

    public function toArray();
}
