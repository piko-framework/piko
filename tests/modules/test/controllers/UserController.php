<?php
namespace Piko\Tests\modules\test\controllers;

use Piko\Tests\lib\UserService;

class UserController extends \Piko\Controller
{
    public function __construct(protected UserService $user)
    {
    }

    public function indexAction()
    {
        return '';
    }
}
