<?php

use ManaPHP\Di;

if (!function_exists('html')) {
    /**
     * @param string $name
     * @param array  $data
     *
     * @return string
     */
    function html($name, $data = [])
    {
        return di('html')->render($name, $data);
    }
}

if (!function_exists('attr_nv')) {
    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    function attr_nv($name, $default = '')
    {
        return sprintf('name="%s" value="%s"', $name, e(input($name, $default)));
    }
}

if (!function_exists('attr_inv')) {
    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    function attr_inv($name, $default = '')
    {
        if ($pos = strpos($name, '[')) {
            $id = substr($name, $pos + 1, -1);
        } else {
            $id = $name;
        }

        return sprintf('id="%s" name="%s" value="%s"', $id, $name, e(input($name, $default)));
    }
}

if (!function_exists('widget')) {
    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string|array
     */
    function widget($name, $vars = [])
    {
        return Di::getDefault()->view->widget($name, $vars);
    }
}

if (!function_exists('partial')) {
    /**
     * @param string $path
     * @param array  $vars
     *
     * @return void
     */
    function partial($path, $vars = [])
    {
        Di::getDefault()->view->partial($path, $vars);
    }
}

if (!function_exists('block')) {
    /**
     * @param string $path
     * @param array  $vars
     *
     * @return void
     */
    function block($path, $vars = [])
    {
        Di::getDefault()->view->block($path, $vars);
    }
}

if (!function_exists('pager')) {
    /**
     * @param \ManaPHP\Paginator|string $template
     * @param \ManaPHP\Paginator|string $pagination
     *
     * @return string
     */
    function pager($template = null, $pagination = null)
    {
        /** @noinspection SuspiciousAssignmentsInspection */
        $template = $pagination = null;

        foreach (func_get_args() as $value) {
            if (is_string($value)) {
                $template = $value;
            } elseif (is_object($value)) {
                $pagination = $value;
            }
        }

        if (!$pagination) {
            $pagination = di('paginator');
        }

        return $pagination->renderAsHtml($template);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * @return string
     */
    function csrf_token()
    {
        return di('csrfPlugin')->get();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * @return string
     */
    function csrf_field()
    {
        $csrfToken = di('csrfPlugin');
        return sprintf('<input type="hidden" name="%s" value="%s" />', $csrfToken->getName(), $csrfToken->get());
    }
}

if (!function_exists('bundle')) {
    /**
     * @param array  $files
     * @param string $name
     *
     * @return string
     */
    function bundle($files, $name = 'app')
    {
        return di('assetBundle')->bundle($files, $name);
    }
}

if (!function_exists('action')) {

    /**
     * @param array|string $args
     * @param bool|string  $scheme
     *
     * @return string
     */
    function action($args = [], $scheme = false)
    {
        static $router;
        if (!$router) {
            $router = Di::getDefault()->router;
        }
        return $router->createUrl($args, $scheme);
    }
}

if (!function_exists('url')) {
    /**
     * @param string|array $args
     * @param bool|string  $scheme
     *
     * @return string
     */
    function url($args, $scheme = false)
    {
        return di('url')->get($args, $scheme);
    }
}

if (!function_exists('asset')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function asset($path)
    {
        static $alias;
        if (!$alias) {
            $alias = di('alias');
        }

        if (strpos($path, '?') === false && is_file($file = $alias->resolve("@public{$path}"))) {
            return $alias->resolve("@asset{$path}") . '?' . substr(md5_file($file), 0, 12);
        } else {
            return $alias->resolve("@asset{$path}");
        }
    }
}