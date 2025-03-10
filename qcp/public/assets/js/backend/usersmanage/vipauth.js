define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/vipauth/index' + location.search,
                    add_url: 'usersmanage/vipauth/add',
                    edit_url: 'usersmanage/vipauth/edit',
                    del_url: 'usersmanage/vipauth/del',
                    multi_url: 'usersmanage/vipauth/multi',
                    table: 'vip_auth',
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
                        {field: 'type', title: __('分类'),
                            searchList: $.getJSON("usersmanage/Vipauth/vip_auth_type"),
                        },
                        {field: 'level', title: __('Level')},
                        {field: 'name', title: __('Name'),operate: 'like'},
                        {field: 'title', title: __('Title'),operate: 'like'},
                        {field: 'img_0', title: __('Img_0'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'img_1', title: __('Img_1'), formatter: Table.api.formatter.image, operate: false},

                        {field: 'addtime', title: __('创建时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/vipauth/detail'
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
            }
        }
    };
    return Controller;
});