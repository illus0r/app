<?php

namespace Brick\App\Tests;

use Brick\App\Application;
use Brick\App\Route;
use Brick\App\RouteMatch;
use Brick\Http\MessageBodyString;
use Brick\Http\Request;
use Brick\Http\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param Response $response
     *
     * @return ResponseAssertion
     */
    private function assertResponse(Response $response)
    {
        return new ResponseAssertion($this, $response);
    }

    /**
     * @param int      $statusCode
     * @param Response $response
     */
    private function assertStatusCode($statusCode, Response $response)
    {
        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function testNoRouteReturns404()
    {
        $application = Application::create();
        $response = $application->handle(new Request());
        $this->assertStatusCode(404, $response);
    }

    public function testRouting()
    {
        $application = Application::create();
        $application->addRoute(new HelloRoute());

        $request = new Request();
        $this->assertResponse($application->handle($request))
            ->hasStatusCode(404);

        $request->setPath('/a');
        $this->assertResponse($application->handle($request))
            ->hasStatusCode(200)
            ->hasBody('Hello');

        $request->setPath('/b');
        $this->assertResponse($application->handle($request))
            ->hasStatusCode(200)
            ->hasBody('World');

        $request->setPath('/c');
        $this->assertResponse($application->handle($request))
            ->hasStatusCode(404);
    }
}

class HelloRoute implements Route
{
    public function match(Request $request)
    {
        if ($request->getPath() === '/a') {
            return RouteMatch::forFunction(function() {
                return (new Response())->setBody(new MessageBodyString('Hello'));
            });
        }

        if ($request->getPath() === '/b') {
            return RouteMatch::forFunction(function() {
                return (new Response())->setBody(new MessageBodyString('World'));
            });
        }

        return null;
    }
}
