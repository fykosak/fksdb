<?php

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelSchool;
use FKSDB\Models\ORM\Services\ServiceSchool;
use Nette\Application\IPresenter;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use FKSDB\Models\SQL\SearchableDataSource;

class SchoolsGrid extends BaseGrid {

    private ServiceSchool $serviceSchool;

    final public function injectServiceSchool(ServiceSchool $serviceSchool): void {
        $this->serviceSchool = $serviceSchool;
    }

    protected function getData(): IDataSource {
        $schools = $this->serviceSchool->getTable();
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
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);

        //
        // columns
        //
        $this->addColumn('name', _('Name'));
        $this->addColumn('city', _('City'))->setRenderer(function (ActiveRow $row) {
            $school = ModelSchool::createFromActiveRow($row);
            return $school->getAddress()->city;
        });
        $this->addColumn('active', _('Active?'))->setRenderer(function (ModelSchool $row): Html {
            return Html::el('span')->addAttributes(['class' => ('badge ' . ($row->active ? 'badge-success' : 'badge-danger'))])->addText(($row->active));
        });

        $this->addLink('school.edit');
        $this->addLink('school.detail');
    }
}
