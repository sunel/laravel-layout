<?php

namespace Layout;

use Layout\Core\Block as BaseBlock;

class Block extends BaseBlock
{
	/**
     * Get relevant path to template.
     *
     * @return string
     */
    public function getTemplate()
    {
        $fileLocation = $this->config->get('handle_layout_section');

        $template = explode("::",$this->template);

        if(count($template) == 2) {
           $template = "{$template[0]}::$fileLocation.{$template[1]}";
        } else {
            $template = "$fileLocation.{$template[0]}";
        }
        
        return $template;
    }

    protected function getView($fileName, $viewVars)
    {
    	return app('view')->make($fileName, $viewVars)->render();
    }
}