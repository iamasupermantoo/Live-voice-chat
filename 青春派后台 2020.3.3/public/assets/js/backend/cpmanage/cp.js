define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cpmanage/cp/index' + location.search,
                   /* add_url: 'cpmanage/cp/add',
                    edit_url: 'cpmanage/cp/edit',
                    del_url: 'cpmanage/cp/del',*/
                    multi_url: 'cpmanage/cp/multi',
                    table: 'pack',
                }
            });

            var table = $("#table");

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
                        {field: 'fromUid', title: __('fromUid'),visible:false},
                        {field: 'users.nickname', title: __('User_id'),operate:false,formatter: Controller.api.formatter.search_id},
                        {field: 'users2.nickname', title: __('fromUid'),operate:false,formatter: Controller.api.formatter.search_fromUid},
                        {field: 'wares.name', title: __('Target_id')},
                        {field: 'num', title: __('Num')},
                        {field: 'exp', title: __('exp')},
                       
                       
                        {field: 'agreetime', title: __('Agreetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'refusetime', title: __('Refusetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('状态'),
                            searchList:{ "1": "守护中","2": "已解除","3":"等待对方同意","4":"拒绝"},
                            formatter: Controller.api.formatter.statusStr
                        },
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                statusStr: function (value, row, index) {
                    var msg = '守护中';
                    if (value == 1){
                        msg = '守护中';
                    }else if(value == 2){
                        msg = '已解除';
                    }else if(value == 3){
                        msg = '等待对方同意';
                    }else if(value == 4){
                        msg = '拒绝';
                    }
                    return msg;
                },
                search_id: function (value, row, index) {
                    var field = 'user_id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.user_id + '">' + value + '</a>';
                },
                search_fromUid: function (value, row, index) {
                    var field = 'fromUid';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.fromUid + '">' + value + '</a>';
                },
            },
        }
    };
    return Controller;
});