<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
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

    /**
     * @param ServiceSchool $serviceSchool
     * @return void
     */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    protected function getData(): IDataSource {
        $schools = $this->serviceSchool->getSchools();
        $dataSource = new SearchableDataSource($schools);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        });
        return $dataSource;
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);

        //
        // columns
        //
        $this->addColumn('name', _('Name'));
        $this->addColumn('city', _('City'));
        $this->addColumn('active', _('Exists?'))->setRenderer(function (ModelSchool $row) {
            return Html::el('span')->addAttributes(['class' => ('badge ' . ($row->active ? 'badge-success' : 'badge-danger'))])->addText(($row->active));
        });

        $this->addLinkButton('edit', 'edit', _('Edit'), false, ['id' => 'school_id']);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'school_id']);
    }
}
