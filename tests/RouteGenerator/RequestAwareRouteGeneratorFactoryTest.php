<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\Tests\RouteGenerator;

use BabDev\PagerfantaBundle\RouteGenerator\RequestAwareRouteGeneratorFactory;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RequestAwareRouteGeneratorFactoryTest extends TestCase
{
    /**
     * @var MockObject&UrlGeneratorInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var MockObject&PropertyAccessorInterface
     */
    private $propertyAccessor;

    protected function setUp(): void
    {
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->requestStack = new RequestStack();
        $this->propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
    }

    protected function tearDown(): void
    {
        do {
            $request = $this->requestStack->pop();
        } while (null !== $request);
    }

    public function testTheGeneratorIsCreatedWhenResolvingTheRouteNameFromTheRequest(): void
    {
        $request = Request::create('/');
        $request->attributes->set('_route', 'pagerfanta_view');
        $request->attributes->set('_route_params', []);

        $this->requestStack->push($request);

        $this->assertInstanceOf(
            RouteGeneratorInterface::class,
            $this->createFactory(true)->create()
        );
    }

    public function testTheGeneratorIsCreatedWhenGivenARouteNameDuringASubrequest(): void
    {
        $masterRequest = Request::create('/');
        $masterRequest->attributes->set('_route', 'pagerfanta_view');
        $masterRequest->attributes->set('_route_params', []);

        $subRequest = Request::create('/_internal');

        $this->requestStack->push($masterRequest);
        $this->requestStack->push($subRequest);

        $this->assertInstanceOf(
            RouteGeneratorInterface::class,
            $this->createFactory(true)->create(['routeName' => 'pagerfanta_view'])
        );
    }

    public function testTheGeneratorIsNotCreatedWhenARouteNameIsNotGivenDuringASubrequest(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The request aware route generator can not guess the route when used in a sub-request, pass the "routeName" option to use this generator.');

        $masterRequest = Request::create('/');
        $masterRequest->attributes->set('_route', 'pagerfanta_view');
        $masterRequest->attributes->set('_route_params', []);

        $subRequest = Request::create('/_internal');

        $this->requestStack->push($masterRequest);
        $this->requestStack->push($subRequest);

        $this->createFactory(false)->create();
    }

    private function createFactory(bool $withPropertyAccessor): RequestAwareRouteGeneratorFactory
    {
        return new RequestAwareRouteGeneratorFactory(
            $this->router,
            $this->requestStack,
            $withPropertyAccessor ? $this->propertyAccessor : null
        );
    }
}
