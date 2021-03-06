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

namespace Tymon\JWTAuth\Validators;

use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class TokenValidator extends Validator
{
    /**
     * Check the structure of the token.
     *
     * @param string  $value
     *
     * @return void
     */
    public function check($value)
    {
        $this->validateStructure($value);
    }

    /**
     * @param  string  $token
     *
     * @throws \Tymon\JWTAuth\Exceptions\TokenInvalidException
     *
     * @return bool
     */
    protected function validateStructure($token)
    {
        if (count(explode('.', $token)) !== 3) {
            throw new TokenInvalidException('Wrong number of segments');
        }

        return true;
    }
}
