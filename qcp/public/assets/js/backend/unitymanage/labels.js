define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unitymanage/labels/index' + location.search,
                    add_url: 'unitymanage/labels/add',
                    edit_url: 'unitymanage/labels/edit',
                    del_url: 'unitymanage/labels/del',
                    multi_url: 'unitymanage/labels/multi',
                    table: 'labels',
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
                        {field: 'enable', title: __('Enable'),operate: false, formatter: Controller.api.formatter.custom},
                        {field: 'name', title: __('Name')},
                        {field: 'type', title: __('是否推荐'),
                            searchList: {"1":__('推荐'),"2":__('非推荐')},
                            formatter: Controller.api.formatter.typestr},

                        {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'unitymanage/labels/detail'
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
                typestr:function (value, row, index) {
                    return row.type == 2 ? "" : "<span class='label label-success'>推荐</span>";
                },
                custom: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="unitymanage/labels/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
        }
    };
    return Controller;
});