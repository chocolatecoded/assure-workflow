<?php

namespace Assure\Workflow\Contracts;

interface WorkflowConfigInterface
{
    public function get(string $key, $default = null);
}

