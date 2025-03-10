define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'officialsmanage/reporttypes/index' + location.search,
                    add_url: 'officialsmanage/reporttypes/add',
                    edit_url: 'officialsmanage/reporttypes/edit',
                    del_url: 'officialsmanage/reporttypes/del',
                    multi_url: 'officialsmanage/reporttypes/multi',
                    table: 'report_types',
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
                        {field: 'name', title: __('Report_name')},
                        {field: 'enable', title: __('Enable'), operate: false, formatter: Controller.api.formatter.custom},
                        {field: 'updated_at', title: __('Updated_at')},
                        {field: 'created_at', title: __('Created_at')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'officialsmanage/reporttypes/detail'
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
                    return '<a class="btn-change text-success" data-url="officialsmanage/reporttypes/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
        }
    };
    return Controller;
});