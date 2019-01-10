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

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
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
            return Html::el('span')->addAttributes(['class' => ('badge ' . ($row->active ? 'badge-success' : 'badge-danger'))])->addText(($row->active));
        });

        //
        // operations
        //
        $this->addButton('edit', _('Upravit'))
            ->setText(_('Upravit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->school_id);
            });
        $this->addGlobalButton('add')
            ->setLink($this->getPresenter()->link('create'))
            ->setLabel(_('Vložit školu'))
            ->setClass('btn btn-sm btn-primary');

        //
        // appeareance
        //

    }

}
