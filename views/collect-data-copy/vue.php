<?php
$this->title = Yii::t('app', 'Collect Data Copies');
$this->params['breadcrumbs'][] = $this->title;
?>
<script src="https://unpkg.com/vue/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/vue.resource/1.3.1/vue-resource.min.js"></script>
<div id="data">
    <table class="table table-striped table-hover">
        <tr v-for="todo in datas">
            <td>{{todo.title}}</td>
            <td>{{todo.learn_name}}</td>
            <td>{{todo.is_exist}}</td>
            <td>{{todo.is_download}}</td>
        </tr>
    </table>
    <button  v-bind:class="[{ 'btn btn-success': list.is_show ,'btn btn-info': !list.is_show }]" v-for="list in lists" v-on:click="clickEvent(list.no)">{{list.no}}</button>
</div>
<script>
    var cache = {};
    var url = '<?php echo Yii::$app->urlManager->createUrl("/collect-data-copy/vue")?>';
    var ajaxGetData = function (page) {
        if(page in cache){
            data.datas = cache[page].data;
            data.lists = cache[page].list;
        }else{
            Vue.http.post(url, {page:page,'<?= Yii::$app->request->csrfParam ?>': '<?= Yii::$app->request->getCsrfToken() ?>'},
                {'emulateJSON':true}).then(function (res) {
                data.datas = res.body.data;
                data.lists = res.body.list;
                cache[page] = res.body;
            });
        }
    };

    var data = new Vue({
        el:'#data',
        data:{
            datas:{},
            lists:{}
        },
        created:function(){
            ajaxGetData(45);
        },
        methods:{
            clickEvent:function (page) {
                ajaxGetData(page);
            }
        }
    });
</script>