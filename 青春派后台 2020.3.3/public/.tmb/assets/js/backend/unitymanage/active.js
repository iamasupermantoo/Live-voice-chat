define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unitymanage/active/index' + location.search,
                    add_url: 'unitymanage/active/add',
                    edit_url: 'unitymanage/active/edit',
                    del_url: 'unitymanage/active/del',
                    multi_url: 'unitymanage/active/multi',
                    table: 'active',
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
                        {field: 'enable', title: __('Enable'),
                            searchList:{ "1": "启用","2": "停用"},
                            formatter: Controller.api.formatter.enablestr},
                        {field: 'title', title: __('Title'),operate:'like'},
                        {field: 'img', title: __('Img'), formatter: Table.api.formatter.image,operate:'like'},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url,operate:'like'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'unitymanage/active/detail'
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
                enablestr: function (value, row, index) {
                    return value == 1 ? "<span class='label bg-red'>启用</span>" : "<span class='label bg-gray'>禁用</span>";
                },
            },
        }
    };
    return Controller;
});