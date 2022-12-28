<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\TeacherService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class TeachersGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct($container, TeacherService::class, [
            'person.full_name',
            'teacher.note',
            'teacher.state',
            'teacher.since',
            'teacher.until',
            'teacher.number_brochures',
            'school.school',
        ]);
    }

    protected function getData(): TypedSelection
    {
        return $this->service->getTable();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addORMLink('teacher.edit');
        $this->addORMLink('teacher.detail');
    }
}
