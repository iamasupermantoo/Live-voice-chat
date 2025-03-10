define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/likes/index' + location.search,
                    add_url: 'usersmanage/likes/add',
                    //edit_url: 'usersmanage/likes/edit',
                    del_url: 'usersmanage/likes/del',
                    multi_url: 'usersmanage/likes/multi',
                    table: 'likes',
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
                        {field: 'type', title: __('Type'),searchList: {"1":__('点赞动态'),"2":__('收藏动态'),"3":__('转发动态'),"4":__('点赞评论')}, formatter: Controller.api.formatter.typestr},
                        {field: 'users.nickname', title: __('User_id')},
                        {field: 'dynamics.content', title: __('Target_id'),formatter: Controller.api.formatter.getContents},
                        {field: 'is_read', title: __('Is_read'),formatter: Controller.api.formatter.readstr},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            /*buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/likes/detail'
                            }],*/
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
                    switch(value){
                        case 1:
                            name = '点赞动态';
                            bdColor = 'red';
                            break;
                        case 2:
                            name = '收藏动态';
                            bdColor = 'green';
                            break;
                        case 3:
                            name = '转发动态';
                            bdColor = 'blue';
                            break;
                        case 4:
                            name = '点赞评论';
                            bdColor = 'yellow';
                            break;
                    }
                    return "<span class='label bg-"+bdColor+"'>"+name+"</span>";
                },
                readstr:function (value, row, index) {
                    return row.is_read == 1 ? "<span class='label bg-gray'>已读</span>" : "<span class='label bg-red'>未读</span>";
                },
                getContents:function (value, row, index) {
                    return row.type == 4 ? "<p title='"+row.dynamiccomments.content+"' style='width:200px;text-overflow:ellipsis;overflow:hidden;'>"+row.dynamiccomments.content
                        +"</p>": "<p title='"+row.dynamics.content+"' style='width:200px;text-overflow:ellipsis;overflow:hidden;'>"+row.dynamics.content+"</p>";
                },
            },
        }
    };
    return Controller;
});