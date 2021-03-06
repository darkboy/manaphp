<?php
// This file is not a CODE, it makes no sense and won't run or validate
// Its AST serves IDE as DATA source to make advanced type inference decisions.

namespace PHPSTORM_META {
    $STATIC_METHOD_TYPES = [
        \ManaPHP\DiInterface::getShared('') => [
            'eventsManager' instanceof \ManaPHP\Event\ManagerInterface,
            'alias' instanceof \ManaPHP\AliasInterface,
            'dotenv' instanceof \ManaPHP\DotenvInterface,
            'configure' instanceof \ManaPHP\Configuration\Configure,
            'settings' instanceof \ManaPHP\Configuration\SettingsInterface,
            'errorHandler' instanceof \ManaPHP\ErrorHandlerInterface,
            'router' instanceof \ManaPHP\RouterInterface,
            'dispatcher' instanceof \ManaPHP\Dispatcher,
            'url' instanceof \ManaPHP\UrlInterface,
            'modelsMetadata' instanceof \ManaPHP\Mvc\Model\MetadataInterface,
            'modelsValidator' instanceof \ManaPHP\Model\ValidatorInterface,
            'response' instanceof \ManaPHP\Http\ResponseInterface,
            'cookies' instanceof \ManaPHP\Http\CookiesInterface,
            'request' instanceof \ManaPHP\Http\RequestInterface,
            'filter' instanceof \ManaPHP\Http\FilterInterface,
            'crypt' instanceof \ManaPHP\Security\CryptInterface,
            'flash' instanceof \ManaPHP\View\FlashInterface,
            'flashSession' instanceof \ManaPHP\View\FlashInterface,
            'session' instanceof \ManaPHP\Http\SessionInterface,
            'view' instanceof \ManaPHP\ViewInterface,
            'html' instanceof \ManaPHP\Renderer\HtmlInterface,
            'logger' instanceof \ManaPHP\LoggerInterface,
            'renderer' instanceof \ManaPHP\RendererInterface,
            'cache' instanceof \ManaPHP\CacheInterface,
            'ipcCache' instanceof \ManaPHP\IpcCacheInterface,
            'httpClient' instanceof \ManaPHP\Curl\EasyInterface,
            'captcha' instanceof \ManaPHP\Security\CaptchaInterface,
            'csrfPlugin' instanceof \ManaPHP\Plugins\CsrfPlugin,
            'authorization' instanceof \ManaPHP\AuthorizationInterface,
            'identity' instanceof \ManaPHP\IdentityInterface,
            'paginator' instanceof \ManaPHP\Paginator,
            'filesystem' instanceof \ManaPHP\Filesystem\Adapter\File,
            'random' instanceof \ManaPHP\Security\RandomInterface,
            'messageQueue' instanceof \ManaPHP\Message\QueueInterface,
            'secint' instanceof \ManaPHP\Security\SecintInterface,
            'swordCompiler' instanceof \ManaPHP\Renderer\Engine\Sword\Compiler,
            'tasksManager' instanceof \ManaPHP\Task\ManagerInterface,
            'viewsCache' instanceof \ManaPHP\CacheInterface,
            'htmlPurifier' instanceof \ManaPHP\Security\HtmlPurifierInterface,
            'db' instanceof \ManaPHP\DbInterface,
            'redis' instanceof \ManaPHP\Redis,
            'mongodb' instanceof \ManaPHP\MongodbInterface,
            'translator' instanceof \ManaPHP\I18n\TranslatorInterface,
            'rabbitmq' instanceof \ManaPHP\AmqpInterface,
            'relationsManager' instanceof \ManaPHP\Model\Relation\Manager,
            'di' instanceof \ManaPHP\Di | \ManaPHP\DiInterface,
            'application' instanceof \ManaPHP\ApplicationInterface,
            'jwt' instanceof \ManaPHP\Authentication\Token\Adapter\Jwt,
            'mwt' instanceof \ManaPHP\Authentication\Token\Adapter\Mwt,
            'mailer' instanceof \ManaPHP\MailerInterface,
            'swooleHttpServer' instanceof \ManaPHP\Swoole\Http\ServerInterface,
            'assetBundle' instanceof \ManaPHP\Renderer\AssetBundleInterface,
            'aclbuilder' instanceof \ManaPHP\Authorization\AclBuilderInterface,
        ],
        \di('') => [
            'di' instanceof \ManaPHP\DiInterface,
            'eventsManager' instanceof \ManaPHP\Event\ManagerInterface,
            'alias' instanceof \ManaPHP\AliasInterface,
            'dotenv' instanceof \ManaPHP\DotenvInterface,
            'configure' instanceof \ManaPHP\Configuration\Configure,
            'settings' instanceof \ManaPHP\Configuration\SettingsInterface,
            'errorHandler' instanceof \ManaPHP\ErrorHandlerInterface,
            'router' instanceof \ManaPHP\RouterInterface,
            'dispatcher' instanceof \ManaPHP\Dispatcher,
            'url' instanceof \ManaPHP\UrlInterface,
            'modelsMetadata' instanceof \ManaPHP\Mvc\Model\MetadataInterface,
            'modelsValidator' instanceof \ManaPHP\Model\ValidatorInterface,
            'response' instanceof \ManaPHP\Http\ResponseInterface,
            'cookies' instanceof \ManaPHP\Http\CookiesInterface,
            'request' instanceof \ManaPHP\Http\RequestInterface,
            'filter' instanceof \ManaPHP\Http\FilterInterface,
            'crypt' instanceof \ManaPHP\Security\CryptInterface,
            'flash' instanceof \ManaPHP\View\FlashInterface,
            'flashSession' instanceof \ManaPHP\View\FlashInterface,
            'session' instanceof \ManaPHP\Http\SessionInterface,
            'view' instanceof \ManaPHP\ViewInterface,
            'html' instanceof \ManaPHP\Renderer\HtmlInterface,
            'logger' instanceof \ManaPHP\LoggerInterface,
            'renderer' instanceof \ManaPHP\RendererInterface,
            'cache' instanceof \ManaPHP\CacheInterface,
            'ipcCache' instanceof \ManaPHP\IpcCacheInterface,
            'httpClient' instanceof \ManaPHP\Curl\EasyInterface,
            'captcha' instanceof \ManaPHP\Security\CaptchaInterface,
            'csrfPlugin' instanceof \ManaPHP\Plugins\CsrfPlugin,
            'authorization' instanceof \ManaPHP\AuthorizationInterface,
            'identity' instanceof \ManaPHP\IdentityInterface,
            'paginator' instanceof \ManaPHP\Paginator,
            'filesystem' instanceof \ManaPHP\Filesystem\Adapter\File,
            'random' instanceof \ManaPHP\Security\RandomInterface,
            'messageQueue' instanceof \ManaPHP\Message\QueueInterface,
            'secint' instanceof \ManaPHP\Security\SecintInterface,
            'swordCompiler' instanceof \ManaPHP\Renderer\Engine\Sword\Compiler,
            'tasksManager' instanceof \ManaPHP\Task\ManagerInterface,
            'viewsCache' instanceof \ManaPHP\CacheInterface,
            'htmlPurifier' instanceof \ManaPHP\Security\HtmlPurifierInterface,
            'db' instanceof \ManaPHP\DbInterface,
            'redis' instanceof \ManaPHP\Redis,
            'mongodb' instanceof \ManaPHP\MongodbInterface,
            'translator' instanceof \ManaPHP\I18n\TranslatorInterface,
            'rabbitmq' instanceof \ManaPHP\AmqpInterface,
            'relationsManager' instanceof \ManaPHP\Model\Relation\Manager,
            'di' instanceof \ManaPHP\Di | \ManaPHP\DiInterface,
            'application' instanceof \ManaPHP\ApplicationInterface,
            'jwt' instanceof \ManaPHP\Authentication\Token\Adapter\Jwt,
            'mwt' instanceof \ManaPHP\Authentication\Token\Adapter\Mwt,
            'mailer' instanceof \ManaPHP\MailerInterface,
            'swooleHttpServer' instanceof \ManaPHP\Swoole\Http\ServerInterface,
            'assetBundle' instanceof \ManaPHP\Renderer\AssetBundleInterface,
            'aclbuilder' instanceof \ManaPHP\Authorization\AclBuilderInterface,
        ],
        \ManaPHP\DiInterface::get('') => [
            '' == '@',
        ],
        \ManaPHP\DiInterface::getInstance('') => [
            '' == '@',
        ]
    ];
}

/**
 * @xglobal $view ManaPHP\ViewInterface
 */
/**
 * @var \ManaPHP\ViewInterface         $view
 * @var \ManaPHP\Di                    $di
 * @var \ManaPHP\Http\RequestInterface $request
 * @var \ManaPHP\RendererInterface     $renderer
 */
$view = null;
$di = null;
$request = null;
unset($view, $renderer);

class_exists('\Elasticsearch\Client') || class_alias('\stdClass', '\Elasticsearch\Client');