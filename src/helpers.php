<?php

if (!function_exists('loadLayout')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Layout\Factory
     */
    function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $disableRouteHandle = false)
    {
        $factory = getLayoutFactory();

        return $factory->loadHandles($handles, $disableRouteHandle)->loadLayout($generateBlocks, $generateXml);
    }
}

if (!function_exists('render')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Illuminate\View\View
     */
    function render($handles = null, $generateBlocks = true, $generateXml = true, $disableRouteHandle = false)
    {
        $factory = getLayoutFactory();
        $html = $factory->render($handles, $generateBlocks, $generateXml, $disableRouteHandle);
        return view('render::template.page.root', ['html' => $html]);
    }
}

if (!function_exists('renderWithOptions')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Illuminate\View\View
     */
    function renderWithOptions(array $options, $handles = null, $generateBlocks = true, $generateXml = true, $disableRouteHandle = false)
    {
        $factory = getLayoutFactory();

        $factory->loadHandles($handles, $disableRouteHandle)->loadLayout($generateBlocks, $generateXml);

        foreach ($options as $key => $option) {
            switch ($key) {
                /**
                 * Prepare titles in the 'head' layout block
                 * Supposed to work only in actions where layout is rendered
                 * Falls back to the default logic if there are no titles eventually.
                 */
                case 'titles':
                    $titles = [];
                    $removeDefaultTitle = false;
                    foreach ($option as $text) {
                        if (is_string($text)) {
                            $titles[] = $text;
                        } elseif (-1 === $text) {
                            if (empty($titles)) {
                                $removeDefaultTitle = true;
                            } else {
                                array_pop($titles);
                            }
                        }
                    }
                    $titleBlock = $factory->getLayout()->getBlock('head');
                    if ($titleBlock) {
                        if (!$removeDefaultTitle) {
                            $title = trim($titleBlock->getTitle());
                            if ($title) {
                                array_unshift($titles, $title);
                            }
                        }
                        $titleBlock->setTitle(implode(' / ', array_reverse($titles)));
                    }
                    break;
                case 'breadcrumbs':
                    $crumbs = $factory->getLayout()->getBlock('breadcrumbs');
                    if ($crumbs) {
                        foreach ($option as $name => $info) {
                            $crumbs->addCrumb($name, $info);
                        }
                    }
                    break;
                case 'with':
                    foreach ($option as $key => $value) {
                        view()->share($key, $value);
                    }
                   break;
                case 'layout_handles':
                    foreach ($option as $value) {
                        $factory->addCustomHandle($value);
                    }
                   break;
                default:
                    $titleBlock = $factory->getLayout()->getBlock('head');
                    if ($titleBlock) {
                        $titleBlock->setData($key, $option);
                    }
                    break;
            }
        }

        $html = $factory->renderLayout();
        return view('render::template.page.root', ['html' => $html]);
    }
}

if (!function_exists('getLayoutFactory')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Layout\Factory
     */
    function getLayoutFactory()
    {
        $factory = new \Layout\Core\Factory(
            app('layout.event'),
            app('layout.config'),
            app('layout.profile')
        );

        $factory->setLayout(
            new \Layout\Core\Layout(
                app('layout.event'),
                new \Layout\Core\Update(app('layout.cache'), app('layout.config'), app('layout.profile')),
                app('layout.config'),
                app('layout.profile')
            )
        );

        return $factory;
    }
}
