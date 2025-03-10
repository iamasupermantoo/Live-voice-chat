define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/follows/index' + location.search,
                    add_url: 'usersmanage/follows/add',
                    edit_url: 'usersmanage/follows/edit',
                    del_url: 'usersmanage/follows/del',
                    multi_url: 'usersmanage/follows/multi',
                    table: 'follows',
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
                        {field: 'user_id', title: __('User_id'),visible: false},
                        {field: 'followed_user_id', title: __('Followed_user_id'),visible: false},

                        {field: 'users.nickname', title: __('Nickname')},
                        {field: 'users2.nickname', title: __('Nickname2')},



                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.statusstr},
                        {field: 'updated_at', title: __('Updated_at')},
                        {field: 'created_at', title: __('Created_at')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/follows/detail'
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
                statusstr:function (value, row, index) {
                    return row.status == 1 ? "<span class='label label-warning'>关注</span>" : "<span class='label label-success'>取消关注</span>";
                },
            },
        }
    };
    return Controller;
});