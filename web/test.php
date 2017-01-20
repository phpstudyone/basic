<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/1/12
 * Time: 12:54
 */

$url = "http://guangdong.circ.gov.cn/tabid/878/ctl/ViewOrganization/mid/6522/ItemID/794615/Default.aspx?ctlmode=none";

/**
 * 使用CURL方式获取网页内容
 * @param string $url 要获取的内容的url
 * @return string mixed 获取的内容
 */
function getContentByCurl($url){
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);			// 执行之后不直接打印出来
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 这样能够让cURL支持页面链接跳转
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}
set_time_limit(0);
ini_set('memory_limit','1000M');
for ($i = 800000; $i >= 1 ; $i--){
    $url = "http://guangdong.circ.gov.cn/tabid/878/ctl/ViewOrganization/mid/6522/ItemID/" .$i . "/Default.aspx?ctlmode=none";
    $content = getContentByCurl($url);
    if($content){
        $preg = "/<span id=\"ess_ctr\d{1,}_ViewOrganization_lblComName\">(.*)<\/span>/";
        $matches = [];
        preg_match($preg,$content,$matches);
        if(isset($matches[1]) && !empty($matches[1])){
            $str = $url . "\t" . $matches[1] . "\r\n";
            file_put_contents('title.txt',$str ,FILE_APPEND);
        }
    }
}