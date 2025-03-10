define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/black/index' + location.search,
                    add_url: 'usersmanage/black/add',
                    edit_url: 'usersmanage/black/edit',
                    del_url: 'usersmanage/black/del',
                    multi_url: 'usersmanage/black/multi',
                    table: 'black',
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
                        {field: 'users.nickname', title: __('User_id'),operate:'like'},
                        {field: 'users2.nickname', title: __('From_uid'),operate:'like'},
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.statusstr,operate:false},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,

                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/black/detail'
                            }],
                            formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            //为表格绑定事件
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
                statusstr: function (value, row, index) {
                    return row.status == 1 ? "<span class='label bg-red'>拉黑</span>" : "<span class='label bg-gray'>解除拉黑</span>";
                },
            },
        }
    };
    return Controller;
});