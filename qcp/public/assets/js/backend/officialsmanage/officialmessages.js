define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'officialsmanage/officialmessages/index' + location.search,
                    add_url: 'officialsmanage/officialmessages/add',
                    edit_url: 'officialsmanage/officialmessages/edit',
                    del_url: 'officialsmanage/officialmessages/del',
                    multi_url: 'officialsmanage/officialmessages/multi',
                    table: 'official_messages',
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
                        {field: 'title', title: __('Title'),operate:'like'},
                        {field: 'img', title: __('图片'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'user_id', title: __('User_id'), operate: false, formatter: Controller.api.formatter.useridstr},
                        {field: 'type', title: __('Type'), operate: false, formatter: Controller.api.formatter.typestr},
                       /* {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},*/
                        {field: 'updated_at', title: __('Updated_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'officialsmanage/officialmessages/detail'
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
            formatter: {//渲染的方法
                typestr:function (value, row, index) {
                    return row.type == 1 ? "<span class='text-success'>系统消息</span>" : "<span class='text-red'>系统公告</span>";
                },
                useridstr:function (value, row, index) {
                    return row.user_id == 0 ? "<span class='text-success'>全体用户</span>" : "<span class='text-red'>其他用户:"+row.user_id+"</span>";
                }
            },
        }
    };
    return Controller;
});