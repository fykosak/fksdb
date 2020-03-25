<?php

namespace FKSDB\Components\Controls\Loaders;

use Nette\Application\UI\Control;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class Webloader extends Control {

    const FILENAME = 'file';
    const ATTRIBUTES = 'attr';
    const UNTAGGED = '__untagged';
    /** @var string[] */
    private $files = [];
    /** @var string[] */
    private $inlines = [];

    /**
     * @param $file
     * @param array $attributes
     */
    public function addFile(string $file, array $attributes = []) {
        $hash = $file . implode(':', $attributes);
        $this->files[$hash] = array(
            self::FILENAME => $file,
            self::ATTRIBUTES => $attributes,
        );
        $this->invalidateControl();
    }

    /**
     * @param $file
     * @param array $attributes
     */
    public function removeFile(string $file, array $attributes = []) {
        $hash = $file . implode(':', $attributes);
        unset($this->files[$hash]);
        $this->invalidateControl();
    }

    /**
     * @param $inline
     * @param string $tag
     */
    public function addInline(string $inline, string $tag = self::UNTAGGED) {
        $this->inlines[$tag] = $inline;
        $this->invalidateControl();
    }

    /**
     * @param $tag
     */
    public function removeInline(string $tag) {
        if ($tag != self::UNTAGGED) {
            unset($this->inlines[$tag]);
        }
        $this->invalidateControl();
    }

    public function render() {
        $args = func_get_args();

        $files = [];
        if (count($args) == 1 && is_array($args[0])) {
            foreach ($args[0] as $file => $attributes) {
                $files[] = array(
                    self::FILENAME => $file,
                    self::ATTRIBUTES => $attributes,
                );
            }
        } else {
            foreach ($args as $arg) {
                $files[] = array(
                    self::FILENAME => $arg,
                    self::ATTRIBUTES => [],
                );
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
        $template->inlines = $this->getInlines();
        $template->render();
    }

    /**
     * @param string $file
     * @return bool
     */
    public static function isRelative(string $file): bool {
        return !preg_match('@https?://|/@Ai', $file);
    }

    /**
     * @return mixed
     */
    abstract protected function getTemplateFilePrefix(): string;

    /**
     * @return array
     */
    protected function getFiles(): array {
        return $this->files;
    }

    /**
     * @return array
     */
    protected function getInlines(): array {
        return $this->inlines;
    }

}
