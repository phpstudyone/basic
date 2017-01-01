<?php

namespace app\components;

use yii;
define('MEISHU_SIGN', 'db7a4fa02786f50cf53891ea8166b24415434021');
/**
 * 密钥类
 * 加密，解密
 */
class RSA {

	public $privateKey; // 私钥
	
	/**
	 * 初始化私钥
	 */
	function __construct() {
		$publicKey = Yii::getAlias ( '@keyPath' ) . DS . 'rsa_private_key.pem';
		$fp = fopen ( $publicKey, "r" );
		$this->privateKey = fread ( $fp, 8192 );
		fclose ( $fp );
	}
	
	/**
	 * 解密方法
	 * 
	 * @param string $value        	
	 * @return string|null
	 * @author rihui.zhang <rihui_best@163.com>
	 */
	public function decrypt($value) {
		$len = strlen ( $value );
		$string = pack ( "H" . $len, $value );
		$res = openssl_get_privatekey ( $this->privateKey );
		openssl_private_decrypt ( $string, $result, $res );
		return $result;
	}
	
	/**
	 * 加密
	 * 
	 * @param
	 *        	$data
	 * @return string
	 */
	public function encrypt($data) {
		$res = openssl_get_publickey ( $this->publicKey );
		openssl_public_encrypt ( $data, $encrypted, $res );
		$encrypted = bin2hex ( $encrypted ); // 转换成十六进制
		return $encrypted;
	}
	
	/**
	 * 加密静态方法
	 * 
	 * @author rihui.zhang <rihui_best@163.com>
	 */
	public static function quickEncrypt($data) {
		$rsa = new RSA ();
		return $rsa->encrypt ( $data );
	}
	
	/**
	 * 解密静态方法
	 * 
	 * @author rihui.zhang <rihui_best@163.com>
	 */
	public static function quickDecrypt($data) {
		$rsa = new RSA ();
		return $rsa->decrypt ( $data );
	}
	
	/**
	 * 将数组转换成 签名明文串
	 * 对数组里的每一个值从 a 到 z 的顺序排序，若遇到相同首字母，则看第二个字母，以此类推。
	 * 排序完成之后，再把所有数组值以“&”字符连接起来
	 * 
	 * @param array $array        	
	 * @return string
	 */
	public static function plain($array) {
		ksort ( $array );
		$plain = '';
		foreach ( $array as $k => $v ) {
			$plain .= $k . '=' . $v . '&';
		}
		return substr ( $plain, 0, - 1 );
	}
	/**
	 * 数据签名
	 * 
	 * @param string $plain
	 *        	签名明文串
	 * @param string $priv_key_file
	 *        	商户租钥证书 路径
	 * @return bool|string
	 */
	public static function sign($plain, $priv_key_file) {
		try {
			if (! File_exists ( $priv_key_file )) {
				exit ( "The key is not found, please check the configuration!" );
			}
			$fp = fopen ( $priv_key_file, "rb" );
			$priv_key = fread ( $fp, 8192 );
			@fclose ( $fp );
			$pkeyid = openssl_get_privatekey ( $priv_key );
			if (! is_resource ( $pkeyid )) {
				return FALSE;
			}
			// compute signature
			openssl_sign ( $plain, $signature, $pkeyid );
			// free the key from memory
			openssl_free_key ( $pkeyid );
			return base64_encode ( $signature );
		} catch ( Exception $e ) {
			exit ( "Signature attestation failure" . $e->getMessage () );
		}
	}
	
	/**
	 * 签名数据验签
	 * 
	 * @param string $plain
	 *        	验签明文
	 * @param string $signature
	 *        	验签密文
	 * @param string $cert_file
	 *        	公钥文件路径
	 * @return bool
	 */
	public static function verify($plain, $signature, $cert_file) {
		if (! file_exists ( $cert_file )) {
			die ( "未找到密钥,请检查配置!" );
		}
		$signature = base64_decode ( $signature );
		$fp = fopen ( $cert_file, "r" );
		$cert = fread ( $fp, 8192 );
		fclose ( $fp );
		$pubkeyid = openssl_get_publickey ( $cert );
		if (! is_resource ( $pubkeyid )) {
			return FALSE;
		}
		$ok = openssl_verify ( $plain, $signature, $pubkeyid );
		@openssl_free_key ( $pubkeyid );
		if ($ok == 1) { // 1
			return TRUE;
		} elseif ($ok == 0) { // 2
			return FALSE;
		} else { // 3
			return FALSE;
		}
	}

    public static function createRsaKey($code = MEISHU_SIGN) {
        $config = array (
            'digest_alg' => $code,
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        );

        // 创建公钥密钥中间变量
        $tmp = openssl_pkey_new ( $config );

        // 如果中间变量生成成功
        if ($tmp) {
            // 根据中间变量生成私钥
            openssl_pkey_export ( $tmp, $privateKey );

            // 根据中间变量生成公钥
            $publicKey = openssl_pkey_get_details ( $tmp );
            $publicKey = $publicKey ['key'];
        } else {
            var_dump ( openssl_error_string () );
            Yii::$app->end ();
        }

        // 去掉开头和结尾,根据实际情况截取
        $publicKeyApk = substr ( $publicKey, 27, - 26 ); // -----BEGIN PUBLIC KEY----- 和-----END PUBLIC KEY-----
        return array (
            'privateKey' => $privateKey,
            'publicKey' => $publicKey,
            'publicKeyApk' => $publicKeyApk
        );
    }
}
