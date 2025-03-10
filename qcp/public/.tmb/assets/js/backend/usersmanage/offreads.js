define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/offreads/index' + location.search,
                    add_url: 'usersmanage/offreads/add',
                    //edit_url: 'usersmanage/offreads/edit',
                    del_url: 'usersmanage/offreads/del',
                    multi_url: 'usersmanage/offreads/multi',
                    table: 'off_reads',
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
                        {field: 'offmsg.title', title: __('Off_id')},
                        {field: 'users.nickname', title: __('User_id')},
                        {field: 'is_read', title: __('Is_read'),
                            searchList: {"1":__('已读'),"2":__('已删除')},
                            formatter: Controller.api.formatter.readstr},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/offreads/detail'
                            }],
                            formatter: Table.api.formatter.operate}
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
                readstr:function (value, row, index) {
                    return row.is_read == 1 ? "<span class='label bg-gray'>已读</span>" : "<span class='label bg-red'>已删除</span>";
                },
            },
        }
    };
    return Controller;
});