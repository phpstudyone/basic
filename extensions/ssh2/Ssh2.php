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
	 * @var string the host for establishing FTP connection. Defaults to null.
	 */
	public $host = null;
	
	/**
	 * @var string the port for establishing FTP connection. Defaults to 21.
	 */
	public $port = 22;
	
	/**
	 * @var string the username for establishing FTP connection. Defaults to null.
	 */
	public $username = null;
	
	/**
	 * @var string the password for establishing FTP connection. Defaults to null.
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
	 * @var boolean
	 */
	public $ssl = false;
	
	/**
	 * @var string the timeout for establishing FTP connection. Defaults to 90.
	 */
	public $timeout = 90;
	
	/**
	 * @var boolean whether the ftp connection should be automatically established
	 *      the component is being initialized. Defaults to false. Note, this property is only
	 *      effective when the EFtpComponent object is used as an application component.
	 */
	public $autoConnect = true;
	private $_active = false;
	private $_errors = null;
	private $_connection = null;
	public $dir;
	
	/**
	 * Initializes the component.
	 * This method is required by {@link IApplicationComponent} and is invoked by application
	 * when the EFtpComponent is used as an application component.
	 * If you override this method, make sure to call the parent implementation
	 * so that the component can be marked as initialized.
	 */
	public function init() {
		parent::init ();
		if ($this->autoConnect)
			$this->setActive ( true );
	}
	
	/**
	 * 获取当前连接 是否连接上
	 * @return boolean whether the FTP connection is established
	 */
	public function getActive() {
		return $this->_active;
	}
	
	/**
	 * 设置当前连接 打开或关闭 ftp
	 * Open or close the FTP connection.
	 * @param boolean whether to open or close FTP connection
	 * @throws CException if connection fails
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
	 * Connect to FTP if it is currently not
	 * @throws CException if connection fails
	 */
	public function connect() {
		if ($this->_connection === null) {
			$this->_connection = ssh2_connect($this->host, $this->port, array('hostkey'=>'ssh-rsa'));
			if (! $this->_connection)
				throw new Exception ( '连接' . $this->host . '失败' );
            if (!ssh2_auth_pubkey_file($this->_connection, $this->username, $this->pubkeyfile, $this->privkeyfile, $this->password)) {
                throw new Exception ( 'ssh2连接' . $this->host . '失败' );
            }
			$this->_active = true;
		}
	}
	
	/**
	 * Closes the current FTP connection.
	 *
	 * @return boolean
	 */
	public function close() {
		if ($this->getActive ()) {
			// Close the connection
			if (ftp_close ( $this->_connection )) {
				return true;
			} else {
				return false;
			}
			
			$this->_active = false;
			$this->_connection = null;
			$this->_errors = null;
		} else {
			throw new Exception ( 'FtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * Passed an array of constants => values they will be set as FTP options.
	 *
	 * @param array $config        	
	 * @return object (chainable)
	 */
	public function setOptions($config) {
		if ($this->getActive ()) {
			if (! is_array ( $config ))
				throw new Exception ( 'EFtpComponent Error: The config parameter must be passed an array!' );
				
				// Loop through configuration array
			foreach ( $config as $key => $value ) {
				// Set the options and test to see if they did so successfully - throw an exception if it failed
				if (! ftp_set_option ( $this->_connection, $key, $value ))
					throw new Exception ( 'EFtpComponent Error: The system failed to set the FTP option: "' . $key . '" with the value: "' . $value . '"' );
			}
			
			return $this;
		} else {
			throw new Exception ( 'EFtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * 执行ftp命令
	 * Execute a remote command on the FTP server.
	 *
	 * @see http://us2.php.net/manual/en/function.ftp-exec.php
	 * @param
	 *        	string remote command
	 * @return boolean
	 */
	public function execute($command) {
		if ($this->getActive ()) {
			// Execute command
			if (ftp_exec ( $this->_connection, $command )) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception ( 'EFtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * 获取远程文件到本地
	 * Get executes a get command on the remote FTP server.
	 *
	 * @param
	 *        	string local file
	 * @param
	 *        	string remote file
	 * @param
	 *        	const mode
	 * @return boolean
	 */
	public function get($local, $remote, $mode = FTP_ASCII) {
		if ($this->getActive ()) {
			// Get the requested file
			if (ftp_get ( $this->_connection, $local, $remote, $mode )) {
				// If successful, return the path to the downloaded file...
				return $remote;
			} else {
				return false;
			}
		} else {
			throw new Exception ( 'EFtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * 发送本地文件到ftp服务器
	 * Put executes a put command on the remote FTP server.
	 * 
	 * @param string $remote        	
	 * @param string $local        	
	 * @param int $mode        	
	 * @return bool
	 * @throws CDbException
	 */
	public function put($remote, $local, $mode = FTP_BINARY) {
		if ($this->getActive ()) {
			// Upload the local file to the remote location specified
			ftp_chdir ( $this->_connection, '/' );
			if (ftp_put ( $this->_connection, $remote, $local, $mode )) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception ( 'EFtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * 给远程文件重命名
	 * Rename executes a rename command on the remote FTP server.
	 *
	 * @param
	 *        	string old filename
	 * @param
	 *        	string new filename
	 * @return boolean
	 */
	public function rename($old, $new) {
		if ($this->getActive ()) {
			// Rename the file
			ftp_chdir ( $this->_connection, '/' );
			if (ftp_rename ( $this->_connection, $old, $new )) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception ( 'EFtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * Rmdir executes an rmdir (remove directory) command on the remote FTP server.
	 *
	 * @param
	 *        	string remote directory
	 * @return boolean
	 */
	public function rmdir($dir) {
		if ($this->getActive ()) {
			// Remove the directory
			if (ftp_rmdir ( $this->_connection, $dir )) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception ( 'EFtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * Mkdir executes an mkdir (create directory) command on the remote FTP server.
	 * 
	 * @param
	 *        	$dir
	 * @return bool
	 * @throws CDbException
	 */
	public function mkdir($dir) {
		if ($this->getActive ()) {
			// create directory
			if (ftp_mkdir ( $this->_connection, $dir )) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception ( 'EFtpComponent is inactive and cannot perform any FTP operations.' );
		}
	}
	
	/**
	 * 创建目录\以及子目录
	 * 
	 * @param
	 *        	$path
	 * @return bool
	 */
	public function createDir($path) {
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