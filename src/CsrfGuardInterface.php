<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-csrf for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-csrf/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Csrf;

interface CsrfGuardInterface
{
    /**
     * Generate a CSRF token.
     *
     * Typically, implementations should generate a one-time CSRF token,
     * store it within the session, and return it so that developers may
     * then inject it in a form, a response header, etc.
     *
     * CSRF tokens should EXPIRE after the first hop.
     */
    public function generateToken(string $keyName = '__csrf') : string;

    /**
     * Validate whether a submitted CSRF token is the same as the one stored in
     * the session.
     *
     * CSRF tokens should EXPIRE after the first hop.
     */
    public function validateToken(string $token, string $csrfKey = '__csrf') : bool;
}
