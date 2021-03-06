<?php
namespace ManaPHP\Swoole\Http\Server;

use ManaPHP\Http\Response;
use ManaPHP\Rest\Factory;
use ManaPHP\Router\NotFoundRouteException;

/**
 * Class ManaPHP\Mvc\Application
 *
 * @package application
 * @property-read \ManaPHP\AuthorizationInterface      $authorization
 * @property-read \ManaPHP\Http\RequestInterface       $request
 * @property-read \ManaPHP\Http\ResponseInterface      $response
 * @property-read \ManaPHP\Http\CookiesInterface       $cookies
 * @property-read \ManaPHP\RouterInterface             $router
 * @property-read \ManaPHP\DispatcherInterface         $dispatcher
 * @property-read \ManaPHP\Http\SessionInterface       $session
 * @property-read \ManaPHP\Swoole\Http\ServerInterface $swooleHttpServer
 */
class Application extends \ManaPHP\Application
{
    public function getDi()
    {
        if (!$this->_di) {
            $this->_di = new Factory();
            $this->_di->keepInstanceState(true);
            $this->_di->setShared('swooleHttpServer', 'ManaPHP\Swoole\Http\Server');
        }

        return $this->_di;
    }

    public function send()
    {
        $swoole = $this->swooleHttpServer;
        $response = $this->response;
        $cookies = $this->cookies;

        if (isset($_SERVER['HTTP_X_REQUEST_ID']) && !$response->hasHeader('X-Request-Id')) {
            $response->setHeader('X-Request-Id', $_SERVER['HTTP_X_REQUEST_ID']);
        }

        $response->setHeader('X-Response-Time', sprintf('%.3f', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));

        $this->eventsManager->fireEvent('response:beforeSend', $response);

        $swoole->setStatus($response->getStatusCode());
        $swoole->sendHeaders($response->getHeaders());

        $this->eventsManager->fireEvent('cookies:beforeSend', $cookies);
        $swoole->sendCookies($this->cookies->get(null));
        $this->eventsManager->fireEvent('cookies:afterSend', $cookies);

        if ($file = $response->getFile()) {
            $swoole->sendFile($file);
        } else {
            $swoole->sendContent($response->getContent());
        }

        $this->eventsManager->fireEvent('response:afterSend', $response);
    }

    public function handle()
    {
        try {
            $request_uri = $_SERVER['REQUEST_URI'];
            $_GET['_url'] = ($pos = strpos($request_uri, '?')) ? substr($request_uri, 0, $pos) : $request_uri;

            $this->fireEvent('app:beginRequest');

            $this->authenticate();

            if (!$this->router->match()) {
                throw new NotFoundRouteException(['router does not have matched route for `:uri`', 'uri' => $this->router->getRewriteUri()]);
            }

            $this->dispatcher->dispatch($this->router);
            $actionReturnValue = $this->dispatcher->getReturnedValue();
            if ($actionReturnValue !== null && !$actionReturnValue instanceof Response) {
                $this->response->setJsonContent($actionReturnValue);
            }
        } catch (\Exception $exception) {
            $this->handleException($exception);
        } catch (\Error $error) {
            $this->handleException($error);
        }

        $this->send();

        $this->fireEvent('app:endRequest');

        $this->_di->restoreInstancesState();
    }

    public function main()
    {
        $this->dotenv->load();
        $this->configure->load();

        $this->registerServices();

        $this->fireEvent('app:start');

        $this->swooleHttpServer->start([$this, 'handle']);
    }
}