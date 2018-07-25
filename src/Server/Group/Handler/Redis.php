<?php
namespace Imi\Server\Group\Handler;

use Imi\Util\ArrayUtil;
use Imi\Pool\PoolManager;
use Imi\Bean\Annotation\Bean;
use Swoole\Coroutine\Redis as CoRedis;
use Imi\RequestContext;

/**
 * @Bean("GroupRedis")
 */
class Redis implements IGroupHandler
{
	/**
	 * Redis 连接池名称
	 *
	 * @var string
	 */
	protected $redisPool;

	/**
	 * redis中第几个库
	 *
	 * @var integer
	 */
	protected $redisDb = 0;

	/**
	 * 心跳时间，单位：秒
	 *
	 * @var int
	 */
	protected $heartbeatTimespan = 5;

	/**
	 * 心跳数据过期时间，单位：秒
	 *
	 * @var int
	 */
	protected $heartbeatTtl = 8;

	/**
	 * 该服务的分组键
	 * 
	 * @var string
	 */
	protected $key = 'IMI.GROUP.KEY';

	/**
	 * 心跳Timer的ID
	 *
	 * @var int
	 */
	private $timerID;

	/**
	 * 组配置
	 *
	 * @var array
	 */
	private $groups = [];

	/**
	 * 主进程 ID
	 * @var int
	 */
	private $masterPID;

	public function __init()
	{
		if(null === $this->redisPool)
		{
			return;
		}
		$this->useRedis(function($resource, $redis){
			// 判断master进程pid
			$this->masterPID = RequestContext::getServer()->getSwooleServer()->master_pid;
			$hasPing = $this->hasPing($redis);
			$storeMasterPID = $redis->get($this->key);
			if(null === $storeMasterPID)
			{
				// 没有存储master进程pid
				$this->initRedis($redis, $storeMasterPID);
			}
			else if($this->masterPID != $storeMasterPID)
			{
				if($hasPing)
				{
					// 与master进程ID不等
					throw new \RuntimeException('Server Group Redis repeat');
				}
				else
				{
					$this->initRedis($redis, $storeMasterPID);
				}
			}
			$this->startPing($redis);
		});
	}

	/**
	 * 初始化redis数据
	 *
	 * @param mixed $redis
	 * @param int $storeMasterPID
	 * @return void
	 */
	private function initRedis($redis, $storeMasterPID = null)
	{
		if(null !== $storeMasterPID && $redis->del($this->key))
		{
			return;
		}
		if($redis->setnx($this->key, $this->masterPID))
		{
			// 初始化所有分组列表
			$keys = $redis->keys($this->key . '.*');
			foreach($keys as $key)
			{
				try{
					if($redis->scard($key) > 0)
					{
						$redis->del($key);
					}
				}
				catch(\Throwable $ex)
				{

				}
			}
		}
	}

	/**
	 * 开始ping
	 *
	 * @param mixed $redis
	 * @return void
	 */
	private function startPing($redis)
	{
		if($this->ping($redis))
		{
			// 心跳定时器
			$this->timerID = \swoole_timer_tick($this->heartbeatTimespan * 1000, [$this, 'pingTimer']);
		}
	}

	/**
	 * ping定时器执行操作
	 *
	 * @return void
	 */
	public function pingTimer()
	{
		$this->useRedis(function($resource, $redis){
			$this->ping($redis);
		});
	}

	/**
	 * 获取redis中存储ping的key
	 *
	 * @return void
	 */
	private function getPingKey()
	{
		return $this->key . '-PING';
	}

	/**
	 * ping操作
	 *
	 * @param mixed $redis
	 * @return boolean
	 */
	private function ping($redis)
	{
		$key = $this->getPingKey();
		$redis->multi();
		$redis->set($key, '');
		$redis->expire($key, $this->heartbeatTtl);
		$result = $redis->exec();
		if(!$result)
		{
			return false;
		}
		foreach($result as $value)
		{
			if(!$value)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * 是否有ping
	 *
	 * @param mixed $redis
	 * @return boolean
	 */
	private function hasPing($redis)
	{
		$key = $this->getPingKey();
		return 1 == $redis->exists($key);
	}

	public function __destruct()
	{
		if(null !== $this->timerID)
		{
			\swoole_timer_clear($this->timerID);
		}
	}

	/**
	 * 组是否存在
	 *
	 * @param string $groupName
	 * @return boolean
	 */
	public function hasGroup(string $groupName)
	{
		return true;
	}

	/**
	 * 创建组，返回组对象
	 *
	 * @param string $groupName
	 * @param integer $maxClients
	 * @return void
	 */
	public function createGroup(string $groupName, int $maxClients = -1)
	{
		if(!isset($this->groups[$groupName]))
		{
			$this->groups[$groupName] = [
				'maxClient'		=>	$maxClients,
			];
		}
	}

	/**
	 * 关闭组
	 *
	 * @param string $groupName
	 * @return void
	 */
	public function closeGroup(string $groupName)
	{
		$this->useRedis(function($resource, $redis) use($groupName){
			$key = $this->getGroupNameKey($groupName);
			try{
				if($redis->scard($key) > 0)
				{
					$redis->del($key);
				}
			}
			catch(\Throwable $ex)
			{

			}
		});
	}

	/**
	 * 加入组，组不存在则自动创建
	 *
	 * @param string $groupName
	 * @param integer $fd
	 * @return void
	 */
	public function joinGroup(string $groupName, int $fd): bool
	{
		return $this->useRedis(function($resource, $redis) use($groupName, $fd){
			$key = $this->getGroupNameKey($groupName);
			return $redis->sadd($key, $fd) > 0;
		});
	}

	/**
	 * 离开组，组不存在则自动创建
	 *
	 * @param string $groupName
	 * @param integer $fd
	 * @return void
	 */
	public function leaveGroup(string $groupName, int $fd): bool
	{
		return $this->useRedis(function($resource, $redis) use($groupName, $fd){
			$key = $this->getGroupNameKey($groupName);
			return $redis->srem($key, $fd) > 0;
		});
	}

	/**
	 * 连接是否存在于组里
	 *
	 * @param string $groupName
	 * @param integer $fd
	 * @return boolean
	 */
	public function isInGroup(string $groupName, int $fd): bool
	{
		return $this->useRedis(function($resource, $redis) use($groupName, $fd){
			$key = $this->getGroupNameKey($groupName);
			$redis->sIsMember($key, $fd);
		});
	}

	/**
	 * 获取所有fd
	 *
	 * @param string $groupName
	 * @return int[]
	 */
	public function getFds(string $groupName): array
	{
		return $this->useRedis(function($resource, $redis) use($groupName){
			$key = $this->getGroupNameKey($groupName);
			if($this->groups[$groupName]['maxClient'] > 0)
			{
				return $redis->sRandMember($key, $this->groups[$groupName]['maxClient']);
			}
			else
			{
				return $redis->sMembers($key);
			}
		});
	}

	/**
	 * 获取组名处理后的键名
	 *
	 * @param string $groupName
	 * @return string
	 */
	public function getGroupNameKey(string $groupName): string
	{
		return $this->key . '.' . $groupName;
	}

	/**
	 * 获取组中的连接总数
	 * @return integer
	 */
	public function count(string $groupName): int
	{
		return $this->useRedis(function($resource, $redis) use($groupName){
			$key = $this->getGroupNameKey($groupName);
			return $redis->scard($key);
		});
	}

	/**
	 * 使用redis
	 *
	 * @param callable $callback
	 * @return void
	 */
	private function useRedis($callback)
	{
		return PoolManager::use($this->redisPool, function($resource, $redis) use($callback){
			$redis->select($this->redisDb);
			return $callback($resource, $redis);
		});
	}
}