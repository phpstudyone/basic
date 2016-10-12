<?php

namespace app\extensions\ssh2;

use yii\base\Exception;
use yii\base\Object;

/**
 * Class Ssh2
 * @author rihui.zhang <845830229@qq.com>
 * @package app\extensions\ssh2
 */
class Ssh2 extends Object {
	/**
	 * @var string ssh2主机地址，默认为空
	 */
	public $host = null;
	
	/**
	 * @var string ssh2登录端口 默认22
	 */
	public $port = 22;
	
	/**
	 * @var string ssh2登录用户名
	 */
	public $username = null;
	
	/**
	 * @var string ssh2登录密码
	 */
	public $password = null;

    /**
     * @var null 公钥文件
     */
    public $pubkeyfile = null;

    /**
     * @var null 公钥文件
	 */
    public $privkeyfile = null;
	
	/**
	 * @var bool 是否自动连接
	 */
	public $autoConnect = true;
	private $_active = false;
	private $_connection = null;

    /**
     * @var string 远程文件开始地址
     */
	public $dir;

    /**
     * 初始化方法
     */
	public function init() {
		parent::init ();
		if ($this->autoConnect)
			$this->setActive ( true );
	}
	
	/**
	 * 获取当前连接 是否连接上
	 */
	public function getActive() {
		return $this->_active;
	}

    /**
     * 设置ssh2连接 打开或者关闭
     * @param bool $value
     *
     */
	public function setActive($value) {
		if ($value != $this->_active) {
			if ($value)
				$this->connect ();
			else
				$this->close ();
		}
	}

    /**
     * ssh2 连接
     * @throws Exception
     */
	public function connect() {
		if ($this->_connection === null) {
			$this->_connection = ssh2_connect($this->host, $this->port, ['hostkey'=>'ssh-rsa']);
			if (! $this->_connection)
				throw new Exception ( '连接' . $this->host . '失败' );
            if (!ssh2_auth_pubkey_file($this->_connection, $this->username, $this->pubkeyfile, $this->privkeyfile, $this->password)) {
                throw new Exception ( 'ssh2连接' . $this->host . '失败' );
            }
			$this->_active = true;
		}
	}

    /**
     * 关闭连接
     * @return bool
     */
	public function close() {
		if ($this->getActive ()) {
		} else {
		}
		return true;
	}

    /**
     * 从远程服务器获取文件
     * @param string $remote 远程文件
     * @param string $local 本地存储文件
     * @return bool
     * @throws Exception
     */
	public function get($remote,$localRoot , $path ) {
		if ($this->getActive ()) {
		    if(self::localCreateDir($localRoot , $path)){
//                if(ssh2_scp_recv($this->_connection, $this->dir . $remote, $localRoot . $path)){
//                    return true;
//                } else {
//                    throw new Exception ( '获取远程文件'.$remote.'失败' );
//                }
            }else
                throw new Exception ( '目录不存在或创建目录失败' );
		} else {
			throw new Exception ( '连接远程服务器失败' );
		}
	}

    /**
     * 发送本地文件至服务器
     * @param string $local
     * @param string $remote
     * @return bool
     * @throws Exception
     */
	public function put($local, $remote) {
		if ($this->getActive ()) {
            if(ssh2_scp_send ($this->_connection, $local, $remote)){
                return true;
            } else {
                throw new Exception ( '发送本地文件'.$local.'失败' );
			}
		} else {
			throw new Exception ( '连接远程服务器失败' );
		}
	}

    /**
     * 本地创建目录
     * 可以递归创建，默认是以当前网站根目录下创建
     * 第二个参数指定，就以第二参数目录下创建
     * @param string $path 要创建的目录
     * @param string $webRoot 要创建目录的根目录
     * @return boolean
     */
    public static function localCreateDir($localRoot , $path) {
        $filePath = preg_replace ( '/\/+|\\+/', DS, $localRoot . $path );
        $dir = pathinfo($filePath,1);
        if (! is_dir ( $dir )) {
            if (! mkdir ( $dir, 0777, true ))
                return false;
            else
                chmod ( $localRoot, 0777 );
        }
        return true;
    }

	/**
	 * 创建目录
	 * @param string $dir
	 * @return bool
	 */
	public function mkdir($dir) {
		if ($this->getActive ()) {
			if (ftp_mkdir ( $this->_connection, $dir )) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception ( '连接失败' );
		}
	}
	
	/**
	 * 远程创建目录\以及子目录
	 * @param string $path
	 * @return bool
	 */
	public function remoteCreateDir($path) {
		$path = preg_replace ( '/\/+|\\+/', DS, $path );
		$dir = explode ( DS, $path );
		$path = "";
		$ret = true;
		for($i = 0; $i < count ( $dir ); $i ++) {
			$path .= "/" . $dir [$i];
			if (! @ftp_chdir ( $this->_connection, $path )) {
				@ftp_chdir ( $this->_connection, "/" );
				if (! @ftp_mkdir ( $this->_connection, $path )) {
					$ret = false;
					break;
				}
			}
		}
		return $ret;
	}
}