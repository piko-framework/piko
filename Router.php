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
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Router extends Component
{
    /**
     * Name-value pair uri to routes correspondance.
     *
     * Each name corresponds to a regular expression of the request uri.
     * Each value corresponds to a route replacement.
     *
     * eg. `'^/about$' => 'site/default/about'` means all requests corresponding to
     * '/about' will be treated in 'about' action in the 'defaut' controller of 'site' module.
     *
     * eg. `'^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3'` means uri part 1 is the module id,
     * part 2, the controller id and part 3 the action id.
     *
     * Also route parameters could be given using pipe character after route.
     *
     * eg. `'^/user/(\d+)' => 'site/user/view|id=$1'` The router will populate `$_GET`
     * with 'id' = The user id in the uri.
     *
     * @var array
     * @see preg_replace()
     */
    public $routes = [];

    /**
     * Resolve the application route corresponding to the request uri.
     * The expected route scheme is : '{moduleId}/{controllerId}/{ationId}'
     *
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
            if (preg_match('`' . $uriPattern . '`', $uri)) {
                $route = preg_replace('`' . $uriPattern . '`', $routePattern, $uri);
                break;
            }
        }

        // Parse route request parameters
        if (($start = strpos($route, '|')) !== false) {
            $params = [];
            parse_str(substr($route, $start + 1), $params);

            foreach ($params as $k => $v) {
                $_GET[$k] = $v;
            }

            $route = substr($route, 0, $start);
        }

        return $route;
    }

    /**
     * Convert a route to an url.
     *
     * @param string $route The route given as '{moduleId}/{controllerId}/{ationId}'.
     * @param array $params Optional query parameters.
     * @return string The url.
     */
    public function getUrl($route, $params = [])
    {
        $uri = '';

        foreach ($this->routes as $uriPattern => $routePattern) {

            $strParams = '';
            $replacements = [];

            // Extract params from $routePattern string
            if (($pos = strpos($routePattern, '|')) !== false) {
                $strParams = substr($routePattern, $pos + 1);
                $routePattern = substr($routePattern, 0, $pos);
            }

            // Looking for dynamic route
            if (strpos($routePattern, '$') !== false) {

                $routeParts = explode('/', $route);
                $routePatternParts = explode('/', $routePattern);
                $matches = [];

                foreach ($routePatternParts as $k => &$part) {

                    if (isset($routeParts[$k]) && preg_match('/\$([0-9]+)/', $part, $matches)) {
                        $replacements[(int) $matches[1]] = $routeParts[$k];
                        $part = $routeParts[$k];
                    }
                }

                $routePattern = implode('/', $routePatternParts);
            }

            if ($route == $routePattern) {
                // Looking for parameters substitutions
                if (!empty($params) && !empty($strParams)) {
                    $res = [];
                    parse_str($strParams, $res);
                    $diff = false;

                    foreach ($res as $k => $v) {

                        if (!isset($params[$k]) || (strpos($v, '$') === false && $params[$k] != $v)) {
                            $diff = true;
                            break;
                        }

                        $pos = (int) str_replace('$', '', $v);
                        $replacements[$pos] =  $params[$k];
                    }

                    // If params differ, route doesn't match
                    if ($diff) {
                        continue;
                    }
                }

                if (!empty($replacements)) {

                    $uriPattern = preg_replace_callback('`\(.*?\)`', function ($matches) use ($replacements) {
                        static $count = 1;
                        $value = $replacements[$count];
                        $count++;
                        return $value;
                    }, $uriPattern);
                }

                $uri = str_replace(['^', '$'], '', $uriPattern);

                if (!empty($params) && empty($strParams)) {
                    $uri .= '/?' . http_build_query($params);
                }

                break;
            }
        }

        $this->trigger('afterBuildUri', [&$uri]);

        return Piko::getAlias('@web') . $uri;
    }
}
