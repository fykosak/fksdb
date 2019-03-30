<?php

namespace FKSDB\Components\Grids;


use FKSDB\ORM\Services\ServiceSchool;
use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SchoolsGrid extends BaseGrid {

    /**
     * @var \FKSDB\ORM\Services\ServiceSchool
     */
    private $serviceSchool;

    /**
     * SchoolsGrid constructor.
     * @param \FKSDB\ORM\Services\ServiceSchool $serviceSchool
     */
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
        $this->addColumn('name', _('Name'));
        $this->addColumn('city', _('City'));
        $this->addColumn('active', _('Exists?'))->setRenderer(function ($row) {
            return Html::el('span')->addAttributes(['class' => ('badge ' . ($row->active ? 'badge-success' : 'badge-danger'))])->addText(($row->active));
        });

        //
        // operations
        //
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->school_id);
            });
        $this->addGlobalButton('add')
            ->setLink($this->getPresenter()->link('create'))
            ->setLabel(_('CreateSchool'))
            ->setClass('btn btn-sm btn-primary');

        //
        // appeareance
        //

    }

}
