<?php

namespace ORM;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IService {

    /**
     * @param IModel $data
     */
    public function createNew($data = null);

    public function findByPrimary($key);

    /*
     * These methods are not declared explicitly as they'd collide with contravariant typehinting.
     * Type-hinting is considered more important than interface compliance (dynamic typing) so the declarations
     * are only symbolic.
     */
    
    /* public function update(IModel $model); */

    /* public function save(IModel &$model); */

    /* public function dispose(IModel $model); */
}
