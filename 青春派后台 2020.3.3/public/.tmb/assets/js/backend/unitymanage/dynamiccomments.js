define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unitymanage/dynamiccomments/index' + location.search,
                    add_url: 'unitymanage/dynamiccomments/add',
                    edit_url: 'unitymanage/dynamiccomments/edit',
                    del_url: 'unitymanage/dynamiccomments/del',
                    multi_url: 'unitymanage/dynamiccomments/multi',
                    table: 'dynamic_comments',
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
                        {field: 'dynamics.content', title: __('B_dynamic_id'),operate:'like'},
                        {field: 'dynamics2.content', title: __('Pid'),operate:'like'},
                        {field: 'users2.nickname', title: __('Hf_uid'),operate:'like'},
                        {field: 'users.nickname', title: __('User_id'),operate:'like'},
                        {field: 'content', title: __('Content'),operate:'like'},
                        {field: 'praise', title: __('Praise'),operate:false},
                        {field: 'is_read', title: __('Is_read'),
                            searchList:{ "0": "未读","1": "已读"},
                            formatter: Controller.api.formatter.readstr},
                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updated_at', title: __('Updated_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'unitymanage/dynamiccomments/detail'
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
        edits: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                readstr: function (value, row, index) {
                    return value == 0 ? "<span class='label bg-red'>未读</span>" : "<span class='label bg-gray'>已读</span>";
                },
            },
        }
    };
    return Controller;
});