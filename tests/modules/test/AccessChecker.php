<?php
namespace tests\modules\test;

class AccessChecker
{
    public function checkAccess($userId, $permission)
    {
        return $userId == 1 && $permission == 'test';
    }
}