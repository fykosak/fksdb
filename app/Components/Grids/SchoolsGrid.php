<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\BaseGrid;
use Nette\Database\Table\Selection;
use ServiceSchool;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SchoolsGrid extends BaseGrid {

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    public function __construct(ServiceSchool $serviceSchool) {
        parent::__construct();
        $this->serviceSchool = $serviceSchool;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $schools = $this->serviceSchool->getSchools();

        $dataSource = new SearchableDataSource($schools);
        $dataSource->setFilterCallback(function(Selection $table, $value) {
                    $tokens = preg_split('/\s+/', $value);
                    foreach ($tokens as $token) {
                        $table->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
                    }
                });
        $this->setDataSource($dataSource);

        //
        // columns
        //
        $this->addColumn('name', _('Název'));
        $this->addColumn('city', _('Město'));

        //
        // operations
        //
        $that = $this;
        $this->addButton("edit", _("Upravit"))
                ->setText('Upravit') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link("edit", $row->school_id);
                        });
        $this->addGlobalButton('add')
                ->setLink($this->getPresenter()->link('create'))
                ->setLabel('Vložit školu')
                ->setClass('btn btn-sm btn-primary');

        //
        // appeareance
    //

    }

}
