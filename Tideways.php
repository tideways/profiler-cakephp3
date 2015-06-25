<?php

namespace Tideways\Extensions;

class CakePHP3
{
    public static function load()
    {
        if (!class_exists('Tideways\Profiler')) {
            return;
        }

        \Tideways\Profiler::watchCallback(
            'Cake\Controller\Controller::invokeAction',
            function ($context) {
                $controller = $context['object'];

                if (!$controller->request ||
                    !$controller->isAction($controller->request->params['action'])) {
                    return;
                }

                $title = get_class($controller) . '::' . $controller->request->params['action'];

                $span = \Tideways\Profiler::createSpan('php.ctrl');
                $span->annotate(['title' => $title]);

                return $span->getId();
            }
        );

        \Tideways\Profiler::watchCallback(
            'Cake\View\View::render',
            function ($context) {
                $view = ($context['args'][0]) ?: get_class($context['object']);
                $span = \Tideways\Profiler::createSpan('view');
                $span->annotate(['title' => $view]);

                return $span->getId();
            }
        );

        \Tideways\Profiler::watchCallback(
            'Cake\Event\EventManager::dispatch',
            function ($context) {
                $event = is_object($context['args'][0]) ? $context['args'][0]->name() : $context['args'][0];
                $span = \Tideways\Profiler::createSpan('event');
                $span->annotate(['title' => $event]);

                return $span->getId();
            }
        );

        \Tideways\Profiler::watchCallback(
            'Cake\Event\EventManager::_callListener',
            function ($context) {
                $listener = $context['args'][0];
                if (is_array($listener)) {
                    $title = get_class($listener[0]) . '::' . $listener[1];
                } else {
                    return; // only support object listeners
                }

                $span = \Tideways\Profiler::createSpan('php');
                $span->annotate(['title' => $title]);

                return $span->getId();
            }
        );

        \Tideways\Profiler::watch('Cake\ORM\Table::schema');
    }
}

CakePHP3::load();
