<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Database\Table\Selection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use OrgModule\TeacherPresenter;
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
     * TeachersGrid constructor.
     * @param ServiceTeacher $serviceTeacher
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceTeacher $serviceTeacher, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @param TeacherPresenter $presenter
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws BadRequestException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $teachers = $this->serviceTeacher->getTable()->select('teacher.*, person.family_name AS display_name');

        $dataSource = new SearchableDataSource($teachers);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('CONCAT(person.family_name, person.other_name) LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        });
        $this->setDataSource($dataSource);

        $this->addColumns([
            'referenced.person_name',
            'referenced.school_name',
            DbNames::TAB_TEACHER . '.note',
            DbNames::TAB_TEACHER . '.state',
            DbNames::TAB_TEACHER . '.since',
            DbNames::TAB_TEACHER . '.until',
            DbNames::TAB_TEACHER . '.number_brochures',
        ]);

        $this->addColumn('school_id', _('School'))->setRenderer(function ($row) {
            return $row->getSchool()->name_abbrev;
        });
        $this->addLinkButton($presenter, 'edit', 'edit', _('Edit'), false);
        $this->addLinkButton($presenter, 'detail', 'detail', _('Detail'), false);

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel(_('Create new teacher'))
                ->setLink($this->getPresenter()->link('create'));
        }
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelTeacher::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_TEACHER;
    }
}
