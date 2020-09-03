<?php
namespace Imi\Cli\Tools\Imi;

use Imi\App;
use Imi\Util\Text;
use Imi\Cli\ArgType;
use Imi\Bean\Annotation;
use Imi\Util\Imi as ImiUtil;
use Imi\Cli\Annotation\Option;
use Imi\Cli\Annotation\Command;
use Imi\Pool\Annotation\PoolClean;
use Imi\Cli\Annotation\CommandAction;
use Imi\Cli\Contract\BaseCommand;

/**
 * @Command("imi")
 */
class Imi extends BaseCommand
{
    /**
     * 构建框架预加载缓存
     * 
     * @CommandAction("buildImiRuntime")
     * @Option(name="file", type=ArgType::STRING, default=null, comments="可以指定生成到目标文件")
     * 
     * @return void
     */
    public function buildImiRuntime(?string $file): void
    {
        if(null === $file)
        {
            $file = \Imi\Util\Imi::getRuntimePath('imi-runtime.cache');
        }
        ImiUtil::buildRuntime($file);
        $this->output->writeln('<info>Build imi runtime complete</info>');
    }

    /**
     * 清除框架预加载缓存
     * 
     * @CommandAction("clearImiRuntime")
     * 
     * @return void
     */
    public function clearImiRuntime(): void
    {
        $file = \Imi\Util\Imi::getRuntimePath('imi-runtime.cache');
        if(is_file($file))
        {
            unlink($file);
            $this->output->writeln('<info>Clear imi runtime complete</info>');
        }
        else
        {
            $this->output->writeln('<error>Imi runtime does not exists</error>');
        }
    }

    /**
     * 构建项目预加载缓存
     * 
     * @PoolClean
     * 
     * @CommandAction(name="buildRuntime", co=false)
     * 
     * @Option(name="format", type=ArgType::STRING, default="", comments="返回数据格式，可选：json或其他。json格式框架启动、热重启构建缓存需要。")
     * @Option(name="changedFilesFile", type=ArgType::STRING, default=null, comments="保存改变的文件列表的文件，一行一个")
     * @Option(name="confirm", type=ArgType::BOOL, default=false, comments="是否等待输入y后再构建")
     * @Option(name="sock", type=ArgType::STRING, default=null, comments="如果传了 sock 则走 Unix Socket 通讯")
     * 
     * @return void
     */
    public function buildRuntime(string $format, ?string $changedFilesFile, bool $confirm, ?string $sock): void
    {
        $socket = null;
        $success = false;
        ob_start();
        register_shutdown_function(function() use($format, &$socket, &$success){
            $result = ob_get_clean();
            if($success)
            {
                $result = 'Build app runtime complete' . PHP_EOL;
            }
            if($result)
            {
                if('json' === $format)
                {
                    $this->output->write(json_encode($result));
                }
                else
                {
                    $this->output->write($result);
                }
            }
            if($socket)
            {
                $data = [
                    'action'    =>  'buildRuntimeResult',
                    'result'    =>  $result,
                ];
                $content = serialize($data);
                $content = pack('N', strlen($content)) . $content;
                fwrite($socket, $content);
                fclose($socket);
            }
        });

        if($sock)
        {
            $socket = stream_socket_client('unix://' . $sock, $errno, $errstr, 10);
            if(false === $socket)
            {
                exit;
            }
            stream_set_timeout($socket, 60);
            do {
                $meta = fread($socket, 4);
                if('' === $meta)
                {
                    if(feof($socket))
                    {
                        exit;
                    }
                    continue;
                }
                if(false === $meta)
                {
                    exit;
                }
                $length = unpack('N', $meta)[1];
                $data = fread($socket, $length);
                if(false === $data || !isset($data[$length - 1]))
                {
                    exit;
                }
                $result = unserialize($data);
                if('buildRuntime' === $result['action'])
                {
                    break;
                }
            } while(true);
        }
        else if($confirm)
        {
            $input = fread(STDIN, 1);
            if('y' !== $input)
            {
                exit;
            }
        }

        if(!Text::isEmpty($changedFilesFile) && App::loadRuntimeInfo(ImiUtil::getRuntimePath('runtime.cache')))
        {
            $files = explode("\n", file_get_contents($changedFilesFile));
            ImiUtil::incrUpdateRuntime($files);
        }
        else
        {
            // 加载服务器注解
            Annotation::getInstance()->init(\Imi\Main\Helper::getAppMains());
        }
        ImiUtil::buildRuntime();
        $success = true;
    }

    /**
     * 清除项目预加载缓存
     * 
     * @CommandAction("clearRuntime")
     * 
     * @return void
     */
    public function clearRuntime(): void
    {
        $file = \Imi\Util\Imi::getRuntimePath('runtime.cache');
        if(is_file($file))
        {
            unlink($file);
            $this->output->writeln('<info>Clear app runtime complete</info>');
        }
        else
        {
            $this->output->writeln('<error>App runtime does not exists</error>');
        }
    }

}