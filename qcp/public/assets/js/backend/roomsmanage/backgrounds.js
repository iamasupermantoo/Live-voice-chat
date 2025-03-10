define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'roomsmanage/backgrounds/index' + location.search,
                    add_url: 'roomsmanage/backgrounds/add',
                    edit_url: 'roomsmanage/backgrounds/edit',
                    del_url: 'roomsmanage/backgrounds/del',
                    multi_url: 'roomsmanage/backgrounds/multi',
                    table: 'backgrounds',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'enable asc,id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'img', title: __('Img'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'enable', title: __('是否启用'),
                            searchList:{ "1": "启用","2": "停用"},
                            formatter: Controller.api.formatter.custom},
                        {field: 'updated_at', title: __('Updated_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'roomsmanage/backgrounds/detail'
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
                custom: function (value, row, index) {
                    return '<a class="btn-change text-success" data-url="roomsmanage/backgrounds/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
        }
    };
    return Controller;
});