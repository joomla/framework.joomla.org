<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\EventListener;

use Joomla\Application\Event\ApplicationErrorEvent;
use Joomla\FrameworkWebsite\EventListener\ErrorSubscriber;
use Joomla\FrameworkWebsite\Tests\Stub\TestWebApplication;
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Exception\MethodNotAllowedException;
use Joomla\Router\Exception\RouteNotFoundException;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Tests for the error subscriber
 */
final class ErrorSubscriberTest extends TestCase
{
    /**
     * @return  array<string, array{\Throwable, bool, int, string}>
     */
    public static function jsonErrorProvider(): array
    {
        $internal = new \RuntimeException('SQLSTATE[HY000] [1045] Access denied for user "framework"@"localhost"', 1045);

        return [
            'not found is public'               => [new RouteNotFoundException('Route `foo` not found', 404), false, 404, 'Route `foo` not found'],
            'method not allowed is public'      => [new MethodNotAllowedException(['GET'], 'Method not allowed'), false, 405, 'Method not allowed'],
            'internal error is hidden'          => [$internal, false, 500, 'An error occurred while processing this request.'],
            'internal error is shown in debug'  => [$internal, true, 500, $internal->getMessage()],
        ];
    }

    #[DataProvider('jsonErrorProvider')]
    public function testJsonErrorsOnlyExposeDetailsForPublicErrorsOrInDebugMode(\Throwable $error, bool $debug, int $status, string $message): void
    {
        $app = new TestWebApplication(['_format' => 'json'], ['debug' => $debug]);

        $this->createSubscriber()->handleWebError(new ApplicationErrorEvent($error, $app));

        $response = $app->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($status, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertSame($message, $data['message']);
        $this->assertTrue($data['error']);

        // The code is only exposed together with the message, an internal code like a SQL error number is never public
        $this->assertSame($debug || \in_array($error->getCode(), [404, 405], true) ? $error->getCode() : 500, $data['code']);
    }

    public function testJsonResponseIsUsedForJsonMimeType(): void
    {
        $app           = new TestWebApplication();
        $app->mimeType = 'application/json';

        $this->createSubscriber()->handleWebError(new ApplicationErrorEvent(new RouteNotFoundException('Not found', 404), $app));

        $this->assertInstanceOf(JsonResponse::class, $app->getResponse());
    }

    public function testHtmlErrorPageIsRenderedWithStatusCode(): void
    {
        $error    = new RouteNotFoundException('Not found', 404);
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->expects($this->once())
            ->method('render')
            ->with('exception.twig', ['exception' => $error])
            ->willReturn('<h1>Not found</h1>');

        $app = new TestWebApplication();
        $this->createSubscriber($renderer)->handleWebError(new ApplicationErrorEvent($error, $app));

        $response = $app->getResponse();
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('<h1>Not found</h1>', (string) $response->getBody());
    }

    public function testMethodNotAllowedSetsAllowHeader(): void
    {
        $app = new TestWebApplication();

        $this->createSubscriber()->handleWebError(new ApplicationErrorEvent(new MethodNotAllowedException(['GET', 'HEAD'], 'Method not allowed'), $app));

        $this->assertSame(405, $app->getResponse()->getStatusCode());
        $this->assertContains(['name' => 'Allow', 'value' => 'GET, HEAD'], $app->getHeaders());
    }

    public function testUnknownErrorCodesBecomeInternalServerErrors(): void
    {
        $app = new TestWebApplication();

        $this->createSubscriber()->handleWebError(new ApplicationErrorEvent(new \RuntimeException('Teapot', 418), $app));

        $this->assertSame(500, $app->getResponse()->getStatusCode());
    }

    private function createSubscriber(?RendererInterface $renderer = null): ErrorSubscriber
    {
        if ($renderer === null) {
            $renderer = $this->createStub(RendererInterface::class);
            $renderer->method('render')->willReturn('error page');
        }

        $subscriber = new ErrorSubscriber($renderer);
        $subscriber->setLogger(new NullLogger());

        return $subscriber;
    }
}
