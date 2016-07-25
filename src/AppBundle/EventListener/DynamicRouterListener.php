<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;

class DynamicRouterListener extends RouterListener
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    public function __construct(Kernel $kernel, RequestStack $requestStack)
    {
        $this->routes = new RouteCollection();
        parent::__construct(
            new UrlMatcher($this->routes, new RequestContext()),
            $requestStack
        );

        // get current path, split it and check if there is 3 parts according to rule: {bundle_name}/{controller}/{action}
        $path = trim($requestStack->getCurrentRequest()->getPathInfo(), "/");
        $parts = explode('/', $path);
        if (sizeof($parts) == 2) {
            $parts[] = 'index';
        }
        if (sizeof($parts) < 3) {
            throw new NotFoundHttpException('No route found');
        }

        $bundleName = ucfirst($parts[0]).'Bundle';
        $controllerName = ucfirst($parts[1]);
        $func = $parts[2];

        // check if the bundle exists
        $bundles = $kernel->getBundles();
        if (!isset($bundles[$bundleName])) {
            throw new NotFoundHttpException('No bundle found');
        }

        // check if the controller exists
        $dir = $bundles[$bundleName]->getPath();
        $file = $dir . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . $controllerName . 'Controller.php';
        if (!file_exists($file)) {
            throw new NotFoundHttpException('No controller found');
        }

        // check if the action exists
        $content = file_get_contents($file);
        if (!preg_match('/function\s+'.$func.'Action\s*\(/i', $content)) {
            throw new NotFoundHttpException('No action found');
        }

        $this->routes->add(
            'dynamic_route_' . ($this->routes->count() + 1),
            new Route(
                $path,
                $defaults = [
                    '_controller' => $bundleName.':'.$controllerName.':'.$func
                ]
            )
        );
    }
}