<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractGrid extends NiftyGrid\Grid {

    protected function configure($presenter) {
        $this->setTemplate(LIBS_DIR . '/NiftyGrid/templates/grid.latte');
        $this['paginator']->setTemplate(LIBS_DIR . '/NiftyGrid/templates/paginator.latte');
    }

}
