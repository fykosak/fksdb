<?php

namespace FKSDB\Components\Grids;

use NiftyGrid\Grid;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class BaseGrid extends Grid {

    protected function configure($presenter) {
        $this->setTemplate(LIBS_DIR . '/NiftyGrid/templates/grid.latte');
        $this['paginator']->setTemplate(LIBS_DIR . '/NiftyGrid/templates/paginator.latte');
    }

}
