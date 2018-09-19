<?php
namespace Imi\Db\Annotation;

use Imi\Bean\Annotation\Base;
use Imi\Bean\Annotation\Parser;

/**
 * 列字段注解
 * @Annotation
 * @Target("METHOD")
 * @Parser("Imi\Db\Parser\DbParser")
 */
class Transaction extends Base
{
    /**
     * 数据库连接池名，为null或默认都为默认连接池
     *
     * @var string|null
     */
    public $dbPoolName = null;
}