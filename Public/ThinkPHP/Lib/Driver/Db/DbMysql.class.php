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
 * Mysql数据库驱动类
 * @category   Think
 * @package  Think
 * @subpackage  Driver.Db
 * @author    liu21st <liu21st@gmail.com>
 */
class DbMysql extends Db{
	//使用cache
	private $_cache				= null;
	//表缓存配置
	private $_mmcTbCfg			= null;
	//表结果集缓存
	private $_mmcRes			= null;

    /**
     * 架构函数 读取数据库配置信息
     * @access public
     * @param array $config 数据库配置数组
     */
    public function __construct($config=''){
        if ( !extension_loaded('mysql') ) {
            throw_exception(L('_NOT_SUPPERT_').':mysql');
        }
        if(!empty($config)) {
            $this->config   =   $config;
            if(empty($this->config['params'])) {
                $this->config['params'] =   '';
            }
        }

		$this->_cache = Cache :: getInstance('Memcache');
		$this->_mmcTbCfg = C('MMC_TB_CFG');
		$this->_mmcRes   = C('MMC_RES');
    }

    /**
     * 连接数据库方法
     * @access public
     * @throws ThinkExecption
     */
    public function connect($config='',$linkNum=0,$force=false) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            // 处理不带端口号的socket连接情况
            $host = $config['hostname'].($config['hostport']?":{$config['hostport']}":'');
            // 是否长连接
            $pconnect   = !empty($config['params']['persist'])? $config['params']['persist']:$this->pconnect;
            if($pconnect) {
                $this->linkID[$linkNum] = mysql_pconnect( $host, $config['username'], $config['password'],131072);
            }else{
                $this->linkID[$linkNum] = mysql_connect( $host, $config['username'], $config['password'],true,131072);
            }
            if ( !$this->linkID[$linkNum] || (!empty($config['database']) && !mysql_select_db($config['database'], $this->linkID[$linkNum])) ) {
                throw_exception(mysql_error());
            }
            $dbVersion = mysql_get_server_info($this->linkID[$linkNum]);
            //使用UTF8存取数据库
            mysql_query("SET NAMES '".C('DB_CHARSET')."'", $this->linkID[$linkNum]);
            //设置 sql_model
            if($dbVersion >'5.0.1'){
                mysql_query("SET sql_mode=''",$this->linkID[$linkNum]);
            }
            // 标记连接成功
            $this->connected    =   true;
            // 注销数据库连接配置信息
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }

    /**
     * 释放查询结果
     * @access public
     */
    public function free() {
        mysql_free_result($this->queryID);
        $this->queryID = null;
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function query($p_sql, $p_cache = true) {
        if(0===stripos($p_sql, 'call')){ // 存储过程查询支持
            $this->close();
        }
        $this->initConnect(false);
		$oldSql = $this->queryStr;
		$this->queryStr = trim($p_sql);
		switch($this->queryStr {0}){
			case 's' :
			case 'S' :
				$start = stripos($this->queryStr, 'from') + 5;
				$end = stripos($this->queryStr, 'where', $start);
				if($end === false){
					$end = stripos($this->queryStr, ' ', $start);
				}
				if($end){
					$table = substr($this->queryStr, $start, $end - $start);
				}else{
					$table = substr($this->queryStr, $start);
				}
				$table = trim($table);
				$table = trim ( $table, '`' );

				$method = strtoupper(substr($this->queryStr, 0, 4)) == 'SHOW' ? 'DbMysql::show' : 'DbMysql::select';

				if(_DC_MMC_DISABLE || !$this->_cache->isConnected()){
					$this->innerQuery($method, $table);
				}else{
					$this->doBeforeSelect($table, $p_cache, $method);
				}

				//如果是“SHOW COLUMNS FROM”，则不记录本条SQL
				if(strtoupper(substr($this->queryStr, 0, 4)) == 'SHOW'){
					$this->queryStr  = $oldSql;
					unset($oldSql);
				}
//				echo 'old:'.$oldSql.'<br>';
//				echo 'new:'.$this->queryStr."<br><br>";
//				var_dump($this->queryID);
				if($this->queryID){
					return $this->getAll($this->queryID);
				}else{
					return false;
				}
			case 'i' :
			case 'I' :
			case 'r' :
			case 'R' :
				$start = stripos($this->queryStr, 'into') + 4;
				$end = stripos($this->queryStr, '(', $start);
				$table = substr($this->queryStr, $start, $end - $start);
				$table = trim($table);
				$table = trim ( $table, '`' );
				$this->doBeforeInsert($table, 'DbMysql::insert');
				break;
			case 'u' :
			case 'U' :
				$start = stripos($this->queryStr, 'update') + 7;
				$end = stripos($this->queryStr, ' ', $start);
				$table = substr($this->queryStr, $start, $end - $start);
				$table = trim($table);
				$table = trim ( $table, '`' );
				$this->doBeforeUpdate($table, 'DbMysql::update');
				break;
			case 'd' :
			case 'D' :
				$start = stripos($this->queryStr, 'from') + 5;
				$end = stripos($this->queryStr, ' ', $start);
				$table = substr($this->queryStr, $start, $end - $start);
				$table = trim($table);
				$table = trim ( $table, '`' );
				$this->doBeforeDelete($table, 'DbMysql::delete');
				break;
			default :
				$this->queryStr = str_replace('`', '', $this->queryStr);
				$this->innerQuery('DbMysql::query', $table);
				break;
		}
    }

    /**
     * 执行语句
     * @access public
     * @param string $str  sql指令
     * @return integer|false
     */
    public function execute($str) {
		$this->initConnect(true);
        //释放前次的查询结果
        if ( $this->queryID ) {    $this->free();    }
        N('db_write',1);
        // 记录开始执行时间
        G('queryStartTime');
		$result = $this->query($str);
        $this->debug();
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = mysql_affected_rows($this->_linkID);
            $this->lastInsID = mysql_insert_id($this->_linkID);
            return $this->numRows;
        }
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans() {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        //数据rollback 支持
        if ($this->transTimes == 0) {
            mysql_query('START TRANSACTION', $this->_linkID);
        }
        $this->transTimes++;
        return ;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return boolen
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = mysql_query('COMMIT', $this->_linkID);
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 事务回滚
     * @access public
     * @return boolen
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = mysql_query('ROLLBACK', $this->_linkID);
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 获得所有的查询数据
     * @access private
     * @return array
     */
    public function getAll($p_res = false) {
		if(!$p_res){
			$p_res = $this->queryID;
		}
        $result = array();
		while($row = $this->fetch_assoc($p_res)){
			$result[] = $row;
		}
        return $result;
    }

	/**
	 * 获得记录集内的单条记录，获取完指针下移一位
	 * 这里以字段名作为下标
	 *
	 * @param <rs> $p_res	记录集，若不指定，则使用本次查询的记录集
	 * @return <Array>		结果
	 */
	public function fetch_assoc($p_res = false)
	{
		if(!$p_res){
			$p_res = $this->queryID;
		}
		if(is_resource($p_res)){
			return mysql_fetch_assoc($p_res);
		}
		if(!empty($this->_mmcRes[$p_res])){
			if($this->_mmcRes[$p_res]['count'] <= $this->_mmcRes[$p_res]['idx']){
				return false;
			}
			return $this->_mmcRes[$p_res]['value'][$this->_mmcRes[$p_res]['idx']++];
		}
		return false;
	}

	/**
	 * 获得记录数
	 *
	 * @param <rs> $p_res	记录集，若不指定，则使用本次查询的记录集
	 * @return <Int>		记录数
	 */
	public function num_rows($p_res = false)
	{
		if(!$p_res)
			$p_res = $this->_selectRes;

		if(is_resource($p_res))
			return mysql_num_rows($p_res);

		global $g_mmc_res;
		return $g_mmc_res[$p_res]['count'];
	}

    /**
     * 取得数据表的字段信息
     * @access public
     * @return array
     */
    public function getFields($tableName) {
        $result =   $this->query('SHOW COLUMNS FROM '.$this->parseKey($tableName));
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[$val['Field']] = array(
                    'name'    => $val['Field'],
                    'type'    => $val['Type'],
                    'notnull' => (bool) ($val['Null'] === ''), // not null is empty, null is yes
                    'default' => $val['Default'],
                    'primary' => (strtolower($val['Key']) == 'pri'),
                    'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
                );
            }
        }
        return $info;
    }

    /**
     * 取得数据库的表信息
     * @access public
     * @return array
     */
    public function getTables($dbName='') {
        if(!empty($dbName)) {
           $sql    = 'SHOW TABLES FROM '.$dbName;
        }else{
           $sql    = 'SHOW TABLES ';
        }
        $result =   $this->query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     * 替换记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @return false | integer
     */
    public function replace($data,$options=array()) {
        foreach ($data as $key=>$val){
            $value   =  $this->parseValue($val);
            if(is_scalar($value)) { // 过滤非标量数据
                $values[]   =  $value;
                $fields[]     =  $this->parseKey($key);
            }
        }
        $sql   =  'REPLACE INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        return $this->execute($sql);
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $datas 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insertAll($datas,$options=array(),$replace=false) {
        if(!is_array($datas[0])) return false;
        $fields = array_keys($datas[0]);
        array_walk($fields, array($this, 'parseKey'));
        $values  =  array();
        foreach ($datas as $data){
            $value   =  array();
            foreach ($data as $key=>$val){
                $val   =  $this->parseValue($val);
                if(is_scalar($val)) { // 过滤非标量数据
                    $value[]   =  $val;
                }
            }
            $values[]    = '('.implode(',', $value).')';
        }
        $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES '.implode(',',$values);
        return $this->execute($sql);
    }

    /**
     * 关闭数据库
     * @access public
     * @return void
     */
    public function close() {
        if ($this->_linkID){
            mysql_close($this->_linkID);
        }
        $this->_linkID = null;
    }

    /**
     * 数据库错误信息
     * 并显示当前的SQL语句
     * @access public
     * @return string
     */
    public function error() {
        $this->error = mysql_error($this->_linkID);
        if('' != $this->queryStr){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        trace($this->error,'','ERR');
        return $this->error;
    }

	/**
     * 获取最近一次查询的sql语句
	 * 重写
     * @param string $model  模型名
     * @access public
     * @return string
     */
    public function getLastSql() {
        return $this->queryStr;
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {
        if($this->_linkID) {
            return mysql_real_escape_string($str,$this->_linkID);
        }else{
            return mysql_escape_string($str);
        }
    }

    /**
     * 字段和表名处理添加`
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        $key   =  trim($key);
        if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
           $key = '`'.$key.'`';
        }
        return $key;
    }


	/*
	 * 获取缓存的组KEY设置
	 * @param string $p_key 缓存的key名
	 */
	public function getMemKey($p_key)
	{
		if(_DC_MMC_DISABLE || !$this->_cache->isConnected()){
			return false;
		}

		$val = $this->_cache->mGet($p_key);
		if($val == false){
			$time = time();
			$this->_cache->mSet($p_key, $time);
			return $time;
		}else{
			return $val;
		}
	}

	/*
	 * 更新缓存的组KEY设置
	 * @param string $p_key 缓存的key名
	 */
	public function setMemKey($p_key)
	{
		if(_DC_MMC_DISABLE || !$this->_cache->isConnected()){
			return false;
		}

		$time = time();
		$this->_cache->mSet($p_key, $time);
		return $time;
	}

	/**
	 * 清除指定表缓存
	 *
	 * @param <String>	$p_table	表名
	 */
	public function flushOneTable($p_table)
	{
		$returnStr = "";
		if(_DC_MMC_DISABLE || !$this->_cache->isConnected())
			return $returnStr;

		if(!isset($this->_mmcTbCfg[$p_table])){
			return $returnStr;
		}

		$returnStr .= "开始刷新表缓存 ( 表名： $p_table ) ........................ <br/>";

		$conf = $this->_mmcTbCfg[$p_table];
		
		$oldVersion = $this->getTableVersion($p_table);
		$newVersion = $this->getTableVersion($p_table, 1);

        $returnStr .= " <span style='color:#CC33FF'> ( 版本由 {$oldVersion} 更新为 {$newVersion} )</span><br><br> ";
		
		return $returnStr;
	}

	/**
	 * 获取低粒度缓存的条件唯一值
	 *
	 * @param string $p_table 表名
	 */
	private function getUnkey($p_table)
	{
		$start = stripos($this->queryStr, 'where');
		$p_search = substr($this->queryStr, $start);
		$isInsert = strtolower(substr($this->queryStr, 0, 6)) == 'insert' ? true : false;

		//单独处理插入时提取关键字的操作
		if($isInsert == true){
			$queryStr = $this->queryStr;
			$astart = stripos($queryStr, '(');
			$aend = stripos($queryStr, ')');
			$aArr = explode(',', substr($queryStr, $astart + 1, $aend - $astart - 1));
			
			$queryStr = substr($queryStr, $aend + 3);
			$bstart = stripos($queryStr, '(');
			$bend = stripos($queryStr, ')');
			$bArr = explode(',', substr($queryStr, $bstart + 1, $bend - $bstart - 1));

			$insertArr = array();
			foreach($aArr as $key=>$val){
				$insertArr[trim(trim($val), '`')] = trim(trim($bArr[$key]), '\'');
			}
		}
		
		$unkey = '';
		if(isset($this->_mmcTbCfg[$p_table]['unkey'])){
			foreach($this->_mmcTbCfg[$p_table]['unkey'] as $key => &$val){
				if($isInsert == true && !empty($insertArr) && isset($insertArr[$val])){
					$unkey = $val .'='.strtolower(str_replace(array(' ', '"', "'"), '', $insertArr[$val]));
					break;
				}
				if(preg_match("/\s{$val}(\s=\s|=)(.*?)(\s|$)/", $p_search, $unkeyTmp)){
					$unkey = strtolower(str_replace(array(' ', '"', "'"), '', $unkeyTmp[0]));
					break;
				}
			}
		}
		return $unkey;
	}

	/**
	 * 获取低粒度缓存的条件唯一值
	 *
	 * @param string $p_table	表名
	 */
	private function getSearch()
	{
		$start = stripos($this->queryStr, 'where');
		$p_search = substr($this->queryStr, $start);
		return $p_search;
	}

	/**
	 * 执行SQL，不可直接使用，是select|update等的附属
	 * _DO_DEBUG 开启时输出语句调试信息
	 * _DO_LOG	 开启时记录SQL语句
	 *
	 * @param string $p_method	这语句从哪执行[DataOper::innerSelect|DataOper::update|DataOper::delete|DataOper::insert]
	 * @param string $p_table	表名
	 */
	private function innerQuery($p_method, $p_table = '')
	{
		//连接数据库
        $this->initConnect(false);
        if ( !$this->_linkID ){
			return false;
		}

        //释放前次的查询结果
        if ( $this->queryID ){
			$this->free();
		}
		$selectMTArr = array('DbMysql:select', 'DbMysql:query');
		if(in_array($p_method, $selectMTArr)){
			N('db_query', 1);
		}else{
			N('db_write',1);
		}

        // 记录开始执行时间
        G('queryStartTime');
        $this->queryID = mysql_query($this->queryStr, $this->_linkID);
        $this->debug();

		if(_DO_DEBUG){
			if(_DO_DEBUG_ECHO_SQL)
				echo "<b> Run method: {$p_method}() <br>\n SQL: {$this->queryStr} </b><br>\n";
			if(_DO_DEBUG_ECHO_MYSQL_ERROR && mysql_error())
				echo "<b style='color:red'>GET MYSQL ERROR: " . mysql_error() . "<br>\n{$this->queryStr}</b><br>\n";
		}

		if(_DO_LOG && $p_table){
			if(!is_dir(_DO_LOG_PATH))
				mkdir(_DO_LOG_PATH);
			//echo $p_method;
			//echo "<br/>";
			if(_DO_LOG_SELECT && $p_method == 'DbMysql::show'){
				$fp = fopen(_DO_LOG_PATH . $p_table . '_show.sql', 'a');
			}else if(_DO_LOG_SELECT && $p_method == 'DbMysql::select'){
				$fp = fopen(_DO_LOG_PATH . $p_table . '_select.sql', 'a');
			}else if(_DO_LOG_UPDATE && $p_method == 'DbMysql::update'){
				$fp = fopen(_DO_LOG_PATH . $p_table . '_update.sql', 'a');
			}else if(_DO_LOG_DELETE && $p_method == 'DbMysql::delete'){
				$fp = fopen(_DO_LOG_PATH . $p_table . '_delete.sql', 'a');
			}else if(_DO_LOG_INSERT && $p_method == 'DbMysql::insert'){
				$fp = fopen(_DO_LOG_PATH . $p_table . '_insert.sql', 'a');
			}else{
				$fp = fopen(_DO_LOG_PATH . $p_table . '_other.sql', 'a');
			}
			flock($fp, LOCK_EX);
			fwrite($fp, $this->queryStr . ";\r\n");
			fclose($fp);
		}

		if($p_method == 'DbMysql::show'){
			return true;
		}
        else if ( false === $this->queryID ) {
            $this->error();
            return false;
        } else {
			if(_DO_LOG_SELECT && $p_method == 'DbMysql::select'){
				$this->numRows = mysql_num_rows($this->queryID);
			}
            return true;
        }
	}

	/**
	 * 判断当前查询是否已经被缓存， 如果是则返回缓存， 否则根据参数删除或缓存查询结果
	 *
	 * @param string 	$p_table		// 查询的表名
	 * @param bool 		$p_cache		// 是否缓存结果
	 * @param string 	$p_method		// 调用该方法的函数, 用于记录日志
	 * @return mysql resource / custom resource
	 */
	private function doBeforeSelect($p_table, &$p_cache, $p_method)
	{
		//单独处理SHOW开头
		if(strtoupper(substr($this->queryStr, 0, 4)) == 'SHOW'){
			$p_table = 'bluefoot_table_show';
		}
		// 判断是否对当前查询进行缓存
		if(!_DC_MMC_DISABLE && isset($this->_mmcTbCfg[$p_table]) && $this->_mmcTbCfg[$p_table]['mmc'] && $p_cache){

			//生成SQL关键名
			if($tableShowKey != ''){
				$key = $tableShowKey;
				echo $key.'_'.$this->queryStr.'<br/>';
			}else{
				$unkey = $this->getUnkey($p_table);
				if($unkey){
					$key = $this->getTableUnkeyVersion($p_table, $unkey);
				}else{
					$key = $this->getTableNormalVersion($p_table);
				}
//				echo $unkey.'_'.$key.'_'.$this->queryStr.'<br/>';
				$key = md5($key . $this->queryStr);
			}

			//试提取数据
			$data = $this->_cache->get($key);

			// 如果该查询已经缓存， 那么直接返回自定义全局资源
			if($data !== false){
				$idx = ++$this->_mmcRes[0];
				$this->_mmcRes[$idx] = array('idx' => 0, 'count' => count($data), 'value' => $data);
				$this->writeCacheLog(_DC_LOG_USE_CACHE, $p_table, $unkey);
				return $this->queryID = $idx;
			}

			// 执行查询
			$this->innerQuery($p_method, $p_table);
			$num_rows = @mysql_num_rows($this->queryID);

			// 保存数据
			$cache_data = $this->getAll($this->queryID);

			// 写日志
			$this->writeCacheLog(_DC_LOG_CRT_CACHE, $p_table, $unkey);

			// 缓存查询结果
			$expired = isset($this->_mmcTbCfg[$p_table]['expired']) ? $this->_mmcTbCfg[$p_table]['expired'] : _DC_MMC_DEFAULT_EXPIRED;
			$this->_cache->set($key, $cache_data, $expired);

			$idx = ++$this->_mmcRes[0];
			$this->_mmcRes[$idx] = array('idx' => 0, 'count' => $num_rows, 'value' => $cache_data);

			return $this->queryID = $idx;
		} else{
			$this->innerQuery($p_method, $p_table);
			// 如果阻止缓存的粒度只是当前语句， 那么必须将之前的SQL语句删除
			if(!_DC_MMC_DISABLE && isset($this->_mmcTbCfg[$p_table]) && $this->_mmcTbCfg[$p_table]['mmc']){
				//生成SQL关键名
				$unkey = $this->getUnkey($p_table);
				if($unkey){
					$key = $this->getTableUnkeyVersion($p_table, $unkey);
				}else{
					$key = $this->getTableNormalVersion($p_table);
				}
				$key = md5($key . $this->queryStr);
				//删除
				$this->_cache->rm($key);
			}
			return $this->queryID;
		}
	}

	/**
	 * 判断当前插入语句对之前缓存的数据产生的影响，
	 * 如果当前的插入操作导致某个缓存失效， 那么就从mmc中删除这个缓存， 并更新配置文件
	 *
	 * @param string 	$p_table	// 要更新的表名
	 * @param string 	$p_method	// 调用该方法的函数, 用于记录日志
	 */
	private function doBeforeInsert($p_table, $p_method)
	{
		//删除表普通缓存和低粒度缓存
		$this->deleteTableCache($p_table);

		//执行语句
		return $this->innerQuery($p_method, $p_table);
	}

	/**
	 * 判断当前更新语句对之前缓存的数据产生的影响，
	 * 如果当前的更新操作导致某个缓存失效， 那么就从mmc中删除这个缓存， 并更新配置文件
	 *
	 * @param string 	$p_table	// 要更新的表名
	 * @param string 	$p_method	// 调用该方法的函数, 用于记录日志
	 */
	private function doBeforeUpdate($p_table, $p_method)
	{
		//删除表普通缓存和低粒度缓存
		$this->deleteTableCache($p_table);

		//执行语句
		return $this->innerQuery($p_method, $p_table);
	}

	/**
	 * 判断当前删除语句对之前缓存的数据产生的影响，
	 * 如果当前的删除操作导致某个缓存失效， 那么就从mmc中删除这个缓存， 并更新配置文件
	 *
	 * @param string 	$p_table	// 要更新的表名
	 * @param string 	$p_method	// 调用该方法的函数, 用于记录日志
	 */
	private function doBeforeDelete($p_table, $p_method)
	{
		//删除表普通缓存和低粒度缓存
		$this->deleteTableCache($p_table);

		//执行语句
		return $this->innerQuery($p_method, $p_table);
	}

	/**
	 * 为数组的key添加表名
	 * 将array(field => value) 改为 array( table_field => value)
	 * @param string &$p_table
	 * @param array  &$p_data
	 * @return array
	 */
	private function addTbName2Key($p_table, &$p_data)
	{
		if(!is_array($p_data))
			return $p_data;
		$arr = array();
		foreach($p_data as $k => $v)
			$arr["{$p_table}_{$k}"] = $v;
		return $arr;
	}

	/**
	 * 为数组的value添加表名
	 * 将array(key => value) 改为 array( key => table_value)
	 * @param string &$p_table
	 * @param array	 &$p_data
	 * @return array
	 */
	private function addTbName2Value($p_table, &$p_data)
	{
		$arr = array();
		foreach($p_data as $k => $v)
			$arr[$k] = "{$p_table}_{$v}";
		return $arr;
	}

	/**
	 * 提取总版本版号标识
	 *
	 * @param int	$p_auto		是否强行更换版本号[默认0，若为1则更新]
	 */
	private function getAllVersion($p_auto=0)
	{
		$value	= $this->_cache->get(md5(_DO_MMC_VERSION_NAME));
		if(!$value || $p_auto == 1){
			$value = $this->createAndSetVersion(md5(_DO_MMC_VERSION_NAME), $value);
		}
		return $value;
	}

	/**
	 * 提取表版号标识
	 * 
	 * @param int	$p_auto		是否强行更换版本号[默认0，若为1则更新]
	 */
	private function getTableVersion($p_table, $p_auto=0)
	{
		$key = md5($this->getAllVersion() .'_'. _DO_MMC_TABLE_NAME .'_'. $p_table);
		$value	= $this->_cache->get($key);
		if(!$value || $p_auto == 1){
			$value = $this->createAndSetVersion($key, $value);
		}
		return $value;
	}

	/**
	 * 提取表低粒度版号标识
	 *
	 * @param int	$p_auto		是否强行更换版本号[默认0，若为1则更新]
	 */
	private function getTableUnkeyVersion($p_table, $p_unkeyString, $p_auto=0)
	{
		$key = md5($this->getTableVersion($p_table) .'_'. _DO_MMC_UNKEY_NAME .'_'. $p_unkeyString);
		$value	= $this->_cache->get($key);
		if(!$value || $p_auto == 1){
			$value = $this->createAndSetVersion($key, $value);
		}
		return $value;
	}

	/**
	 * 提取表低粒度版号标识
	 *
	 * @param int	$p_auto		是否强行更换版本号[默认0，若为1则更新]
	 */
	private function getTableNormalVersion($p_table, $p_auto=0)
	{
		$key = md5($this->getTableVersion($p_table) .'_'. _DO_MMC_NORMAL_NAME);
		$value	= $this->_cache->get($key);
		if(!$value || $p_auto == 1){
			$value = $this->createAndSetVersion($key, $value);
		}
		return $value;
	}


	/**
	 * 创建版本号，并写入
	 *
	 * @param string $p_key			键名
	 * @param string $p_oldVersion	旧版本号
	 */
	private function createAndSetVersion($p_key, $p_oldVersion)
	{
		$value = rand(100, 40000);
		while($value == $p_oldVersion){
			$value = rand(100, 40000);
		}
		$expired = isset($this->_mmcTbCfg[$p_table]['expired']) ? $this->_mmcTbCfg[$p_table]['expired'] : _DC_MMC_DEFAULT_EXPIRED;
		$this->_cache->set($p_key, $value, $expired);
		return $value;
	}

	/**
	 * 根据SQL删除表缓存
	 *
	 * @param string $p_table 表名
	 * @return boolean
	 */
	private function deleteTableCache($p_table)
	{
		if(!_DC_MMC_DISABLE && isset($this->_mmcTbCfg[$p_table]) && $this->_mmcTbCfg[$p_table]['mmc']){

			// 删除普通缓存
			$this->getTableNormalVersion($p_table, 1);
			$this->writeCacheLog(_DC_LOG_DEL_CACHE, $p_table);

			//删除低粒度缓存
			if(isset($this->_mmcTbCfg[$p_table]['unkey'])){
				$unkey = $this->getUnkey($p_table);
				$search = $this->getSearch();
				// 删除相关低粒度缓存
				if($unkey){
					// 获取和当前唯一键值关联的其他字段的值
					$assoc = $this->_cache->get(_DO_MMC_UNKEY_CONF_NAME . $p_table . $unkey);
					if(!$assoc || count($this->_mmcTbCfg[$p_table]['unkey']) != count($assoc) || !is_array($assoc)){
						// 为了不修改类指针和其他属性， 这里直接使用数据库函数
						$res = mysql_query("SELECT * FROM `{$p_table}` {$search}");
						if(mysql_num_rows($res)){
							$row = mysql_fetch_assoc($res);
							$assoc = array();
							foreach($this->_mmcTbCfg[$p_table]['unkey'] as $k => $v){
								$assoc[] = strtolower($v . '=' . $row[$v]);
							}
							$this->_cache->set(_DO_MMC_UNKEY_CONF_NAME . $p_table . $unkey, $assoc);
						}else{
							$assoc = array($unkey);
						}
					}
					// 删除所有相关的低粒度缓存
					foreach($assoc as $k => $v){
						$this->getTableUnkeyVersion($p_table, $v, 1);
						$this->writeCacheLog(_DC_LOG_LOW_DEL_CACHE, $p_table, $v);
					}
				}else{
					// 删除所有低粒度缓存
					$this->getTableVersion($p_table, 1);
					$this->writeCacheLog(_DC_LOG_LOW_DEL_CACHE, $p_table);
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * write memcache log
	 * @param int $p_type 对缓存的更新类型(_DC_LOG_*_CACHE)
	 * @param string $p_table 需要做记录的表明
	 * @param string $p_unkey 低粒度缓存的索引字段
	 */
	private function writeCacheLog($p_type, $p_table = '', $p_unkey = '')
	{
		if(!_DC_LOG)
			return;
		if(!isset($this->_mmcTbCfg[$p_table]) || ( isset($this->_mmcTbCfg[$p_table]['log']) && !$this->_mmcTbCfg[$p_table]['log'] ))
			return;
		$tmp = array(_DC_LOG_PATH, date('Y') . '/', date('n') . '/', date('j') . '/', date('G') . '/');
		$path = '';
		foreach($tmp as $k => $v){
			$path .= $v;
			if(!is_dir($path))
				mkdir($path);
		}
		$head = is_file($path . $p_table . '.log.html');
		$fp = fopen($path . $p_table . '.log.html', 'a');
		flock($fp, LOCK_EX);

		if(!$head)
			fwrite($fp, '<body bgcolor="#000000" text="#999999">');

		fwrite($fp, '(' . date('m-d  H : i : s') . ") ");

		if($p_unkey)
			$content = "{$this->queryStr} ( {$p_unkey} ) ( " . strlen($p_unkey) . ' )';
		else
			$content = $this->queryStr;

		switch($p_type){
			case _DC_LOG_USE_CACHE :
				if($p_unkey){
					fwrite($fp, '<div style="color:#339900">使用低粒度缓存： ' . $content . '</div><br/>'."\n");
				}else{
					fwrite($fp, '<div style="color:#339900">使用缓存： ' . $content . '</div><br/>'."\n");
				}
				break;
			case _DC_LOG_UPD_CACHE :
				if($p_unkey){
					fwrite($fp, '<div style="color:#6633CC">更新低粒度缓存： ' . $content . '</div><br/>'."\n");
				}else{
					fwrite($fp, '<div style="color:#6633CC">更新缓存： ' . $content . '</div><br/>'."\n");
				}
				break;
			case _DC_LOG_CRT_CACHE :
				if($p_unkey){
					fwrite($fp, '<div style="color:#FF6600">新建低粒度缓存： ' . $content . '</div><br/>'."\n");
				}else{
					fwrite($fp, '<div style="color:#FF6600">新建缓存： ' . $content . '</div><br/>'."\n");
				}
				break;
			case _DC_LOG_DEL_CACHE :
				fwrite($fp, '<div style="color:#FF0000">删除缓存： ' . $content . ' ( 表  ' . $p_table . ' 的普通缓存失效 ) </div><br/>'."\n");
				break;
			case _DC_LOG_LOW_DEL_CACHE :
				if($p_unkey){
					fwrite($fp, '<div style="color:#FF0000">删除低粒度缓存： ' . "( {$p_unkey} ) ( " . strlen($p_unkey) . ' )' . '失效</div><br/>'."\n");
				}else{
					fwrite($fp, '<div style="color:#FF0000">批量更新导致表： ' . $p_table . ' 的所有低粒度缓存失效('. $p_unkey .')</div><br/>'."\n");
				}
				break;
			default :
				break;
		}

		fclose($fp);
	}
}