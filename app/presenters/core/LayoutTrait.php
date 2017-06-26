<?php

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
trait LayoutTrait {

    private $layout;

    protected function setDynamicLayout($layout) {
        $this->layout = $layout;
    }

    public function formatLayoutTemplateFiles() {

        $originalLayout = $this->getLayout();
        $this->setLayout($this->layout);
        $dynamicFiles = parent::formatLayoutTemplateFiles();
        $this->setLayout($originalLayout);
        $originalFiles = parent::formatLayoutTemplateFiles();

        $files = [];
        for ($i = 0; $i < count($originalFiles); ++$i) {
            $files[] = $dynamicFiles[$i];
            $files[] = $originalFiles[$i];
        }

        return $files;
    }
}
