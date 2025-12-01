<?php

namespace Assure\Workflow\Services;

use Assure\Workflow\Contracts\WorkflowConfigInterface;

class ConfigurationManager implements WorkflowConfigInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $key, $default = null)
    {
        return array_get($this->config, $key, $default);
    }
}

