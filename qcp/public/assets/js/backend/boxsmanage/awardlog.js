define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'boxsmanage/awardlog/index' + location.search,
                    /*add_url: 'boxsmanage/awardlog/add',
                    edit_url: 'boxsmanage/awardlog/edit',
                    del_url: 'boxsmanage/awardlog/del',*/
                    multi_url: 'boxsmanage/awardlog/multi',
                    table: 'award_log',
                }
            });

            var table = $("#table");
            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里可以获取从服务端获取的JSON数据
                // console.log(data);
                //这里我们手动设置底部的值
                $("#money").text(data.sum);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id'),visible:false},
                        {field: 'users.nickname', title: __('name'),operate:false,formatter: Controller.api.formatter.search_id},
                        {field: 'type', title: __('Type'),visible:false,
                            //1宝石3卡卷4头像框5气泡框6进场特效7麦上光圈8徽章
                            searchList: $.getJSON("wares/getTypeList"),
                        },
                        {field: 'type_name', title: __('Type'),operate:false,formatter: Controller.api.formatter.search_type},
                        {field: 'wares_id', title: __('Wares_id'),visible:false},
                        {field: 'val', title: __('Waresname'),operate:false,formatter: Controller.api.formatter.search_wares_id},
                        {field: 'is_play', title: __('Is_play'),
                            searchList:{ "0": "未播报","1": "已播报"},
                            formatter: Controller.api.formatter.getIsplay
                        },
                        {field: 'num', title: __('Num')},
                        {field: 'term', title: __('期数')},
                        {field: 'box_type', title: __('宝箱类型'),
                            searchList:{ "1": "普通宝箱","2": "守护宝箱"},
                            formatter: Controller.api.formatter.getBoxtype
                        },
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                getIsplay:function(value, row, index){
                    var msg = "";
                    return value == 1 ? "已播报" : "<span class='label bg-red'>未播报</span>";
                },
                getBoxtype:function(value, row, index){
                    var msg = "";
                    if (value == 1){
                        msg += "普通宝箱";
                    }else if(value == 2){
                        msg += "守护宝箱";
                    }
                    return msg;
                },
                search_id: function (value, row, index) {
                    var field = 'user_id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.user_id + '">' + value + '</a>';
                },
                search_wares_id: function (value, row, index) {
                    var field = 'wares_id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.wares_id + '">' + value + '</a>';
                },
                search_type: function (value, row, index) {
                    var field = 'type';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.type + '">' + value + '</a>';
                },
            },
        }
    };
    return Controller;
});