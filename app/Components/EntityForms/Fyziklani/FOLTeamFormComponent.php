<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\Forms\FormProcessing\FOLCategoryProcessing;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

class FOLTeamFormComponent extends TeamFormComponent
{
    /**
     * @throws Exception
     */
    protected function getMemberFieldsDefinition(): array
    {
        return Neon::decodeFile(__DIR__ . DIRECTORY_SEPARATOR . 'fol.member.neon');
    }

    protected function getProcessing(): array
    {
        return [
            new FOLCategoryProcessing($this->container),
        ];
    }

    public function render(): void
    {
        $this->template->event = $this->event;
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.fol.latte';
    }

    protected function getTeamFields(): array
    {
        return ['name'];
    }

    protected function getTeacherFieldsDefinition(): array
    {
        return [];
    }
}
