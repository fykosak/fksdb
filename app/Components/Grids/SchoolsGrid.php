<?php

namespace FKSDB\Components\Grids;

use NiftyGrid\DataSource\NDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SchoolsGrid extends BaseGrid {

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $serviceSchool = $presenter->context->getService('ServiceSchool');
        $schools = $serviceSchool->getSchools();

        $this->setDataSource(new NDataSource($schools));

        //
        // columns
        //
        $this->addColumn('name', 'Název');
        $this->addColumn('city', 'Město');

        //
        // operations
        //
        $that = $this;
        $this->addButton("edit", "Upravit")
                ->setClass("edit")
                ->setText('Upravit') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link("edit", $row->school_id);
                        });
        // TODO add search/filtering schools

        //
        // appeareance
        //

    }

}
