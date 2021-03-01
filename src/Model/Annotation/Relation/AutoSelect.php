<?php

declare(strict_types=1);

namespace Imi\Model\Annotation\Relation;

use Imi\Bean\Annotation\Base;
use Imi\Bean\Annotation\Parser;

/**
 * 自动查询.
 *
 * @Annotation
 * @Target("PROPERTY")
 * @Parser("Imi\Bean\Parser\NullParser")
 */
#[\Attribute]
class AutoSelect extends Base
{
    /**
     * 只传一个参数时的参数名.
     *
     * @var string|null
     */
    protected ?string $defaultFieldName = 'status';

    /**
     * 是否开启.
     *
     * @var bool
     */
    public bool $status = true;

    /**
     * 是否总是显示该属性
     * 如果为false，在为null时序列化为数组或json不显示该属性.
     *
     * @var bool
     */
    public bool $alwaysShow = true;

    public function __construct(?array $__data = null, bool $status = true, bool $alwaysShow = true)
    {
        parent::__construct(...\func_get_args());
    }
}
