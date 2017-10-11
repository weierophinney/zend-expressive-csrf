<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-csrf for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-csrf/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Csrf;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface as DelegateInterface;
use Zend\Expressive\Csrf\CsrfGuardFactoryInterface;
use Zend\Expressive\Csrf\CsrfGuardInterface;
use Zend\Expressive\Csrf\CsrfMiddleware;

use const Webimpress\HttpMiddlewareCompatibility\HANDLER_METHOD;

class CsrfMiddlewareTest extends TestCase
{
    public function setUp()
    {
        $this->guardFactory = $this->prophesize(CsrfGuardFactoryInterface::class);
    }

    public function testConstructorUsesSaneAttributeKeyByDefault()
    {
        $middleware = new CsrfMiddleware($this->guardFactory->reveal());
        $this->assertAttributeSame($this->guardFactory->reveal(), 'guardFactory', $middleware);
        $this->assertAttributeSame($middleware::GUARD_ATTRIBUTE, 'attributeKey', $middleware);
    }

    public function testConstructorAllowsProvidingAlternateAttributeKey()
    {
        $middleware = new CsrfMiddleware($this->guardFactory->reveal(), 'alternate-key');
        $this->assertAttributeSame($this->guardFactory->reveal(), 'guardFactory', $middleware);
        $this->assertAttributeSame('alternate-key', 'attributeKey', $middleware);
    }

    public function attributeKeyProvider()
    {
        return [
            'null-default' => [null],
            'custom'       => ['alternate-key'],
        ];
    }

    /**
     * @dataProvider attributeKeyProvider
     */
    public function testProcessDelegatesNewRequestContainingGeneratedGuardInstance($attributeKey)
    {
        $guard = $this->prophesize(CsrfGuardInterface::class)->reveal();
        $request = $this->prophesize(ServerRequestInterface::class);
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $middleware = $attributeKey
            ? new CsrfMiddleware($this->guardFactory->reveal(), $attributeKey)
            : new CsrfMiddleware($this->guardFactory->reveal());

        $attributeKey = $attributeKey ?: CsrfMiddleware::GUARD_ATTRIBUTE;

        $this->guardFactory->createGuardFromRequest($request->reveal())->willReturn($guard);
        $request->withAttribute($attributeKey, $guard)->will([$request, 'reveal']);

        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->{HANDLER_METHOD}(Argument::that([$request, 'reveal']))->willReturn($response);

        $this->assertSame($response, $middleware->process($request->reveal(), $delegate->reveal()));
    }
}
