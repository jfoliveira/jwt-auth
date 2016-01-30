<?php

/*
 * This file is part of jwt-auth.
 *
 * @author Sean Tymon <tymon148@gmail.com>
 * @copyright Copyright (c) Sean Tymon
 * @link https://github.com/tymondesigns/jwt-auth
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Test\Providers\Auth;

use Mockery;
use Tymon\JWTAuth\Providers\Auth\Sentinel as Auth;
use Tymon\JWTAuth\Test\Stubs\SentinelStub;

class SentinelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $sentinel;

    /**
     * @var \Tymon\JWTAuth\Providers\Auth\Sentinel
     */
    protected $auth;

    public function setUp()
    {
        $this->sentinel = Mockery::mock('Cartalyst\Sentinel\Sentinel');
        $this->auth = new Auth($this->sentinel);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_true_if_credentials_are_valid()
    {
        $this->sentinel->shouldReceive('stateless')->once()->with(['email' => 'foo@bar.com', 'password' => 'foobar'])->andReturn(true);
        $this->assertTrue($this->auth->byCredentials(['email' => 'foo@bar.com', 'password' => 'foobar']));
    }

    /** @test */
    public function it_should_return_true_if_user_is_found()
    {
        $stub = new SentinelStub;
        $this->sentinel->shouldReceive('getUserRepository->findById')->once()->with(123)->andReturn($stub);
        $this->sentinel->shouldReceive('setUser')->once()->with($stub);

        $this->assertTrue($this->auth->byId(123));
    }

    /** @test */
    public function it_should_return_false_if_user_is_not_found()
    {
        $this->sentinel->shouldReceive('getUserRepository->findById')->once()->with(321)->andReturn(false);
        $this->sentinel->shouldReceive('setUser')->never();

        $this->assertFalse($this->auth->byId(321));
    }

    /** @test */
    public function it_should_return_the_currently_authenticated_user()
    {
        $this->sentinel->shouldReceive('getUser')->once()->andReturn(new SentinelStub);
        $this->assertSame($this->auth->user()->getUserId(), 123);
    }
}
