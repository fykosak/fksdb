<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Control;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<M>
 */
abstract class Button extends BaseItem
{
    /** @var callable(M):array{string,array<string,mixed>} */
    private $linkCallback;
    /** @var (callable(M,int|null):bool)|null */
    private $showCallback;
    private ?string $buttonClassName;

    /**
     * @phpstan-param callable(M):array{string,array<string,mixed>} $linkCallback
     * @phpstan-param (callable(M,int|null):bool)|null $showCallback
     */
    public function __construct(
        Container $container,
        Title $title,
        callable $linkCallback,
        ?string $buttonClassName = null,
        ?callable $showCallback = null
    ) {
        parent::__construct($container, $title);
        $this->linkCallback = $linkCallback;
        $this->buttonClassName = $buttonClassName;
        $this->showCallback = $showCallback;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'button.latte';
    }

    /**
     * @param M|null $model
     */
    public function render(?Model $model, ?int $userPermission, array $params = []): void
    {
        [$destination, $params] = ($this->linkCallback)($model);
        parent::render($model, $userPermission, [
            'linkControl' => $this->getLinkControl(),
            'show' => isset($this->showCallback) ? ($this->showCallback)($model, $userPermission) : true,
            'destination' => $destination,
            'params' => $params,
            'buttonClassName' => $this->buttonClassName ?? 'btn btn-sm me-1 btn-outline-secondary',
        ]);
    }

    abstract protected function getLinkControl(): Control;
}
