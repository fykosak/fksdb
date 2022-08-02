<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\ServiceSchool;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class SchoolsGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct($container, ServiceSchool::class, [], []);
    }

    protected function getData(): IDataSource
    {
        $schools = $this->service->getTable();
        $dataSource = new SearchableDataSource($schools);
        $dataSource->setFilterCallback(function (Selection $table, array $value) {
            $tokens = preg_split('/\s+/', $value['term']);
            foreach ($tokens as $token) {
                $table->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        });
        return $dataSource;
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        //
        // columns
        //
        $this->addColumn('name', _('Name'));
        $this->addColumn('city', _('City'))->setRenderer(function (ActiveRow $row) {
            $school = SchoolModel::createFromActiveRow($row);
            return $school->address->city;
        });
        $this->addColumn('active', _('Active?'))->setRenderer(
            fn(SchoolModel $row): Html => Html::el('span')
                ->addAttributes(['class' => ('badge ' . ($row->active ? 'bg-success' : 'bg-danger'))])
                ->addText(($row->active))
        );

        $this->addLink('school.edit');
        $this->addLink('school.detail');
    }
}
