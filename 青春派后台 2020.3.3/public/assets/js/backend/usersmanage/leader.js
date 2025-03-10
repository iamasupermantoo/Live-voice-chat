define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/leader/index' + location.search,
                    add_url: 'usersmanage/leader/add',
                    edit_url: 'usersmanage/leader/edit',
                    del_url: 'usersmanage/leader/del',
                    multi_url: 'usersmanage/leader/multi',
                    table: 'leader',
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

                        {field: 'users2.nickname', title: __('Uid'),operate:'like'},
                        {field: 'users.nickname', title: __('User_id'),operate:'like'},
                        {field: 'scale', title: __('Scale'),operate:false},
                        {field: 'status', title: __('类型'),
                            searchList:{ "1": "房主发出申请","2": "主播接受邀请","3": "房主解除关系","4": "主播解除关系"},
                            formatter: Controller.api.formatter.statusstr},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/leader/detail'
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
                    var name = '';
                    var bdColor = '';
                    switch(value){
                        case 1:
                            name = '房主发出申请';
                            bdColor = 'red';
                            break;
                        case 2:
                            name = '主播接受邀请';
                            bdColor = 'green';
                            break;
                        case 3:
                            name = '房主解除关系';
                            bdColor = 'blue';
                            break;
                        case 4:
                            name = '主播解除关系';
                            bdColor = 'yellow';
                            break;
                    }
                    return "<span class='label bg-"+bdColor+"'>"+name+"</span>";
                },
            },
        }
    };
    return Controller;
});