# Testing Guide for Assure Workflow Package

## Setup

1. Install dependencies:
```bash
cd assure-workflow
composer install
```

2. Run tests:
```bash
./vendor/bin/phpunit
```

Or with verbose output:
```bash
./vendor/bin/phpunit --verbose
```

## Writing Tests

### Test File Structure

All test files should:
- Be placed in the `tests/` directory
- End with `Test.php` (e.g., `WorkflowEngineTest.php`)
- Be in the `Assure\Workflow\Tests` namespace
- Extend `PHPUnit\Framework\TestCase`

### Example Test

```php
<?php

namespace Assure\Workflow\Tests;

use Assure\Workflow\Services\WorkflowEngine;
use PHPUnit\Framework\TestCase;
use Mockery;

class MyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testSomething()
    {
        // Your test code here
        $this->assertTrue(true);
    }
}
```

### Testing Static Methods

For testing static methods like `validateWorkflowStep`, you can use Mockery to mock Eloquent models:

```php
// Mock a model
$mockModel = Mockery::mock('alias:' . WorkflowFormSubmission::class);
$mockModel->shouldReceive('where')
    ->once()
    ->andReturnSelf();
$mockModel->shouldReceive('first')
    ->once()
    ->andReturn(null);
```

### Running Specific Tests

Run a specific test file:
```bash
./vendor/bin/phpunit tests/WorkflowEngineTest.php
```

Run a specific test method:
```bash
./vendor/bin/phpunit --filter testStartCreatesInstance
```

### Code Coverage

Generate code coverage report:
```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## Best Practices

1. **One test per behavior**: Each test should verify one specific behavior
2. **Use descriptive names**: Test method names should clearly describe what they test
3. **Arrange-Act-Assert**: Structure your tests with clear sections
4. **Mock external dependencies**: Use Mockery to mock Eloquent models and external services
5. **Clean up**: Always call `Mockery::close()` in `tearDown()` when using Mockery

## Common Patterns

### Testing Model Creation
```php
$model = Mockery::mock('alias:' . MyModel::class);
$model->shouldReceive('create')
    ->once()
    ->with(['key' => 'value'])
    ->andReturn(new MyModel());
```

### Testing Database Queries
```php
$query = Mockery::mock();
$query->shouldReceive('where')->andReturnSelf();
$query->shouldReceive('first')->andReturn($result);

$model = Mockery::mock('alias:' . MyModel::class);
$model->shouldReceive('where')->andReturn($query);
```


