define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'carouselsmanage/homecarousels/index' + location.search,
                    add_url: 'carouselsmanage/homecarousels/add',
                    edit_url: 'carouselsmanage/homecarousels/edit',
                    del_url: 'carouselsmanage/homecarousels/del',
                    multi_url: 'carouselsmanage/homecarousels/multi',
                    table: 'home_carousels',
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
                        {field: 'img', title: __('Img'),formatter: Table.api.formatter.image,operate:false},
                        {field: 'updated_at', title: __('Updated_at'),operate:false},
                        {field: 'created_at', title: __('Created_at'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'carouselsmanage/homecarousels/detail'
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