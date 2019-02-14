<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
namespace piko;

/**
 * Base application router.
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Router extends Component
{
    /**
     * @var array name-value pair uri to routes correspondance.
     * Each name corresponds to a regular expression of the request uri.
     * Each value corresponds to a route replacement.
     *
     * eg. '^/about$' => 'site/default/about' means all requests corresponding to
     * '/about' will be treated in 'about' action in the 'defaut' controller of 'site' module.
     *
     * eg. '^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3' means uri part 1 is the module id,
     * part 2, the controller id and part 3 the action id.
     *
     * Also route parameters could be given using pipe character after route.
     *
     * eg. '^/user/(\d+)' => 'site/user/view|id=$1' The router will populate $_GET
     * with 'id' = The user id in the uri.
     *
     * @see preg_replace()
     */
    public $routes = [];

    /**
     * Resolve the application route corresponding to the request uri.
     * The expected route scheme is : '{moduleId}/{controllerId}/{ationId}'
     * @return string The route.
     */
    public function resolve()
    {
        $route = '';
        $uri = str_replace(Piko::getAlias('@web'), '', $_SERVER['REQUEST_URI']);

        if (($start = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $start - 1);
        }

        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $uriPattern => $routePattern) {
            if (preg_match('`' . $uriPattern . '`', $uri, $matches)) {
                $route = preg_replace('`' . $uriPattern . '`', $routePattern, $uri);
                break;
            }
        }

        // Parse route request parameters
        if (($start = strpos($route, '|')) !== false) {
            parse_str(substr($route, $start + 1), $params);

            foreach ($params as $k => $v) {
                $_GET[$k] = $v;
            }

            $route = substr($route, 0, $start);
        }

        return $route;
    }
}
