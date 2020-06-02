<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeachersGrid extends BaseGrid {

    /**
     * @var ServiceTeacher
     */
    private $serviceTeacher;

    /**
     * @param ServiceTeacher $serviceTeacher
     * @return void
     */
    public function injectServiceTeacher(ServiceTeacher $serviceTeacher) {
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
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        //
        // columns
        //
        $this->addColumns([
            'referenced.person_name',
            'teacher.note',
            'teacher.state',
            'teacher.since',
            'teacher.until',
            'teacher.number_brochures',
        ]);
        $this->addColumn('school_id', _('School'))->setRenderer(function (ModelTeacher $row) {
            return $row->getSchool()->name_abbrev;
        });
        //
        // operations
        //
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function (ModelTeacher $row) {
                return $this->getPresenter()->link('edit', $row->teacher_id);
            });
        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function (ModelTeacher $row) {
                return $this->getPresenter()->link('detail', ['id' => $row->teacher_id]);
            });

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel(_('Create new teacher'))
                ->setLink($this->getPresenter()->link('create'));
        }
    }
}
