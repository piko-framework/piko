<?php
namespace Piko\Tests\modules\test\controllers;

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
}
