define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'configmanage/config/index' + location.search,
                    add_url: 'configmanage/config/add',
                    edit_url: 'configmanage/config/edit',
                    del_url: 'configmanage/config/del',
                    multi_url: 'configmanage/config/multi',
                    table: 'config',
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
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.statusstr,operate:false},
                        {field: 'title', title: __('Title'),operate:'like'},
                        {field: 'name', title: __('Name'),operate:'like'},
                        {field: 'value', title: __('Value'),operate:'like'},
                        {field: 'sort', title: __('Sort'),operate:false},

                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'configmanage/config/detail'
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
                    //return row.status == 1 ? "<span class='label bg-red'>启用</span>" : "<span class='label bg-gray'>禁用</span>";
                    //custom: function (value, row, index) {
                        return '<a class="btn-change text-success" data-url="configmanage/config/change?id=' + row.id + '" data-id="' + row.status + '"><i class="fa ' + (row.status == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                   // },
                },
            },
        }
    };
    return Controller;
});