<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

defined('THINK_PATH') or exit();
/**
 * Memcache缓存驱动
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Cache
 * @author    liu21st <liu21st@gmail.com>
 */
class CacheMemcache extends Cache {

	/**
	 * 记录是否连接
	 */
	public $connected = false;
	
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct($options=array()) {
		if ( !extension_loaded('memcache') ) {
            //throw_exception(L('_NOT_SUPPERT_').':memcache');
			return false;
        }

		$this->connected = false;

		if(!_DC_MMC_DISABLE){
			$this->handler = new Memcache();
			$mmcCfgArr = C('MMC_CFG');

			if($mmcCfgArr){
				foreach($mmcCfgArr as $k => $val){
					if($this->handler->addServer($val ['MMC_HOST'], $val ['MMC_PORT'], true, 1, $val ['MMC_TIMEOUT'])){
						$this->connected = true;
					}
					$this->handler->setServerParams($val ['MMC_HOST'], $val ['MMC_PORT'], 1, 15, true, '_callback_memcache_failure');
				}
			}
		}
    }

	/**
     * 是否连接
     * @return boolen
     */
    public function isConnected() {
        return $this->connected;
    }

	/**
	 * 在mmc服务器上增加缓存数据
	 *
	 * @param string $p_key		索引
	 * @param mixed	 $p_val		需要缓存的数据
	 * @param int	 $p_expires	缓存失效时间
	 * @return 如果成功则返回 TRUE，失败则返回 FALSE
	 */
	public function add($p_key, $p_val, $p_expires = '1', $flag = false)
	{
		if(_DC_MMC_DISABLE || !$this->connected){
			return false;
		}
		return $this->handler->add($p_key, $p_val, $flag, $p_expires);
	}

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($p_name) {
        N('cache_read',1);
        if(_DC_MMC_DISABLE || !$this->connected){
			return false;
		}
        return $this->handler->get(G_MMC_PRE . $p_name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $p_name 缓存变量名
     * @param mixed $p_val  存储数据
     * @param integer $p_expire  有效时间（秒）
     * @return boolen
     */
    public function set($p_name, $p_val, $p_expire = null) {
        N('cache_write',1);
        if(_DC_MMC_DISABLE || !$this->connected){
			return false;
		}
		return $this->handler->set(G_MMC_PRE . $p_name, $p_val, _DC_MMC_COMPRESS, $p_expire);
        return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $p_name 缓存变量名
     * @return boolen
     */
    public function rm($p_name) {
		if(_DC_MMC_DISABLE || !$this->connected){
			return false;
		}

		$key = substr($p_name, 0);

        return $this->handler->delete($key);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
		if(_DC_MMC_DISABLE || !$this->connected){
			return false;
		}
        return $this->handler->flush();
    }

	/**
	 * 获取当前MEMCACHE状态
	 *
	 * @return Array
	 */
	public function mGetStats()
	{
//		if(_DC_MMC_DISABLE || !$this->connected){
//			return "=== Can not connect to memcache server! ====<br>\n";
//		}
		return $this->handler->getServerStatus();
	}
}

/**
 * 当memcache出错时的回调函数
 */
function _callback_memcache_failure($host, $port) {
	$cache = Cache :: getInstance('Memcache');
	$cache->connected = false;
	echo "Failed to connect to Memcache Server '$host:$port'<br/>";
//	exit("Failed to connect to the cache server!<br>\n");
}