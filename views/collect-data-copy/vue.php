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
</div>

<div id="page">
    <button v-for="list in lists" v-on:click="clickEvent(list.page)">{{list.page}}</button>
</div>
<script>
    var cache = {};
    var url = '<?php echo Yii::$app->urlManager->createUrl("/collect-data-copy/result")?>';

    var ajaxGetData = function (page) {
        if(page in cache){
            data.datas = cache[page];
        }else{
            Vue.http.post(url, {page:page,'<?= Yii::$app->request->csrfParam ?>': '<?= Yii::$app->request->getCsrfToken() ?>'},
                {'emulateJSON':true}).then(function (res) {
                data.datas = res.body;
                cache[page] = res.body;
            });
        }
    };

    var data = new Vue({
        el:'#data',
        data:{
            datas:[]
        },
        created:function(){
            ajaxGetData(25);
        }
    });

new Vue({
    el:'#page',
    data:{
        lists:[
            {page:25},
            {page:26},
            {page:27},
            {page:28}
        ]
    },
    methods:{
        clickEvent:function (page) {
            ajaxGetData(page);
        }
    }
});
</script>