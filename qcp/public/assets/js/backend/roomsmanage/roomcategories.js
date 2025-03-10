define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'roomsmanage/roomcategories/index' + location.search,
                    add_url: 'roomsmanage/roomcategories/add',
                    edit_url: 'roomsmanage/roomcategories/edit',
                    //del_url: 'roomsmanage/roomcategories/del',
                    multi_url: 'roomsmanage/roomcategories/multi',
                    table: 'room_categories',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'enable as,id desc',
                sortOrder: 'asc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'enable', title: __('是否启用'),
                            searchList:{ "1": "启用","2": "禁用"},
                            formatter: Controller.api.formatter.custom
                        },
                        {field: 'pid', title: __('上级'),
                            searchList: $.getJSON("roomsmanage/roomcategories/getPidList"),
                            formatter: Controller.api.formatter.getPidList
                        },
                        {field: 'name', title: __('Name'),operate: 'LIKE'},
                        {field: 'updated_at', title: __('更新时间'),operate:false},
                        {field: 'created_at', title: __('创建时间'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'roomsmanage/roomcategories/detail'
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
                getPidList:function (value, row, index) {
                    
                    return row.pname;
                },
                custom: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="roomsmanage/roomcategories/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
        }
    };
    return Controller;
});