define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'boxsmanage/award/index' + location.search,
                   /* add_url: 'boxsmanage/award/add',*/
                    /*edit_url: 'boxsmanage/award/edit',*/
                    del_url: 'boxsmanage/award/del',
                    multi_url: 'boxsmanage/award/multi',
                    table: 'award',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'is_special desc,id desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'is_special', title: __('是否特殊礼物'),
                            searchList:{ "0": "否","1": "是"},
                            formatter: Controller.api.formatter.get_is_special
                        },
                        {field: 'enable', title: __('是否加入奖池'),operate: false, formatter: Controller.api.formatter.custom},
                        {field: 'status', title: __('Status'),

                            searchList:{ "0": "已开","1": "未开"},
                            formatter: Controller.api.formatter.getStatus
                        },
                        {field: 'class', title: __('Class'),
                            searchList:{ "1": "普通宝箱","2": "守护宝箱"},
                            formatter: Controller.api.formatter.getClass
                        },
                        {field: 'type', title: __('Type'),

                            //1宝石3卡卷4头像框5气泡框6进场特效7麦上光圈8徽章
                            searchList: $.getJSON("wares/getTypeList"),
                            formatter: Controller.api.formatter.getType
                        },
                        {field: 'wares_id', title: __('Wares_id')},
                        {field: 'val', title: __('val'),operate:false},

                        /*{field: 'num', title: __('Num')},*/
                        
                        

                        
                        
                        {field: 'term', title: __('term')},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                getType:function (value, row, index) {
                    var msg = "";
                    if (value == 1){
                        msg += "宝石";
                    }else if(value == 2){
                        msg += "礼物";
                    }else if(value == 3){
                        msg += "卡卷";
                    }else if(value == 4){
                        msg += "头像框";
                    }else if(value == 5){
                        msg += "气泡框";
                    }else if(value == 6){
                        msg += "进场特效";
                    }else if(value == 7){
                        msg += "麦上光圈";
                    }else if(value == 8){
                        msg += "徽章";
                    }
                    return msg;
                },
                getClass:function(value, row, index){
                    var msg = "";
                    if (value == 1){
                        msg += "普通宝箱";
                    }else if(value == 2){
                        msg += "守护宝箱";
                    }
                    return msg;
                },
                getIsplay:function(value, row, index){
                    var msg = "";
                    if (value == 0){
                        msg += "不播报";
                    }else if(value == 1){
                        msg += "全服播报";
                    }
                    return msg;
                },
                getStatus:function(value, row, index){
                    var msg = "";
                    if (value == 0){
                        msg += "<span class='text-green'>已开</span>";
                    }else if(value == 1){
                        msg += "<span class='text-red'>未开</span>";
                    }
                    return msg;
                },
                get_is_special:function(value, row, index){
                    var msg = "";
                    if(value == 1){
                        msg += "<span class='text-red'>是</span>";
                    }else{
                        msg += "否";
                    }
                    return msg;
                },
                custom: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="boxsmanage/award/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '1' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },

        }
    };
    return Controller;
});