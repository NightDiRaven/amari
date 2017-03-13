<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 013 13.03.17
 * Time: 22:48
 */

namespace Tests\Unit\Sluggable\Traits;


use Tests\TestCase;

class Sluggable extends TestCase
{
    public function testScopeGetSlug()
    {
        $mock = $this->getMockForTrait(\Amari\Sluggable\Traits\Sluggable::class);

    }
}