<?php
/**
 * wap项目测试方法
 */
namespace app\controllers;

use yii\web\Controller;

class ApitestController extends Controller
{
    public $testUrl = "http://www.svn2.com/";			//本地开发
//    public $testUrl = "http://172.16.40.250:8011/";			//服务器调试  svn2
//    public $testUrl = "http://172.16.40.250:9011/";			//服务器开发svn1
//    public $testUrl = "http://www.svn1.com/";			//本地调试

    public function beforeAction($action){
        parent::beforeAction($action);
       header ( "Content-type:text/html;charset=utf-8" );
        return true;
    }


    /**
     * 验证码测试方法
     */
    public function actionOrderList(){
        $arr = [
            'token'=>'fb940161827ac12d875b0b26c0b4ca14'
        ];
        $url = $this->testUrl.'wap/order/user_order_list';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    /**
     * 验证码测试方法
     */
    public function actionCaptcha(){
        $arr = [
            'token'=>'b060699fd93af56a87be98754349254ecd2aa0f9'
        ];
        $url = $this->testUrl.'wap/login/create_captcha';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    /**
     * 检测验证码
     */
    public function actionCheckCaptcha(){
        $arr = [
            'token'=>'b060699fd93af56a87be98754349254ecd2aa0f9',
            'image_code'=>'bsyr'
        ];
        $url = $this->testUrl.'wap/login/check_img_code';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    public function actionTest(){
        $arr = [
            'txt' => '你是谁',
            'text' => '你是谁',
        ];
        $str = "txt=%E9%98%BF%E6%96%AF%E8%92%82%E8%8A%AC";
        $url = "http://www.niurenqushi.com/api/simsimi/";

        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_URL,$url) ;
//        curl_setopt($ch, CURLOPT_POSTFIELDS,$arr);
        curl_setopt($ch, CURLOPT_POST,$str);
//        curl_setopt($ch,CURLOPT_HTTPHEADER,array("X-HTTP-Method-Override: POST"));
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();
        curl_close($ch) ;
        echo $result;
    }

    /**
     * 注册方法测试
     */
    public function actionRegister(){
        $arr = [
            'mobile'=>'18011947530',
            'password'=>'12345',
            'mobile_code'=>'12345',
            'image_code'=>'12345',
            'token'=>'b060699fd93af56a87be98754349254ecd2aa0f9'
        ];
        $url = $this->testUrl.'wap/login/register';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    /**
     * 登录方法测试
     */
    public function actionLogin(){
        $arr = [
            'account'=>'15920332055',
            'password'=>'wurongfei',
        ];
        $url = $this->testUrl.'wap/login/login';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    /**
     * 测试添加购物车
     */
    public function actionProductAddToCard(){
        $arr = [
            'token' => '4de8bb2063ffa26103df5403f5934ef995041604',
            'product_id'=>8032504,          //baiyang_group_goods 表id
            'qty'=>1,
            'group_id'=>'90',           //baiyang_favourable_group表id
            'group_qty'=>'1',
        ];
        $url = $this->testUrl.'wap/cart/product_add_to_cart';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }
    /**
     * 购物车列表
     */
    public function actionCartProductList(){
        $arr = [
            'token' => '4de8bb2063ffa26103df5403f5934ef995041604',
        ];
        $url = $this->testUrl.'wap/cart/cart_product_list';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    /**
     * 搜索
     */
    public function actionSearchIndex(){
        $arr = [
            'keyword' => '小儿',
            'page' => '2',
            'size' => '10',
            'sort_field' => 'price',
            'sort_type' => 'asc',
        ];
        $url = $this->testUrl.'wap/search/index';
        $data = $this->curlTest($url, $arr);
        $a = json_decode($data,true)['data']['products'];
        foreach ($a as $value){
            echo   "id-------" . $value['product_id'];
            echo "\tprice----" . $value['price'] . "<br>";
        }
        echo $data;
    }

    /**
     * 获取搜索关键词联想词数目
     */
    public function actionSearchWord(){
        $arr = [
            'word' => 'out',
        ];
        $url = $this->testUrl.'wap/search/search_word';
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    /**
     * http://58.63.114.90:8008/home/item/show?item_id=22 接口
     */
    public function actionEsearch(){
        $arr = [
            'searchName' => '小儿',
            'type' => 'all',
            'pageStart' => '1',
            'pageSize' => '10',
        ];
        $url = "http://es.baiyjk.com/es/getDataFromApp.do";
        $data = $this->curlTest($url, $arr);
        echo $data;
    }

    /**
     * curl测试接口
     * @param $url:接口地址
     * @param $data:传入参数
     * @return $result:返回的数据
     */
    public function curlTest($url,$data=array()){
        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_URL,$url) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();
        curl_close($ch) ;
        return $result;
    }
}
