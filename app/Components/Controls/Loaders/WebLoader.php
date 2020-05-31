<?php

namespace FKSDB\Components\Controls\Loaders;

use Nette\Application\UI\Control;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class WebLoader extends Control {

    public const FILENAME = 'file';
    public const ATTRIBUTES = 'attr';
    public const UNTAGGED = '__untagged';
    /**
     * @var string[]
     */
    private array $files = [];
    /**
     * @var string[]
     */
    private array $inlines = [];

    public function addFile(string $file, array $attributes = []): void {
        $hash = $file . join(':', $attributes);
        $this->files[$hash] = [
            self::FILENAME => $file,
            self::ATTRIBUTES => $attributes,
        ];
        $this->invalidateControl();
    }

    public function removeFile(string $file, array $attributes = []): void {
        $hash = $file . join(':', $attributes);
        unset($this->files[$hash]);
        $this->invalidateControl();
    }


    public function addInline(string $inline, string $tag = self::UNTAGGED): void {
        $this->inlines[$tag] = $inline;
        $this->invalidateControl();
    }


    public function removeInline(string $tag): void {
        if ($tag != self::UNTAGGED) {
            unset($this->inlines[$tag]);
        }
        $this->invalidateControl();
    }

    public function render(...$args): void {
        $files = [];
        if (count($args) == 1 && is_array($args[0])) {
            foreach ($args[0] as $file => $attributes) {
                $files[] = [
                    self::FILENAME => $file,
                    self::ATTRIBUTES => $attributes,
                ];
            }
        } else {
            foreach ($args as $arg) {
                $files[] = [
                    self::FILENAME => $arg,
                    self::ATTRIBUTES => [],
                ];
            }
        }

        $template = $this->createTemplate();
        $template->setFile($this->getTemplateFilePrefix() . '.files.latte');
        $template->files = array_merge($files, $this->getFiles());
        $template->render();
    }

    public function renderInline() {
        $template = $this->createTemplate();
        $template->setFile($this->getTemplateFilePrefix() . '.inlines.latte');
        $template->inlines = $this->getInLines();
        $template->render();
    }

    public static function isRelative(string $file): bool {
        return !preg_match('@https?://|/@Ai', $file);
    }

    abstract protected function getTemplateFilePrefix(): string;

    protected function getFiles(): array {
        return $this->files;
    }

    protected function getInLines(): array {
        return $this->inlines;
    }
}
