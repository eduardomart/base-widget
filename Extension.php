<?php

namespace Bolt\Extension\Bolt\BaseWidget;

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{

    public function initialize()
    {
        foreach((array) $this->config['widgets'] as $name => $widget) {

            // Skip diabled extensions.
            if ($widget['enabled'] === false) {
                continue;
            }

            if(empty($widget['type']) || $widget['type'] !== 'backend') {
                $widget['type'] = 'frontend';
            }

            $widgetObj = new \Bolt\Asset\Widget\Widget();
            $widgetObj
                ->setType($widget['type'])
                ->setLocation($widget['location'])
                ->setCallback([$this, 'widget'])
                ->setCallbackArguments(['widget' => $widget])
            ;

            if (!empty($widget['location'])) {
                $widgetObj->setClass($widget['class']);
            }

            if (!empty($widget['defer'])) {
                $widgetObj->setDefer($widget['defer']);
            }

            if (!empty($widget['cacheduration'])) {
                $widgetObj->setCacheDuration($widget['cacheduration']);
            }

            $this->addWidget($widgetObj);
        }
    }

    public function getName()
    {
        return "basewidget";
    }

    public function widget($widget)
    {
        // If we need a 'static' widget, we can just retun the output here and we're done
        if (!empty($widget['static'])) {
            return $widget['static'];
        }

        // fetch the configured record, if any
        if (!empty($widget['record'])) {
            list($ct, $slug) = explode('/', $widget['record']);
            $key = is_numeric($slug) ? 'id' : 'slug';
            $record = $this->app['storage']->getContent($ct, [$key => $slug, 'returnsingle' => true]);
        } elseif (!empty($widget['content'])) {
            $record = $widget['content'];
        } else {
            $record = [];
        }

        // Add the `widgets/` path, so it can be overridden in themes
        $this->app['twig.loader.filesystem']->addPath(__DIR__ . '/widgets');

        // Data to pass into the widget
        $data = ['record' => $record, 'widget' => $widget ];

        // Render the template, and return the results
        return $this->app['render']->render($widget['template'], $data);
    }


}






