<?php

namespace FKSDB\Components\Grids;


use Nette\Database\Table\Selection;
use Nette\Utils\Html;
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
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.v4.latte');
        $this['paginator']->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.v4.latte');
        //
        // data
        //
        $schools = $this->serviceSchool->getSchools();

        $dataSource = new SearchableDataSource($schools);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
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
        $this->addColumn('active', _('Existuje?'))->setRenderer(function ($row) {
            return Html::el('span')->addAttributes(['class' => ('badge ' . ($row->active ? 'badge-success' : 'badge-danger'))])->add(($row->active));
        });

        //
        // operations
        //
        $this->addButton("edit", _("Upravit"))
            ->setText('Upravit')//todo i18n
            ->setLink(function ($row) {
                return $this->getPresenter()->link("edit", $row->school_id);
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
