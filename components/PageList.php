<?php
/**
 * Created by PhpStorm.
 * User: Winds10
 * Date: 2017/5/7
 * Time: 19:40
 */
namespace app\components;
class PageList{
    /**
     * 返回分页列表
     * @param int $total 总数
     * @param int $page 当前页数
     * @param int $count 每页显示数
     */
    public static function lists($total,$page = 1,$count = 10){
        $data = [];
        $pageCount = $total / $count;
        $data[] = ['no'=>1,'is_show'=>$page == 1 ? true : false] ;
        if($pageCount <= 10) {
            for ($i = 2 ; $i < $pageCount  ; $i++){
                $data[] = ['no'=> $i, 'is_show'=>$page == $i ? true : false];
            }
        }else{
            for ($i = 2 ; $i < $pageCount  ; $i++){
                if( $i<3 || $i > ($pageCount - 3) ){
                    $data[] = ['no'=> $i, 'is_show'=>$page == $i ? true : false];
                }else{
                    if($i == $page){
                        if($i-2 > 4){
                            $jLeft = $i -2;
                            $jRight = $pageCount - 3;
                        }else{
                            $jLeft = 3;
                            $jRight = 6;
                        }
                        for ($j = $jLeft ; $j <= $jRight ; $j++){
                            $data[] = ['no'=> $j, 'is_show'=>$page == $j ? true : false];
                        }
                    }
                }
            }
        }
        $data[] = [
            'no'=>(int) ceil($pageCount),
            'is_show'=>$page == (int) ceil($pageCount) ? true : false
        ] ;
        return $data;
    }
}