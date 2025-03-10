define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'packmanage/baoyinka/index' + location.search,
                    /*add_url: 'packmanage/baoyinka/add',
                    edit_url: 'packmanage/baoyinka/edit',
                    del_url: 'packmanage/baoyinka/del',*/
                    multi_url: 'packmanage/baoyinka/multi',
                    table: 'baoyinka',
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
                        {field: 'fromUid', title: __('Fromuid'),visible:false},
                        {field: 'users.nickname', title: __('Uid'),operate:'like'},
                        {field: 'users2.nickname', title: __('User_id'),formatter: Controller.api.formatter.search_id},
                        {field: 'users3.nickname', title: __('Fromuid'),formatter: Controller.api.formatter.search_fromUid},
                        {field: 'wares.name', title: __('物品名称'),operate:'like'},
                        {field: 'num', title: __('Num')},
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
                search_id: function (value, row, index) {
                    var field = 'user_id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.user_id + '">' + value + '</a>';
                },
                search_fromUid: function (value, row, index) {
                    var field = 'fromUid';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.fromUid + '">' + value + '</a>';
                },
            },
        },

    };
    return Controller;
});