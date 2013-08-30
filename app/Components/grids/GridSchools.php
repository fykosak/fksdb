<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class GridSchools extends AbstractGrid {

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $serviceSchool = $presenter->context->getService('ServiceSchool');
        $schools = $serviceSchool->getSchools();
        
        $this->setDataSource(new NiftyGrid\DataSource\NDataSource($schools));

        //
        // columns
        //
        $this->addColumn('name', 'Název');
        $this->addColumn('city', 'Město');

        //
        // appeareance
        //

    }

}
