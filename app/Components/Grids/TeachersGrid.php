<?php

namespace FKSDB\Components\Grids;

use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use OrgModule\TeacherPresenter;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeachersGrid extends BaseGrid {

    /**
     * @var \ServiceTeacher
     */
    private $serviceTeacher;

    function __construct(\ServiceTeacher $serviceTeacher) {
        parent::__construct();
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @param $presenter TeacherPresenter
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.v4.latte');
        $this['paginator']->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.v4.latte');

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
        //
        // columns
        //
        $this->addColumn('display_name', _('Name'))->setRenderer(function (\ModelTeacher $row) {
            $person = $row->getPerson();
            return $person->getFullname();
        });
        $this->addColumn('since', _('Since'))->setRenderer(function (\ModelTeacher $row) {
            if ($row->since === null) {
                return Html::el('span')->addAttributes(['class' => 'badge badge-secondary'])->add(_('undefined'));
            }
            return $row->since->format('Y-m-d');
        });
        $this->addColumn('until', _('Until'))->setRenderer(function (\ModelTeacher $row) {
            if ($row->until === null) {
                return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->add(_('Still teaches'));
            }
            return $row->until->format('Y-m-d');
        });
        $this->addColumn('school_id', _('School'))->setRenderer(function (\ModelTeacher $row) {
            return $row->getSchool()->name_abbrev;
        });

        //
        // operations
        //
        $this->addButton('edit', _('Edit'))
            ->setText('Edit')//todo i18n
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->teacher_id);
            })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('edit', ['id' => $row->teacher_id]);
            });

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel('Create new teacher')
                ->setLink($this->getPresenter()->link('create'));
        }
    }
}
