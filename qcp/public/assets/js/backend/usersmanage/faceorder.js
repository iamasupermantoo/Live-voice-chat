define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/faceorder/index' + location.search,
                    //add_url: 'usersmanage/faceorder/add',
                    //edit_url: 'usersmanage/faceorder/edit',
                    //del_url: 'usersmanage/faceorder/del',
                    multi_url: 'usersmanage/faceorder/multi',
                    table: 'face_order',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'users.nickname', title: __('Nickname'),operate:'like'},
                        {field: 'order_no', title: __('Order_no'),operate:'like'},
                        {field: 'name', title: __('Name'),operate:'like'},
                        {field: 'idno', title: __('Idno'),operate:'like'},
                        {field: 'certify_id', title: __('Certify_id'),operate:'like'},
                        {field: 'status', title: __('Status'),
                            searchList: {"1":__('未认证'),"3":__('认证成功'),"2":__('认证失败')},
                            formatter: Controller.api.formatter.statusstr},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'finish_time', title: __('Finish_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/faceorder/detail'
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
                statusstr:function (value, row, index) {
                    var name = '';
                    var bdColor = '';
                    switch(value){
                        case 1:
                            name = '未认证';
                            bdColor = 'red';
                            break;
                        case 2:
                            name = '认证失败';
                            bdColor = 'green';
                            break;
                        case 3:
                            name = '认证成功';
                            bdColor = 'blue';
                            break;
                    }
                    return "<span class='label bg-"+bdColor+"'>"+name+"</span>";
                },
            },
        }
    };
    return Controller;
});