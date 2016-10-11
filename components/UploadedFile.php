<?php

namespace api\components;

use Yii;
use yii\base\Exception;

/**
 * 文件上传
 * 
 * @example // 上传
 *          $model = UploadedFile::uploadFile($model, 'thumbnail', 'logo'); // 处理上传的文件
 *          UploadedFile::saveFile('thumbnail', $model->thumbnail); // 上传
 *         
 *          // 更新
 *          $oldFile = $model->thumbnail;
 *          $model = UploadedFile::uploadFile($model, 'thumbnail', 'logo'); // 处理上传的文件
 *          UploadedFile::saveFile('thumbnail', $model->thumbnail, $oldFile, true);
 *         
 */
class UploadedFile extends \yii\web\UploadedFile {

	// 上传文件属性
	private static $uploadFiles = array ();
	// 保存路径属性
	private static $savePath;
	private static $alias;

	/**
	 * 文件上传
	 *
	 * @param object $model
	 *        	AR对象
	 * @param string $fileField
	 *        	AR对象中的文件字段
	 * @param string $saveDir
	 *        	保存目录可递归形式 具体查看 Tool::createDir() 方法
	 * @param string $savePath
	 *        	保存路径默认或路径不存在则使用 Yii::getAlias('@att')
	 * @param string $fileName
	 *        	文件名称，默认系统生成唯一的一串字符
	 * @param string $name
	 *        	the name of the file input field. 当文件上传于与 AR对象中的文件字段 不一样时候使用
	 * $param string $field
	 *        	model中的字段，用来保存新文件名
	 * @return string
	 */
	public static function uploadFile($model, $fileField, $saveDir = 'images/work',  $fileName = null, $name = null ,$field = null,$isApp=false) {
		if (! isset ( self::$uploadFiles [$fileField] ) && empty ( $name )) {
			self::$uploadFiles [$fileField] = self::getInstance ( $model, $fileField ,$isApp );
		}
		if ($name) {
			self::$uploadFiles [$fileField] = self::getInstanceByName ( $name );
		}
		if (self::$uploadFiles [$fileField] !== null) {

			self::$savePath [$fileField] = Yii::getAlias ( '@uploads' );

// 			if ($savePath === 'uploads') {
// 				self::$alias = 'uploads';
// 				self::$savePath [$fileField] = Yii::getAlias ( '@uploads' );
// 			} else {
// 				self::$alias = 'att';
// 				self::$savePath [$fileField] = Yii::getAlias ( '@att' );
// 			}

			if (is_null ( $fileName )){
				$fileName = ToolHandler::generateSalt () . '.' . $model->suffix;
			}

			else
				$fileName = $fileName . '.' . self::$uploadFiles [$fileField]->getExtension ();
			$model->$fileField = $saveDir .'/' . date('Y/m/d') . '/' . $fileName;
			if($field){
				$model->$field = $fileName;				//添加文件名
			}
		}
		return $model;
	}

	/**
	 * 保存文件
	 *
	 * @param string $fileField
	 *        	AR对象中的文件字段
	 * @param string $file
	 *        	含相对路径文件名称(一般为入库名称)
	 * @param string $oldFile
	 *        	旧文件路径，$isUpdate必须为true生效
	 * @param boolean $isUpdate
	 *        	是否更新，为true则执行删除旧文件
	 * @return boolean
	 * @throws Exception
	 */
	public static function saveFile($fileField, $file, $oldFile = null, $isUpdate = false) {
		$dir = pathinfo ( $file, PATHINFO_DIRNAME );
		$savePath = isset ( self::$savePath [$fileField] ) ? self::$savePath [$fileField] : null;
		/** @var UploadedFile $UploadedFile */
		$UploadedFile = isset ( self::$uploadFiles [$fileField] ) ? self::$uploadFiles [$fileField] : null;
		if ($UploadedFile !== null) {
			// 如果配置了远程图片服务器目录，则ftp上传到远程图片服务器
			if (UPLOAD_REMOTE) {
				$ftp = Yii::$app->ftp;
				$fullPathFile = $ftp->dir . '/' . $file;
				if (! $ftp->createDir ( dirname ( $fullPathFile ) )) {
					throw new Exception ( 'create dir error' ,'0002');
				}
				$ftp->put ( $fullPathFile, $UploadedFile->tempName );
				if ($isUpdate === true) {
					@$ftp->delete ( $ftp->dir . '/' . $oldFile ); // 删除旧文件
				}
				return true;
			} else {
				ToolHandler::createDir ( $dir, $savePath );
				$uploadResult = $UploadedFile->saveAs ( $savePath . DS . $file ); // 保存新文件
				if (! $uploadResult) {
					throw new Exception ( 'save file error' ,'0002');
				}
				if ($isUpdate === true)
					@unlink ( $savePath . DS . $oldFile ); // 删除旧文件
				return true;
			}
		}
		return false;
	}


	function resizeImage($im,$maxwidth,$maxheight,$name,$filetype){
		$pic_width = imagesx($im);
		$pic_height = imagesy($im);
		if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight))
		{
			if($maxwidth && $pic_width>$maxwidth)
			{
				$widthratio = $maxwidth/$pic_width;
				$resizewidth_tag = true;
			}
			if($maxheight && $pic_height>$maxheight)
			{
				$heightratio = $maxheight/$pic_height;
				$resizeheight_tag = true;
			}
			if($resizewidth_tag && $resizeheight_tag)
			{
				if($widthratio<$heightratio)
					$ratio = $widthratio;
				else
					$ratio = $heightratio;
			}
			if($resizewidth_tag && !$resizeheight_tag)
				$ratio = $widthratio;
			if($resizeheight_tag && !$resizewidth_tag)
				$ratio = $heightratio;
			$newwidth = $pic_width * $ratio;
			$newheight = $pic_height * $ratio;
			if(function_exists("imagecopyresampled")){
				$newim = imagecreatetruecolor($newwidth,$newheight);//PHP系统函数
				imagecopyresampled($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);//PHP系统函数
			}
			else{
				$newim = imagecreate($newwidth,$newheight);
				imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			}

			$name = $name.$filetype;
			imagejpeg($newim,$name);
			imagedestroy($newim);
		}else{
			$name = $name.$filetype;
			imagejpeg($im,$name);
		}
	}

	public function a(){
		//使用方法：
		$im=imagecreatefromjpeg("./20140416103023202.jpg");//参数是图片的存方路径
		$maxwidth="600";//设置图片的最大宽度
		$maxheight="400";//设置图片的最大高度
		$name="123";//图片的名称，随便取吧
		$filetype=".jpg";//图片类型
		resizeImage($im,$maxwidth,$maxheight,$name,$filetype);//调用上面的函数
	}
}
