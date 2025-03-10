define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'officialsmanage/report/index' + location.search,
                    add_url: 'officialsmanage/report/add',
                    edit_url: 'officialsmanage/report/edit',
                    del_url: 'officialsmanage/report/del',
                    multi_url: 'officialsmanage/report/multi',
                    table: 'report',
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
                        {field: 'users.nickname', title: __('User_id')},
                        {field: 'type', title: __('Type'),searchList: {"1":__('用户'),"2":__('房间'),"3":__('动态')}, formatter: Controller.api.formatter.typestr,visible: false},
                        {field: 'users.nickname', title: __('Target'),formatter: Controller.api.formatter.getContents},
                        {field: 'img', title: __('Img'), formatter: Table.api.formatter.image},
                        {field: 'reporttypes.name', title: __('Report_type')},
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.statusstr},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            /*buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'officialsmanage/report/detail'
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
                typestr: function (value, row, index) {
                    var name = '';
                    var bdColor = '';
                    switch(row.type){
                        case 1:
                            name = '用户';
                            bdColor = 'red';
                            break;
                        case 2:
                            name = '房间';
                            bdColor = 'green';
                            break;
                        case 3:
                            name = '动态';
                            bdColor = 'blue';
                            break;
                    }
                    return "<span class='label bg-"+bdColor+"'>"+name+"</span>";
                },
                statusstr: function (value, row, index) {
                    var name = '';
                    var bdColor = '';
                    switch(row.type){
                        case 1:
                            name = '用户';
                            bdColor = 'red';
                            break;
                        case 2:
                            name = '房间';
                            bdColor = 'green';
                            break;
                        case 3:
                            name = '动态';
                            bdColor = 'blue';
                            break;
                    }
                    return row.status == 1 ? "<span class='label bg-red'>未处理</span>" : "<span class='label bg-gray'>已处理</span>";
                },
                getContents:function (value, row, index) {
                    return row.type == 3 ? row.dynamics.content: row.users2.nickname;
                },
            },
        }
    };
    return Controller;
});