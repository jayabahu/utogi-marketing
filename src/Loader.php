<?php

namespace UtogiMarketing;

class Loader
{

    protected $actions;
    protected $filters;

    public function __construct()
    {
        $this->actions = [];
        $this->filters = [];
    }

    public function addAction($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    public function addFilter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {
        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );
        return $hooks;
    }

    public function load()
    {
        foreach ($this->filters as $filter) {
            add_filter($filter['hook'], array($filter['component'], $filter['callback']), $filter['priority'], $filter['accepted_args']);
        }
        foreach ($this->actions as $action) {
            add_action($action['hook'], array($action['component'], $action['callback']), $action['priority'], $action['accepted_args']);
        }
    }
}