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

namespace Tymon\JWTAuth\Http;

use Illuminate\Http\Request;

class Parser
{
    /**
     * @var array
     */
    private $chain;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @param \Illuminate\Http\Request $request
     * @param array $chain
     */
    public function __construct(Request $request, array $chain = [])
    {
        $this->request = $request;
        $this->chain = $chain;
    }

    /**
     * Get the parser chain.
     *
     * @return array The chain of ParserContracts that the parser evaluates.
     */
    public function getChain()
    {
        return $this->chain;
    }

    /**
     * Set the order of the parser chain.
     *
     * @param array $chain
     * @return $this
     */
    public function setChain(array $chain)
    {
        $this->chain = $chain;

        return $this;
    }

    /**
     * Alias for setting the order of the chain.
     *
     * @param array $chain
     * @return $this
     */
    public function setChainOrder(array $chain)
    {
        $this->setChain($chain);

        return $this;
    }

    /**
     * Iterate through the parsers and attempt to retrieve
     * a value, otherwise return null.
     *
     * @return string|null
     */
    public function parseToken()
    {
        foreach ($this->chain as $parser) {
            $response = $parser->parse($this->request);

            if ($response !== null) {
                return $response;
            }
        }

        return;
    }

    /**
     * Check whether a token exists in the chain.
     *
     * @return  bool
     */
    public function hasToken()
    {
        return $this->parseToken() !== null;
    }

    /**
     * Set the request instance.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
