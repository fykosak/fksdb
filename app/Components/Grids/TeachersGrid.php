<?php

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ServiceTeacher;
use Nette\Application\IPresenter;
use Nette\Database\Table\Selection;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use FKSDB\Models\SQL\SearchableDataSource;

class TeachersGrid extends BaseGrid {

    private ServiceTeacher $serviceTeacher;

    final public function injectServiceTeacher(ServiceTeacher $serviceTeacher): void {
        $this->serviceTeacher = $serviceTeacher;
    }

    protected function getData(): IDataSource {
        $teachers = $this->serviceTeacher->getTable()->select('teacher.*, person.family_name AS display_name');

        $dataSource = new SearchableDataSource($teachers);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('CONCAT(person.family_name, person.other_name) LIKE CONCAT(\'%\', ? , \'%\')', $token);
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
        $this->addColumns([
            'person.full_name',
            'teacher.note',
            'teacher.state',
            'teacher.since',
            'teacher.until',
            'teacher.number_brochures',
            'school.school',
        ]);
        $this->addLink('teacher.edit');
        $this->addLink('teacher.detail');
    }
}
