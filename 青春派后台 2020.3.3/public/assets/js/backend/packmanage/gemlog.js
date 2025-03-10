define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'packmanage/gemlog/index' + location.search,
                   /* add_url: 'packmanage/gemlog/add',
                    edit_url: 'packmanage/gemlog/edit',
                    del_url: 'packmanage/gemlog/del',*/
                    multi_url: 'packmanage/gemlog/multi',
                    table: 'gem_log',
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
                        {field: 'cp_id', title: 'cp_id',formatter: Controller.api.formatter.cp_id},
                        {field: 'wares_id', title: __('Wares_id')},
                        {field: 'val', title: __('宝石名字'),operate:false},
                        {field: 'user_id', title: __('赠送者'),visible:false},
                        {field: 'fromUid', title: __('接收者'),visible:false},
                        {field: 'users.nickname', title: __('赠送者'),formatter: Controller.api.formatter.search_id},
                        {field: 'users2.nickname', title: __('Fromuid'),formatter: Controller.api.formatter.search_fromUid},
                        {field: 'num', title: __('Num')},
                        {field: 'exp', title: __('Exp'),formatter: Controller.api.formatter.get_expire},
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
                get_expire:function (value, row, index) {
                    var msg = "";
                    if (value == 0 || !value){
                        msg += "<span class='text-red'>永久</span>";
                    }else {
                        msg += "<span>"+value+"天</span>";
                    }
                    return msg;
                },
                cp_id: function (value, row, index) {
                    var field = 'cp_id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.cp_id + '">' + value + '</a>';
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