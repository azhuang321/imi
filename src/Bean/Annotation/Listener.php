<?php

declare(strict_types=1);

namespace Imi\Bean\Annotation;

/**
 * 类事件监听.
 *
 * @Annotation
 * @Target("CLASS")
 * @Parser("Imi\Bean\Parser\ListenerParser")
 */
#[\Attribute]
class Listener extends Base
{
    /**
     * 只传一个参数时的参数名.
     *
     * @var string|null
     */
    protected ?string $defaultFieldName = 'eventName';

    /**
     * 事件名.
     *
     * @var string
     */
    public string $eventName = '';

    /**
     * 优先级，越大越先执行.
     *
     * @var int
     */
    public int $priority = 0;

    public function __construct(?array $__data = null, string $eventName = '', int $priority = 0)
    {
        parent::__construct(...\func_get_args());
    }
}
