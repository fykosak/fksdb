<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
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
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->serviceTeacher = $container->getByType(ServiceTeacher::class);
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
            'person.full_name',
            'teacher.note',
            'teacher.state',
            'teacher.since',
            'teacher.until',
            'teacher.number_brochures',
            'school.school',
        ]);

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

    protected function getModelClassName(): string {
        return ModelTeacher::class;
    }
}
