<?php

declare(strict_types=1);

namespace Imi\Server\WebSocket\Route\Annotation;

use Imi\Bean\Annotation\Base;
use Imi\Bean\Annotation\Parser;

/**
 * WebSocket 控制器注解.
 *
 * @Annotation
 * @Target("CLASS")
 * @Parser("Imi\Server\WebSocket\Parser\WSControllerParser")
 */
class WSController extends Base
{
    /**
     * 是否为单例控制器.
     *
     * 默认为 null 时取 '@server.服务器名.controller.singleton'
     *
     * @var bool|null
     */
    public ?bool $singleton = null;

    /**
     * http 路由.
     *
     * 如果设置，则只有握手指定 http 路由，才可以触发该 WebSocket 路由
     *
     * @var string|null
     */
    public ?string $route = null;
}