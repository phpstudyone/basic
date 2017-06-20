<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/1/12
 * Time: 12:54
 */

$url = "http://guangdong.circ.gov.cn/tabid/878/ctl/ViewOrganization/mid/6522/ItemID/794615/Default.aspx?ctlmode=none";
set_time_limit(0);
ini_set('memory_limit','1000M');

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

$handle = @fopen("./title.txt", "r");
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle);
        $arr = explode("\t",$buffer);
        if(!empty($arr[0])){
            $array = [];
            $array['url'] = $arr[0];
            $array['title'] = $arr[1];
            preg_match('/\d{6}/',$arr[0],$matchArr);
            if(!empty($matchArr[0])){
                $url = 'http://localhost:9200/tbl/album_story/' . $matchArr[0];
                $data = json_encode($array);
                putCurl($url,$data);
            }else continue;
            $array['id'] = '';
        }else continue;
    }
    fclose($handle);
}
function putCurl($url,$data){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_COOKIESESSION, true);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $result = curl_exec($curl);
    if (json_decode($result, true)) {
        $result = json_decode($result, true);
    } else {
        $result = json_decode(substr($result, 3), true);
    }
}die;

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