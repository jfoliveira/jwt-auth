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

namespace Tymon\JWTAuth\Test;

use Mockery;
use Tymon\JWTAuth\Factory;
use Tymon\JWTAuth\JWTGuard;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Test\Stubs\LaravelUserStub;

class JWTGuardTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->jwt = Mockery::mock('Tymon\JWTAuth\JWT');
        $this->provider = Mockery::mock('Illuminate\Contracts\Auth\UserProvider');
        $this->guard = new JWTGuard($this->jwt, $this->provider, Request::create('/foo', 'GET'));
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_get_the_authenticated_user_if_a_valid_token_is_provided()
    {
        $this->jwt->shouldReceive('getToken')->once()->andReturn('foo.bar.baz');
        $this->jwt->shouldReceive('check')->once()->andReturn(true);
        $this->jwt->shouldReceive('getPayload->get')
                  ->once()
                  ->with('sub')
                  ->andReturn(1);

        $this->provider->shouldReceive('retrieveById')
                       ->once()
                       ->with(1)
                       ->andReturn((object) ['id' => 1]);

        $this->assertSame(1, $this->guard->user()->id);

        // check that the user is stored on the object next time round
        $this->assertSame(1, $this->guard->user()->id);
    }

    /** @test */
    public function it_should_return_null_if_an_invalid_token_is_provided()
    {
        $this->jwt->shouldReceive('getToken')->once()->andReturn('invalid.token.here');
        $this->jwt->shouldReceive('check')->once()->andReturn(false);
        $this->jwt->shouldReceive('getPayload->get')->never();
        $this->provider->shouldReceive('retrieveById')->never();

        $this->assertNull($this->guard->user());
    }

    /**
     * @test
     * @expectedException \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function it_should_throw_an_exception_if_no_token_is_provided()
    {
        $this->jwt->shouldReceive('getToken')->andReturn(false);
        $this->jwt->shouldReceive('check')->never();
        $this->jwt->shouldReceive('getPayload->get')->never();
        $this->provider->shouldReceive('retrieveById')->never();

        $this->guard->user();
    }

    /** @test */
    public function it_should_return_a_token_if_credentials_are_ok_and_user_is_found()
    {
        $credentials = ['foo' => 'bar', 'baz' => 'bob'];
        $user = new LaravelUserStub;

        $this->provider->shouldReceive('retrieveByCredentials')
                       ->once()
                       ->with($credentials)
                       ->andReturn($user);

        $this->provider->shouldReceive('validateCredentials')
                       ->once()
                       ->with($user, $credentials)
                       ->andReturn(true);

        $this->jwt->shouldReceive('fromUser')
                  ->once()
                  ->with($user)
                  ->andReturn('foo.bar.baz');

        $token = $this->guard->attempt($credentials);

        $this->assertSame($token, 'foo.bar.baz');
    }

    /** @test */
    public function it_should_return_true_if_credentials_are_ok_and_user_is_found_when_choosing_not_to_login()
    {
        $credentials = ['foo' => 'bar', 'baz' => 'bob'];
        $user = new LaravelUserStub;

        $this->provider->shouldReceive('retrieveByCredentials')
                       ->twice()
                       ->with($credentials)
                       ->andReturn($user);

        $this->provider->shouldReceive('validateCredentials')
                       ->twice()
                       ->with($user, $credentials)
                       ->andReturn(true);

        $this->assertTrue($this->guard->attempt($credentials, false)); // once
        $this->assertTrue($this->guard->validate($credentials)); // twice
    }

    /** @test */
    public function it_should_return_false_if_credentials_are_invalid()
    {
        $credentials = ['foo' => 'bar', 'baz' => 'bob'];
        $user = new LaravelUserStub;

        $this->provider->shouldReceive('retrieveByCredentials')
                       ->once()
                       ->with($credentials)
                       ->andReturn($user);

        $this->provider->shouldReceive('validateCredentials')
                       ->once()
                       ->with($user, $credentials)
                       ->andReturn(false);

        $this->assertFalse($this->guard->attempt($credentials));
    }

    /** @test */
    public function it_should_magically_call_the_jwt_instance()
    {
        $this->jwt->shouldReceive('factory')->andReturn(Mockery::mock(Factory::class));
        $this->assertInstanceOf(Factory::class, $this->guard->factory());
    }

    /** @test */
    public function it_should_logout_the_user_by_invalidating_the_token()
    {
        $this->jwt->shouldReceive('getToken')->once()->andReturn(true);
        $this->jwt->shouldReceive('invalidate')->once()->andReturn(true);
        $this->jwt->shouldReceive('unsetToken')->once();

        $this->guard->logout();
        $this->assertNull($this->guard->getUser());
    }

    /** @test */
    public function it_should_refresh_the_token()
    {
        $this->jwt->shouldReceive('getToken')->once()->andReturn(true);
        $this->jwt->shouldReceive('refresh')->once()->andReturn('foo.bar.baz');

        $this->assertSame($this->guard->refresh(), 'foo.bar.baz');
    }

    /** @test */
    public function it_should_generate_a_token_by_id()
    {
        $user = new LaravelUserStub;

        $this->provider->shouldReceive('retrieveById')
                       ->once()
                       ->with(1)
                       ->andReturn($user);

        $this->jwt->shouldReceive('fromUser')
                  ->once()
                  ->with($user)
                  ->andReturn('foo.bar.baz');

        $this->assertSame('foo.bar.baz', $this->guard->tokenById(1));
    }
}
