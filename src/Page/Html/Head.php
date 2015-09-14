<?php

namespace Layout\Page\Html;

class Head extends \Layout\Block
{
    /**
     * Initialize template.
     */
    protected function boot()
    {
        $this->setTemplate('render::template.page.html.head');
    }

    /**
     * Add CSS file to HEAD entity.
     *
     * @param string $name
     * @param string $params
     *
     * @return \Layout\Page\Html\Head
     */
    public function addCss($name, $params = '')
    {
        $this->addItem('css', $name, $params);

        return $this;
    }

    /**
     * Add JavaScript file to HEAD entity.
     *
     * @param string $name
     * @param string $params
     *
     * @return \Layout\Page\Html\Head
     */
    public function addJs($name, $params = '')
    {
        $this->addItem('js', $name, $params);

        return $this;
    }

    /**
     * Add Master CSS file to HEAD entity.
     *
     * @param string $name
     * @param string $params
     *
     * @return \Layout\Page\Html\Head
     */
    public function addMasterCss($name, $params = '')
    {
        $this->addItem('master_css', $name, $params);
        $this->setLoadMasterCss(true);

        return $this;
    }

    /**
     * Add Master JavaScript file to HEAD entity.
     *
     * @param string $name
     * @param string $params
     *
     * @return \Layout\Page\Html\Head
     */
    public function addMasterJs($name, $params = '')
    {
        $this->addItem('master_js', $name, $params);
        $this->setLoadMasterJs(true);

        return $this;
    }

    /**
     * Add CSS file for Internet Explorer only to HEAD entity.
     *
     * @param string $name
     * @param string $params
     *
     * @return \Layout\Page\Html\Head
     */
    public function addCssIe($name, $params = '')
    {
        $this->addItem('css', $name, $params, 'IE');

        return $this;
    }

    /**
     * Add JavaScript file for Internet Explorer only to HEAD entity.
     *
     * @param string $name
     * @param string $params
     *
     * @return \Layout\Page\Html\Head
     */
    public function addJsIe($name, $params = '')
    {
        $this->addItem('js', $name, $params, 'IE');

        return $this;
    }

    /**
     * Add HEAD External Item.
     *
     * Allowed types:
     *  - js
     *  - css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     *
     * @return Mage_Page_Block_Html_Head
     */
    public function addExternalItem($type, $name, $params = null, $if = null, $cond = null)
    {
        $this->addItem($type, $name, $params = null, $if = null, $cond = null);
    }

    /**
     * Remove External Item from HEAD entity.
     *
     * @param string $type
     * @param string $name
     *
     * @return Mage_Page_Block_Html_Head
     */
    public function removeExternalItem($type, $name)
    {
        $this->removeItem($type, $name);
    }

    /**
     * Add Link element to HEAD entity.
     *
     * @param string $rel  forward link types
     * @param string $href URI for linked resource
     *
     * @return \Layout\Page\Html\Head
     */
    public function addLinkRel($rel, $href)
    {
        $this->addItem('link_rel', $href, 'rel="'.$rel.'"');

        return $this;
    }

    /**
     * Add HEAD Item.
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     *
     * @return \Layout\Page\Html\Head
     */
    public function addItem($type, $name, $params = null, $if = null, $cond = null)
    {
        if ($type === 'css' && empty($params)) {
            $params = 'media="all"';
        }
        $this->_data['items'][$type.'/'.$name] = [
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
       ];

        return $this;
    }

    /**
     * Remove Item from HEAD entity.
     *
     * @param string $type
     * @param string $name
     *
     * @return \Layout\Page\Html\Head
     */
    public function removeItem($type, $name)
    {
        unset($this->_data['items'][$type.'/'.$name]);

        return $this;
    }

    /**
     * Get HEAD HTML with CSS/JS/RSS definitions
     * (actually it also renders other elements, TODO: fix it up or rename this method).
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        // separate items by types
        $lines = [];
        foreach ($this->_data['items'] as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
                continue;
            }
            if ($this->getLoadMasterCss()) {
                if ($item['type'] == 'css') {
                    continue;
                };
            }

            if ($this->getLoadMasterJs()) {
                if ($item['type'] == 'js') {
                    continue;
                };
            }

            $if = !empty($item['if']) ? $item['if'] : '';
            $params = !empty($item['params']) ? $item['params'] : '';
            switch ($item['type']) {
                case 'js':        // js/*.js
                case 'css':  // css/*/*.css
                case 'master_js':
                case 'master_css':
                    $lines[$if][$item['type']][$params][$item['name']] = $item['name'];
                    break;
                default:
                    $this->_separateOtherHtmlHeadElements($lines, $if, $item['type'], $params, $item['name'], $item);
                    break;
            }
        }

        $html = '';
        foreach ($lines as $if => $items) {
            if (empty($items)) {
                continue;
            }
            if (!empty($if)) {
                // open !IE conditional using raw value
                if (strpos($if, '><!-->') !== false) {
                    $html .= $if."\n";
                } else {
                    $html .= '<!--[if '.$if.']>'."\n";
                }
            }

            $html .= $this->_prepareStaticElements('<link rel="stylesheet" type="text/css" href="%s"%s />'."\n",
                empty($items['master_css']) ? [] : $items['master_css'],
                empty($items['master_css']) ? '' : 'css'
            );

            $html .= $this->_prepareStaticElements('<script type="text/javascript" src="%s"%s></script>'."\n",
                empty($items['master_js']) ? [] : $items['master_js'],
                empty($items['master_js']) ? '' : 'js'
            );

            // static and skin css
            $html .= $this->_prepareStaticElements('<link rel="stylesheet" type="text/css" href="%s"%s />'."\n",
                empty($items['css']) ? [] : $items['css'],
                empty($items['css']) ? '' : 'css'
            );

            // static and skin javascripts
            $html .= $this->_prepareStaticElements('<script type="text/javascript" src="%s"%s></script>'."\n",
                empty($items['js']) ? [] : $items['js'],
                empty($items['js']) ? '' : 'js'
            );

            // other stuff
            if (!empty($items['other'])) {
                $html .= $this->_prepareOtherHtmlHeadElements($items['other'])."\n";
            }

            if (!empty($if)) {
                // close !IE conditional comments correctly
                if (strpos($if, '><!-->') !== false) {
                    $html .= '<!--<![endif]-->'."\n";
                } else {
                    $html .= '<![endif]-->'."\n";
                }
            }
        }

        return $html;
    }

    /**
     * @param string $format      - HTML element format for sprintf('<element src="%s"%s />', $src, $params)
     * @param array  $staticItems - array of relative names of static items to be grabbed from  folder
     * @param string $type        -  js/css
     *
     * @return string
     */
    protected function &_prepareStaticElements($format, array $staticItems, $type)
    {
        $items = [];

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = asset($name);
            }
        }

        $html = '';
        foreach ($items as $params => $rows) {
            // render elements
            $params = trim($params);
            $params = $params ? ' '.$params : '';
            foreach ($rows as $src) {
                $html .= sprintf($format, $src, $params);
            }
        }

        return $html;
    }

    /**
     * Classify HTML head item and queue it into "lines" array.
     *
     * @see self::getCssJsHtml()
     *
     * @param array  &$lines
     * @param string $itemIf
     * @param string $itemType
     * @param string $itemParams
     * @param string $itemName
     * @param array  $itemThe
     */
    protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe)
    {
        $params = $itemParams ? ' '.$itemParams : '';
        $href = $itemName;
        switch ($itemType) {
            case 'rss':
                $lines[$itemIf]['other'][] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />',
                    $href, $params
                );
                break;
            case 'link_rel':
                $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
                break;
            case 'external_js':
                $lines[$itemIf]['other'][] = sprintf('<script type="text/javascript" src="%s" %s></script>', $href, $params);
                break;

            case 'external_css':
                $lines[$itemIf]['other'][] = sprintf('<link rel="stylesheet" type="text/css" href="%s" %s/>', $href, $params);
                break;
        }
    }

    /**
     * Render arbitrary HTML head items.
     *
     * @see self::getCssJsHtml()
     *
     * @param array $items
     *
     * @return string
     */
    protected function _prepareOtherHtmlHeadElements($items)
    {
        return implode("\n", $items);
    }

    /**
     * Retrieve Content Type.
     *
     * @return string
     */
    public function getContentType()
    {
        if (empty($this->_data['content_type'])) {
            $this->_data['content_type'] = $this->getMediaType().'; charset='.$this->getCharset();
        }

        return $this->_data['content_type'];
    }

    /**
     * Retrieve Media Type.
     *
     * @return string
     */
    public function getMediaType()
    {
        if (empty($this->_data['media_type'])) {
            $this->_data['media_type'] = config('layout.head.media_type');
        }

        return $this->_data['media_type'];
    }

    /**
     * Retrieve Charset.
     *
     * @return string
     */
    public function getCharset()
    {
        if (empty($this->_data['charset'])) {
            $this->_data['charset'] = config('layout.head.charset');
        }

        return $this->_data['charset'];
    }

    /**
     * Set title element text.
     *
     * @param string $title
     *
     * @return \Layout\Page\Html\Head
     */
    public function setTitle($title)
    {
        $this->_data['title'] = config('layout.head.title.prefix').' '.$title.' '.config('layout.head.title.suffix');

        return $this;
    }

    /**
     * Retrieve title element text (encoded).
     *
     * @return string
     */
    public function getTitle()
    {
        if (empty($this->_data['title'])) {
            $this->_data['title'] = $this->getDefaultTitle();
        }

        return htmlspecialchars(html_entity_decode(trim($this->_data['title']), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Retrieve default title text.
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return config('layout.head.title.default');
    }

    /**
     * Retrieve content for description tag.
     *
     * @return string
     */
    public function getDescription()
    {
        if (empty($this->_data['description'])) {
            $this->_data['description'] = config('layout.head.description');
        }

        return $this->_data['description'];
    }

    /**
     * Retrieve content for keyvords tag.
     *
     * @return string
     */
    public function getKeywords()
    {
        if (empty($this->_data['keywords'])) {
            $this->_data['keywords'] = config('layout.head.keywords');
        }

        return $this->_data['keywords'];
    }

    /**
     * Retrieve URL to robots file.
     *
     * @return string
     */
    public function getRobots()
    {
        if (empty($this->_data['robots'])) {
            $this->_data['robots'] = config('layout.head.robots');
        }

        return $this->_data['robots'];
    }

    /**
     * Get miscellanious scripts/styles to be included in head before head closing tag.
     *
     * @return string
     */
    public function getIncludes()
    {
        if (empty($this->_data['includes'])) {
            $this->_data['includes'] = config('layout.head.includes');
        }

        return $this->_data['includes'];
    }

    /**
     * Getter for path to Favicon.
     *
     * @return string
     */
    public function getFaviconFile()
    {
        if (empty($this->_data['favicon_file'])) {
            $this->_data['favicon_file'] = config('layout.head.favicon_file');
        }

        return $this->_data['favicon_file'];
    }
}
