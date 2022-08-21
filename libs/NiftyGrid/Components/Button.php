<?php

declare(strict_types=1);

namespace NiftyGrid\Components;

use Nette\Application\UI\Component;
use Nette\Utils\Html;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
class Button extends Component
{
    private ?string $label;

    /** @var callback */
    private $link;
    private string $text;
    private string $target;
    private string $class;
    /** @var callback */
    private $dialog;
    /** @var callback */
    private $show;

    public function __construct(?string $label)
    {
        $this->label = $label;
    }

    public function setLink(callable $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function setConfirmationDialog(callable $message): self
    {
        $this->dialog = $message;
        return $this;
    }

    /**
     * @param mixed $row
     * @return callback|mixed|string
     */
    public function getConfirmationDialog($row)
    {
        if (is_callable($this->dialog)) {
            return ($this->dialog)($row);
        }
        return $this->dialog;
    }

    /**
     * @param callback|string $show
     */
    public function setShow(callable $show): self
    {
        $this->show = $show;
        return $this;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @param mixed $row
     */
    public function render($row): void
    {
        if (isset($this->show) && !($this->show)($row)) {
            return;
        }

        $el = Html::el('a')
            ->href(($this->link)($row))
            ->setText($this->text ?? null)
            ->addClass('grid-button')
            ->addClass($this->class ?? 'btn btn-secondary')
            ->setTitle($this->label)
            ->setTarget($this->target ?? null);

        if (isset($this->dialog)) {
            $el->addClass('grid-confirm')
                ->addData('grid-confirm', $this->getConfirmationDialog($row));
        }

        echo $el;
    }
}
