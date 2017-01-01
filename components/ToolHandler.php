<?php

namespace app\components;

use Yii;
use \yii\helpers\VarDumper;
use yii\base\Exception;

class ToolHandler {
	
	public static $cache = 'redisCache';
	public static $prefix = 'meishu_app';
	
	const TOKEN_EXPIRE_TIME = 2073600;			//redis缓存，token时效  24*24*3600 (token时效24天)
	
	/**
	 * 设置缓存
	 * @param string $key
	 * @param string $val
	 * @param integer $expre
	 * @return mixed
	 */
	public static function setCache($key,$val,$expre=self::TOKEN_EXPIRE_TIME)
	{
		$cache = self::$cache;
		return Yii::$app->$cache->set($key, $val,$expre);
	}
	/**
	 * 一次设置多个缓存
	 * @param array $arr
	 * @param int $expre
	 */
	public static function setCaches(Array $arr , $expre=self::TOKEN_EXPIRE_TIME){
		$cache = self::$cache;
		return Yii::$app->$cache->mset($arr,$expre);
	}
	 
	/**
	 * 获取缓存
	 * @param string $key
	 * @return mixed <mixed, boolean, string>
	 */
	public static function getCache($key)
	{
		$cache = self::$cache;
		return Yii::$app->$cache->get($key);
	}
	
	/**
	 * 一次获取多个缓存值
	 * @param array $arr 键值对数组<key=>value>
	 */
	public static function getCaches(Array $arr){
		$cache = self::$cache;
		return Yii::$app->$cache->mget($arr);
	}
	
	/**
	 * 删除缓存
	 * @param string $key
	 * @return boolean
	 */
	public static function delCache($key)
	{
		$cache = self::$cache;
		return Yii::$app->$cache->delete($key);
	}
	
	/**
	 * 是否存在缓存
	 * @param string $key
	 * @return boolean
	 */
	public static function existCache($key)
	{
		$cache = self::$cache;
		return Yii::$app->$cache->get($key) === false ? false : true;
	}
	
	/**
	 * 防止重复提交表单
	 * @param string $token
	 * @throws Exception
	 */
	public static function checkRequestFrequency($token, $requestBlanking = 5){
		$key = 'request:'.$token.':'.$_SERVER['REQUEST_URI'];
		$lastTime = self::getCache($key);
		if($lastTime || time() - $lastTime <= $requestBlanking){
			throw new Exception('歇一会儿再点',2002);
		}
		self::setCache($key, time(), $requestBlanking);
	}
	
	/**
	 * 获取连续7天的时间
	 * @param number $tag
	 */
	public static function getWeek($tag=0){
		$time = time() - $tag*604800;//604800一周时间戳
		$date = date('Y-m-d',$time);
		$time =  strtotime($date);
		$data = [];
		for($i = 6 ; $i >= 0 ; $i--){
			$data['time'][$i] = $time - $i * 86400;
			$data['date'][$i] = date('Y-m-d',$data['time'][$i]);
		}
		return $data;
	}
	/**
	 * 获取 离当月第一天往前最近的星期一
	 * 和 离当月最后一天往后最近的星期日 的时间戳
	 * @param string $date 2016-05 格式
	 * @return number[]
	 */
	public static function getMonthTime($date){
		$firstDay = $date . '-01';						//当月第一天
		$LastDay = date('Y-m-d',strtotime('+1 month -1 day',strtotime($firstDay)));			//当月最后一天
		$firstTime = date('Y-m-d',strtotime('last Monday',strtotime($firstDay)));			//离当月第一天往前最近的星期一
		$lastTime = date('Y-m-d',strtotime('next Sunday',strtotime($LastDay)));				//离当月最后一天往后最近的星期日
		return ['firstTime'=>strtotime($firstTime),'lastTime'=>strtotime($lastTime)+3600 * 24];	//返回时间戳
	}
	/**
	 * 获取学生的注册时间到当前时间的月份列表
	 * @param object $student
	 */
	public static function getMonthList($student){
		$registTime = strtotime(date('Y-n',$student->create_time));
		$endTime = time();
		$data = [];
		for ($i = $registTime; $i <= $endTime ; $i = strtotime('+1 month',$i)){
			$data[] = date('Y-n',$i);
		}
		return implode(',', $data);
	}
	
	/**
	 * 格式化输入时间
	 * @param int $time
	 */
	public static function formatTime($time){
		$diff = time() - $time;
		if($diff < 60){
			$count = date('s',$diff);
			$str = "秒钟前";
		}elseif ($diff >= 60 && $diff < 3600){
			$count = date('i',$diff);
			$str = "分钟前";
		}elseif ($diff >= 3600 && $diff < 24 * 3600){
			$count = $diff/3600;
			$str = "小时前";
		}elseif($diff >= 24 * 3600 && $diff < 7 * 24 * 3600){
			$count = $diff / (24 * 3600);
			$str = "天前";
		}else{
			$count = date('Y-m-d',$time);
		}
		$count = isset($str) ? (int)$count : $count;
		return $count . (isset($str) ? $str : '');
	}

	/**
	 * 判断是否是一个合法的json字符串
	 * @param $string
	 * @return bool
	 */
	public static function is_json($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	/**
	 * 判断文件是否存在
	 * @param string $path
	 */
	public static function IsFileExists($path){
		if(UPLOAD_REMOTE){
			$ftp = Yii::$app->ftp;
			return $ftp->IsFileExists($path);
		}else{
			$sourceRoot = \Yii::getAlias ( '@uploads' );
			$fileName = $sourceRoot . "/" . $path;
			return file_exists($fileName);
		}
	}
        
	/**
	 * 重命名文件,也可以移动文件到另一个文件夹里
	 * @param string $old 旧文件名称，如果不在当前目录下，则加上路径
	 * @param string $new 新文件名称，如果不在当前目录下，则加上路径
	 */
	public static function ReName($old,$new) {
		if(UPLOAD_REMOTE){
			$ftp = Yii::$app->ftp;
			return $ftp->rename($old,$new);
		} else {
			$sourceRoot = \Yii::getAlias ( '@uploads' );
			$fileOldName = $sourceRoot . "/" . $old;
			$fileNewName = $sourceRoot . "/" . $new;
			if(file_exists($fileOldName)) {
				return rename($fileOldName, $fileNewName);
			}
		}
	}
        
	/**
	 * 删除文件
	 */
	public static function DelFile($path) {
		if(UPLOAD_REMOTE){
			$ftp = Yii::$app->ftp;
			@$ftp->delete($path);
		} else {
			$sourceRoot = \Yii::getAlias ( '@uploads' );
			$fileName = $sourceRoot . "/" . $path;
			@unlink($fileName);
		}
	}

	/**
	 * @author lhao
	 *         验证手机号
	 * @param string $mobile
	 * @return boolean
	 */
	public static function isMobile($mobile) {
		$pattern = "/(^\d{11}$)|(^852\d{8}$)/";
		if (preg_match ( $pattern, $mobile ))
			return true;
		else
			return false;
	}

	/**
	 * 记录访问日志
	 * @param $fileName
	 * @param string $content
	 * @param array $array
	 */
	public static function addLog($fileName,$content='',$array=array()){
		$root = Yii::getAlias("@backend");
		$num = date("mda");
		$path = $root. DS . 'runtime' . DS . $fileName.'-'.$num;
		$str = PHP_EOL."------------------------------------------" .PHP_EOL.
			", time: " . date("m-d H:i:s") .
			PHP_EOL . $content;
		if(!empty($array)){
			$str .= PHP_EOL;
			$str .= var_export($array, TRUE);
		}
		$str .= PHP_EOL;
		file_put_contents($path, $str, FILE_APPEND);
	}
	
	/**
	 * 更友好的打印函数
	 * @param mixed $var variable to be dumped
	 */
	public static function p($var) {
		VarDumper::dump ( $var, 10 );
	}
	
	/**
	 * 获取客户端真实ip
	 */
	public static function getIP() {
		// 定义一个函数getIP()
		$ip = self::getClientIP ();
		return self::ip2int ( $ip );
	}
	
	/**
	 * 获取客户端IP地址,比$_SERVER['REMOTE_ADDR']要准确获得客户端的IP
	 */
	public static function getClientIP() {
		static $ip = NULL;
		if ($ip !== NULL)
			return $ip;
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$arr = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			$pos = array_search ( 'unknown', $arr );
			if (false !== $pos)
				unset ( $arr [$pos] );
			$ip = trim ( $arr [0] );
		} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$ip = (false !== ip2long ( $ip )) ? $ip : '0.0.0.0';
		return $ip;
	}
	/**
	 * 生成指定长度的随机字符串或md5后的唯一id
	 * @param string $length
	 * @param string $prefix
	 * @return string
	 */
	public static function generateSalt($length = '', $prefix = '')
	{
		$prefix = !$prefix ? mt_rand(0, 1000) : $prefix;
		$string = md5(uniqid($prefix));
		return $length ? substr($string, -$length) : $string;
	}
	
	/**
	 * ip转换为整型
	 * 
	 * @param string $ip
	 * @return int
	 */
	public static function ip2int($ip) {
		return bindec ( decbin ( ip2long ( $ip ) ) );
	}
	
	/**
	 * 整型转换为ip
	 * 
	 * @param int $number
	 * @return string
	 */
	public static function int2ip($number) {
		return long2ip ( $number );
	}
	
	/**
	 * utf8字符串截取
	 * 
	 * @param string $string
	 *        	要截取的字符串
	 * @param int $length
	 *        	截取长度
	 * @param string $etc
	 *        	截取后多余显示字符
	 * @return string
	 */
	public static function truncateUtf8String($string, $length, $etc = '...') {
		$result = '';
		$string = html_entity_decode ( trim ( strip_tags ( $string ) ), ENT_QUOTES, 'UTF-8' );
		$strlen = strlen ( $string );
		for($i = 0; (($i < $strlen) && ($length > 0)); $i ++) {
			if ($number = strpos ( str_pad ( decbin ( ord ( substr ( $string, $i, 1 ) ) ), 8, '0', STR_PAD_LEFT ), '0' )) {
				if ($length < 1.0) {
					break;
				}
				$result .= substr ( $string, $i, $number );
				$length -= 1.0;
				$i += $number - 1;
			} else {
				$result .= substr ( $string, $i, 1 );
				$length -= 0.5;
			}
		}
		$result = htmlspecialchars ( $result, ENT_QUOTES, 'UTF-8' );
		if ($i < $strlen) {
			$result .= $etc;
		}
		return $result;
	}
	
	/**
	 * 创建目录
	 * 可以递归创建，默认是以当前网站根目录下创建
	 * 第二个参数指定，就以第二参数目录下创建
	 * 
	 * @param string $path
	 *        	要创建的目录
	 * @param string $webRoot
	 *        	要创建目录的根目录
	 * @return boolean
	 */
	public static function createDir($path, $webRoot = null) {
		$path = preg_replace ( '/\/+|\\+/', DS, $path );
		
		if (! is_dir ( $webRoot )) {
			$webRoot = \Yii::getAlias ( '@webroot' );
		}
		$dir = $webRoot . DS . $path;
		if (! is_dir ( $dir )) {
			if (! mkdir ( $dir, 0777, true ))
				return false;
			else
				chmod ( $webRoot, 0777 );
		}
		return true;
	}
	
	
	public static function arr_sort($array,$key,$order="asc",$num = 0){//asc是升序 desc是降序
		$arr_nums=$arr=array();
		foreach($array as $k=>$v){
			$arr_nums[$k]=$v[$key];
		}
		if($order=='asc'){
			asort($arr_nums);
		}else{
			arsort($arr_nums);
		}
		if($num>0){
			$i = 1;
		}
		foreach($arr_nums as $k=>$v){
			$arr[$k]=$array[$k];
			if($num>0){
				$i++;
				if($i>$num)
					break;
			}
		}
		return $arr;
	}

	//过滤敏感词,直接替换成*
	public static function Sensitive($string) {
		$model = \common\models\Config::find()->one();
		$value = unserialize($model->value);
		$value = explode(',', $value);
		$badword = array_combine($value,array_fill(0,count($value),'*'));
		$str = strtr($string, $badword);
		return $str;
	}

	//过滤敏感词，有就true，无就false
	public static function checkSensitive($string) {
		$model = \common\models\Config::find()->one();
		$value = unserialize($model->value);
		$value = explode(',', $value);
		//定义子串出现次数
		$num = 0;
		//循环检测
		for($i = 0; $i < count($value); $i ++) {
			//计算子串在字符串中出现的次数
			if (substr_count($string, $value[$i]) > 0) {
				$num ++;
			}
		}

		if($num>0){
			return true;
		} else {
			return false;
		}
	}
    
	/**
	 * 加密
	 * @param $data
	 * @param $key
	 * @return string
	 */
	public static function encrypt($data, $key) {
		$prep_code = serialize($data);
		$block = mcrypt_get_block_size('des', 'ecb');
		if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
			$prep_code .= str_repeat(chr($pad), $pad);
		}
		$encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB);
		return base64_encode($encrypt);
	}

	/**
	 * 解密
	 * @param $str
	 * @param $key
	 * @return mixed
	 */
	public static function decrypt($str, $key) {
		$str = base64_decode($str);
		$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
			$str = substr($str, 0, strlen($str) - $pad);
		}
		return unserialize($str);
	}
} 