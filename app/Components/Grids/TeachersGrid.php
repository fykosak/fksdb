<?php

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ServiceTeacher;
use Nette\Application\IPresenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use FKSDB\Models\SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeachersGrid extends EntityGrid {

    public function __construct(Container $container) {
        parent::__construct($container, ServiceTeacher::class, [
            'person.full_name',
            'teacher.note',
            'teacher.state',
            'teacher.since',
            'teacher.until',
            'teacher.number_brochures',
            'school.school',
        ], []);
    }

    protected function createDataSource(Selection $source): IDataSource {
        return new SearchableDataSource($source);
    }

    protected function getData(): IDataSource {
        $dataSource = parent::getData();
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
        $this->addLink('teacher.edit');
        $this->addLink('teacher.detail');
    }
}
