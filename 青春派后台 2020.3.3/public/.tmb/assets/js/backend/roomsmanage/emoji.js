define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'roomsmanage/emoji/index' + location.search,
                    add_url: 'roomsmanage/emoji/add',
                    edit_url: 'roomsmanage/emoji/edit',
                    del_url: 'roomsmanage/emoji/del',
                    multi_url: 'roomsmanage/emoji/multi',
                    table: 'emoji',
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
                        {field: 'enable', title: __('Enable'), operate: false, formatter: Controller.api.formatter.custom},
                        /*{field: 'pid', title: __('Pid')},*/
                        {field: 'name', title: __('Name'), operate:'like'},
                        {field: 'pname', title: __('Pname'), operate:false},
                        {field: 'emoji', title: __('Emoji'), formatter: Table.api.formatter.image, operate: false},

                        {field: 't_length', title: __('T_length'), operate:false},
                        {field: 'sort', title: __('Sort')},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'roomsmanage/emoji/detail'
                            }],
                            formatter: Table.api.formatter.operate
                        }
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
                    return '<a class="btn-change text-success" data-url="roomsmanage/emoji/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
        }
    };
    return Controller;
});