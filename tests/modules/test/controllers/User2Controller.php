<?php
namespace Piko\Tests\modules\test\controllers;

use Piko\Tests\lib\UserService;

class User2Controller extends \Piko\Controller
{
    public $layout = false;

    public function __construct(protected ?UserService $user = null)
    {
    }

    public function indexAction()
    {
        return 'TestModule::User2Controller::indexAction';
    }
}
