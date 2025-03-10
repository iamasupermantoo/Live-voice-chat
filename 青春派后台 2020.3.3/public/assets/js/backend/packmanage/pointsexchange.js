define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'packmanage/pointsexchange/index' + location.search,
                    /*add_url: 'packmanage/pointsexchange/add',
                    edit_url: 'packmanage/pointsexchange/edit',
                    del_url: 'packmanage/pointsexchange/del',
                    multi_url: 'packmanage/pointsexchange/multi',*/
                    table: 'points_exchange',
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
                        {field: 'users.nickname', title: __('User_id'),formatter: Controller.api.formatter.search_id},
                        {field: 'nums', title: __('Nums'), operate:'BETWEEN',operate:false},
                        {field: 'wares.name', title: __('Wares_id'),operate:'like'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        /*{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}*/
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
            },
        }
    };
    return Controller;
});