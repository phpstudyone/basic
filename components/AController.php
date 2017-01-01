<?php
namespace api\components;

use yii;
use common\base\BaseController;
use api\components\RSA;
use yii\base\Exception;
use api\models\AppErrorLog;
use common\extensions\ToolFuc;

class AController extends BaseController {
	public $layout = false;
	public $enableCsrfValidation = false;
	
	const TYPE_WORK = 1; 				// 作品
	const TYPE_KNOWLEDGE = 2;			// 知识点
	const TYPE_SUBJECT = 3;				//课堂
	
	const TYPE_KNOWLEDGE_ARTICLE = 4;			//文章类型知识点
	const TYPE_KNOWLEDGE_IMAGE = 5;				//图片类型知识点
	const TYPE_KNOWLEDGE_VIDEO = 6;				//视频类型的知识点
	const TYPE_NEWS_NOTICE = 7;					//新鲜事
	const TYPE_TARGET_SCHOOL = 8;				//目标院校
	
	/**
	 * 参数列表
	 *
	 * @var unknown
	 */
	public $params = [ ];
	
	/**
	 * 必填参数
	 *
	 * @var unknown
	 */
	public $requiredFields = [ ];
	
	/**
	 * 需要加密的参数
	 *
	 * @var unknown
	 */
	public $decryptFields = [ ];
	
	/**
	 * 做一些所有接口都做的事
	 *
	 * {@inheritDoc}
	 *
	 * @see \yii\web\Controller::beforeAction()
	 */
	public function beforeAction($action) {
		try {
			if($post = Yii::$app->request->isPost)
			{
			    ToolFuc::xhprof_inic();
			    return parent::beforeAction ( $action );
			}else
			    throw new Exception('请POST提交',0101);
		}catch (Exception $e){
			$this->_error ( $e->getCode(), $e->getMessage (),[], isset($post) ? $post : '');
		}
	}
	

	
	/**
	 *
	 * @author lhao
	 *         验证手机号
	 * @param str $mobile        	
	 * @return boolean
	 */
	public function isMobile($mobile) {
		$pattern = "/(^\d{11}$)|(^852\d{8}$)/";
		if (preg_match ( $pattern, $mobile ))
			return true;
		else
			return false;
	}
	
	/**
	 * 返回接口url
	 * controllerID/actionID 形式
	 */
	protected function getConAction() {
		return Yii::$app->controller->id . '/' . $this->action->id;
	}
	
	/**
	 * 运行成功返回json
	 *
	 * @param string|array $data        	
	 */
	protected function _success($data = '') {
		$array = array ();
		if ($data != false && is_array ( $data )) {
			$array ['resultDesc'] = "";
			$array ['resultData'] = $data;
		} elseif (is_string ( $data ) || is_numeric ( $data )) {
			$array ['resultDesc'] = $data;
		} else {
			$array ['resultDesc'] = "";
		}
		$array ['resultCode'] = "0001";
		header ( "Content-type:text/html;charset=utf-8" );
		$return_json = json_encode ( array_merge( $array,['actionType' => $this->getConAction ()]));
		echo $return_json;
		$this->_outputLog($return_json);
		Yii::$app->end ();
	}
	
	
	/*******定义错误状态码*******/
	protected static $errorCode = [
	        '0000' => '系统错误'
	        ];
	protected static $errorMsg = [
	        '2001'=>'登录失效'
	];
	
	/**
	 * 运行错误返回json
	 *
	 * @param string $resultCode
	 *        	错误码
	 * @param string $resultDesc
	 *        	错误补充说明
	 * @param array|string $data
	 *        	错误补充数据
	 */
	protected function _error($resultCode, $resultDesc = '', $data = '' ,$dePost = '')
	{
		$msg = $resultDesc;
        if($resultCode == 0)
            $resultCode == '0000';
        
        if($resultDesc == '' && isset(self::$errorMsg[$resultCode]))
        {
            $resultDesc = self::$errorMsg[$resultCode];
        }elseif($resultCode<1000){
            $resultDesc = isset(self::$errorCode[$resultCode])?self::$errorCode[$resultCode]:'系统错误';
        }
        
		$res = array (
				'resultCode' => $resultCode,
				'resultDesc' => $resultDesc,
				'resultData' => $data
		);
		$this->_outputLog($msg,$dePost);
		header ( "Content-type:text/html;charset=utf-8" );
		$return_json = json_encode ( array_merge($res,['actionType' => $this->getConAction ()]));
		echo $return_json ;
        Yii::$app->end ();
	}
    
    /**
     *  错误日志
     */
	protected function _outputLog($msg,$dePost = '')
	{
	    if(YII_DEBUG){
    	    $url = Yii::$app->urlManager->createAbsoluteUrl($this->getConAction ());
    	    $controller = Yii::$app->controller->id;
    	    $action = $this->action->id;
    	    $run_id = ToolFuc::get_xhprof_run_id();
    	    AppErrorLog::insertLog($url, $controller, $action,var_export($_REQUEST,true),var_export($dePost,true),$msg,'',$run_id);
	    }
	}
	
	/**
	 * 判断md5加密值有没有改变
	 */
	protected static function CheckMd5Sign($params, $post, $md5key, $key = '') {
		$signString = "";
		if (is_array ( $post )) {
			if (! (isset ( $post [$md5key] )))
				return false;
			sort ( $params );
			foreach ( $params as $val ) {
				if (isset ( $post [$val] ) && $val != $md5key) {
					$signString .= $post [$val];
				}
			}
			if ($key != '')
				$signString .= $key;
			$signMd5 = strtoupper ( md5 ( $signString ) );
			if ($signMd5 != $post [$md5key])
				return false;
		} else {
			$signMd5 = md5 ( $post );
			if ($signMd5 != $post)
				return false;
		}
		return true;
	}
	/**
	 * 解密方法（校验数据合法）
	 *
	 * @param array $request
	 *        	需要传递的参数
	 * @param array $requiredFields
	 *        	必传参数
	 * @param array $decryptFields
	 *        	需要加密的参数
	 * @param string $privateKey
	 *        	私钥
	 */
	protected function decrypt($request, $requiredFields = [], $decryptFields = [], $privateKey = '') {
		$result = array ();
		$rsa = new RSA ();
		if ($privateKey != '')
			$rsa->privateKey = $privateKey;
		foreach ( $this->params as $field ) {
			if (isset ( $request [$field] )) {
				// 验证必填字段
				if ($requiredFields && in_array ( $field, $requiredFields )) {
					if (trim ( $request [$field] ) === '')
						throw new Exception ( $field . '提交数据不能为空');
				}
				// 解密字段值
				if ($decryptFields && in_array ( $field, $decryptFields )) {
					$result [$field] = $rsa->decrypt ( $request [$field] );
					if ($result [$field] === false)
						throw new Exception ( '数据解密失败');
					if ($requiredFields && in_array ( $field, $requiredFields )) {
						if ($result [$field] === '' || $result [$field] === false)
							throw new Exception ( $field . '是必填字段，解密后为空');
					}
				} else
					$result [$field] = $request [$field];
			} elseif (in_array ( $field, $requiredFields )) {
				throw new Exception ( $field . '是必填字段！','1005');
			} else
				continue;
		}
		return $this->magicQuotes ( $result );
	}
	
	/**
	 * 加密方法，用于测试
	 *
	 * @param unknown $data        	
	 */
	protected static function encrypt($data) {
		$public = Yii::getAlias ( '@keyPath' ) . DS . 'rsa_public_key.pem';
		$fp = fopen ( $public, "r" );
		$publicKey = fread ( $fp, 8192 );
		$res = openssl_get_publickey ( $publicKey );
		openssl_public_encrypt ( $data, $encrypted, $res );
		$encrypted = bin2hex ( $encrypted ); // 转换成十六进制
		return $encrypted;
	}
	
	/**
	 * 创建rsa公钥和密钥
	 *
	 * @param String $code
	 *        	用于配置digest_alg参数。
	 * @return array 包含公钥和密钥的数组
	 */
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
	
	/**
	 * 接口参数的验证
	 *
	 * @param array $requireParams
	 *        	必填的参数
	 * @return mixed
	 */
	public function getValidateData($requireParams) {
		$data = $this->post ( 'data' );
		$sign = $this->post ( 'sign' );
		if (substr ( md5 ( $data . MEISHU_SIGN ), 5, 20 ) != $sign) {
			$response = array (
					'status' => 401,
					'msg' => 'Validation errors',
					'data' => '验证不通过' 
			);
			exit ( json_decode ( $response ) );
		}
		$postData = json_decode ( $data, true );
		$arr = array_keys ( $postData );
		foreach ( $requireParams as $param ) {
			if (! in_array ( $param, $arr )) {
				$response = array (
						'status' => 402,
						'msg' => 'params errors',
						'data' => '提交的参数不全' 
				);
				exit ( json_decode ( $response ) );
			}
		}
		return $postData;
	}
	
	/**
	 * callBack 接口data参数的验证
	 *
	 * @param array $requireParams
	 *        	必填的参数
	 * @return mixed
	 */
	public function getCallBackData($requireParams) {
		$data = $this->get ( 'data', false );
		
		if (! $data) {
			$response = array (
					'status' => 401,
					'msg' => 'no data',
					'data' => '没有参数' 
			);
			exit ( json_encode ( $response ) );
		}
		
		$data = json_decode ( urldecode ( $data ), true );
		$sign = isset ( $data ['sign'] ) ? $data ['sign'] : '';
		unset ( $data ['sign'] );
		
		if (! $sign || ($sign && substr ( md5 ( json_encode ( $data ) . GAI_SIGN ), 5, 20 ) != $sign)) {
			$response = array (
					'status' => 401,
					'msg' => 'Validation errors',
					'data' => '验证不通过' 
			);
			exit ( json_encode ( $response ) );
		}
		
		$arr = array_keys ( $data );
		foreach ( $requireParams as $param ) {
			if (! in_array ( $param, $arr )) {
				$response = array (
						'status' => 402,
						'msg' => 'params errors',
						'data' => '提交的参数不全' 
				);
				exit ( json_encode ( $response ) );
			}
		}
		return $data;
	}
}
