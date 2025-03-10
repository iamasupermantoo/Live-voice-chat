define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'officialsmanage/searchhistory/index' + location.search,
                    //add_url: 'officialsmanage/searchhistory/add',
                    edit_url: 'officialsmanage/searchhistory/edit',
                    del_url: 'officialsmanage/searchhistory/del',
                    multi_url: 'officialsmanage/searchhistory/multi',
                    table: 'search_history',
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
                        {field: 'type', title: __('Type'),
                            searchList: {"1":__('官方热门搜索'),"2":__('用户搜索历史')},
                            formatter: Controller.api.formatter.typestr},
                        {field: 'user_id', title: __('User_id'),visible:false,operate:false},
                        {field: 'users.nickname', title: __('Nickname'),operate:'like', formatter: Table.api.formatter.search},
                        {field: 'search', title: __('Search'),operate:'like'},
                        {field: 'sort', title: __('Sort'),operate:false},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                            name = '官方热门搜索';
                            bdColor = 'red';
                            break;
                        case 2:
                            name = '用户搜索历史';
                            bdColor = 'green';
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
            },
        }
    };
    return Controller;
});