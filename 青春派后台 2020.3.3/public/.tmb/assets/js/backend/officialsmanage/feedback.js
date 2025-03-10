define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'officialsmanage/feedback/index' + location.search,
                    add_url: 'officialsmanage/feedback/add',
                    edit_url: 'officialsmanage/feedback/edit',
                    del_url: 'officialsmanage/feedback/del',
                    multi_url: 'officialsmanage/feedback/multi',
                    table: 'feedback',
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
                        {field: 'users.nickname', title: __('User_id'),operate: 'like'},
                        {field: 'content', title: __('Content'),operate: 'like'},
                        {field: 'img', title: __('Img'),formatter: Table.api.formatter.image, operate: false},
                        {field: 'status', title: __('Status'),
                            searchList:{ "1": "未处理","2": "已处理"},
                            formatter: Controller.api.formatter.statusstr},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'officialsmanage/feedback/detail'
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
                statusstr: function (value, row, index) {
                    return value == 1 ? "<span class='label bg-red'>未处理</span>" : "<span class='label bg-gray'>已处理</span>";
                },
            },

        }
    };
    return Controller;
});