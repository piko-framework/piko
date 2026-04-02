<?php
namespace Piko\Tests\modules\test\controllers;

use Piko\Tests\lib\TestDependency;
use Piko\Tests\lib\TestMissingDependency;
use Piko\Tests\lib\TestNullableDependency;
use Piko\Tests\lib\TestOptionalVariadic;

class TestController extends \Piko\Controller
{
    public $layout = false;

    protected \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function indexAction()
    {
        return 'TestModule::TestController::indexAction';
    }

    public function errorAction(\Throwable $exception)
    {
        if ($exception instanceof \Throwable) {
            return $exception->getMessage();
        }
    }

    public function index2Action()
    {
        $this->layout = 'main';

        return 'TestModule::TestController::index2Action';
    }

    public function index3Action(int $id = 0)
    {
        return $id;
    }

    public function index4Action(float $lat = 0.0, float $lng = 0.0, bool $coordinates = false)
    {
        if ($coordinates) {
            return (string) $lat . '/' . (string) $lng;
        }

        return '';
    }

    public function createObjectAction()
    {
        $dependency = $this->create(TestDependency::class, ['id' => 12]);

        if ($dependency instanceof TestDependency && $dependency->db instanceof \PDO && $dependency->id === 12) {
            return 'TestModule::TestController::createObjectAction';
        }

        return '';
    }

    public function createObjectMissingDependencyAction()
    {
        $this->create(TestMissingDependency::class, ['id' => 10]);

        return '';
    }

    public function createObjectNullableDependencyAction()
    {
        $dependency = $this->create(TestNullableDependency::class);

        if ($dependency instanceof TestNullableDependency && $dependency->user === null) {
            return 'TestModule::TestController::createObjectNullableDependencyAction';
        }

        return '';
    }

    public function createObjectOptionalDependencyAction()
    {
        $dependency = $this->create(TestOptionalVariadic::class);

        if ($dependency instanceof TestOptionalVariadic && count($dependency->args) === 1 && $dependency->args[0] === null) {
            return 'TestModule::TestController::createObjectOptionalDependencyAction';
        }

        return '';
    }
}
