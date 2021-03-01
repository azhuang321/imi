<?php

declare(strict_types=1);

namespace Imi\Swoole\Process\Annotation;

use Imi\Bean\Annotation\Base;
use Imi\Bean\Annotation\Parser;

/**
 * 进程注解.
 *
 * @Annotation
 * @Target("CLASS")
 * @Parser("Imi\Swoole\Process\Parser\ProcessParser")
 */
#[\Attribute]
class Process extends Base
{
    /**
     * 只传一个参数时的参数名.
     *
     * @var string|null
     */
    protected ?string $defaultFieldName = 'name';

    /**
     * 进程名称.
     *
     * @var string
     */
    public string $name = '';

    /**
     * 重定向子进程的标准输入和输出。启用此选项后，在子进程内输出内容将不是打印屏幕，而是写入到主进程管道。读取键盘输入将变为从管道中读取数据。默认为阻塞读取。
     *
     * @var bool
     */
    public bool $redirectStdinStdout = false;

    /**
     * 管道类型，启用$redirectStdinStdout后，此选项将忽略用户参数，强制为1。如果子进程内没有进程间通信，可以设置为 0.
     *
     * @var int
     */
    public int $pipeType = 2;

    /**
     * 该进程是否只允许存在一个实例.
     *
     * @var bool
     */
    public bool $unique = false;

    /**
     * 自动开启协程.
     *
     * @var bool
     */
    public bool $co = true;

    public function __construct(?array $__data = null, string $name = '', bool $redirectStdinStdout = false, int $pipeType = 2, bool $unique = false, bool $co = true)
    {
        parent::__construct(...\func_get_args());
    }
}
