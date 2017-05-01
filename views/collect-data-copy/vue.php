<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Collect Data Copies');
$this->params['breadcrumbs'][] = $this->title;
?>
<script src="https://unpkg.com/vue/dist/vue.js"></script>
<div id="data">
    <table class="table table-striped table-hover">
        <tr v-for="todo in datas">
            <td>{{todo.title}}</td>
            <td>{{todo.learn_name}}</td>
            <td>{{todo.is_exist}}</td>
            <td>{{todo.is_download}}</td>
        </tr>
    </table>
</div>

<div id="page">
    <button v-for="list in lists" v-on:click="clickEvent(list.page)">{{list.page}}</button>
</div>
<script>
    var data = new Vue({
        el:'#data',
        data:{
            datas:[
                <?php foreach ($data as $value):?>
                {
                    title: '<?php echo $value["title"]?>' ,
                    learn_name: '<?php echo $value["learn_name"]?>',
                    is_exist: '<?php echo $value["is_exist"]?>',
                    is_download: '<?php echo $value["is_download"]?>'
                },
                <?php endforeach;?>
            ]
        }
    });

new Vue({
    el:'#page',
    data:{
        lists:[
            {page:1},
            {page:2},
            {page:3},
            {page:4}
        ]
    },
    methods:{
        clickEvent:function (page) {
            url = '<?php echo Yii::$app->urlManager->createUrl("/collect-data-copy/result")?>';
            $.post(url,{page:page},function (res) {
                data.datas = res;
            },'json');
        }
    }
});
</script>