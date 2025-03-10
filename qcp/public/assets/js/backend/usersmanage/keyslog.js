define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/keyslog/index' + location.search,
                    // add_url: 'usersmanage/keyslog/add',
                    // edit_url: 'usersmanage/keyslog/edit',
                    // del_url: 'usersmanage/keyslog/del',
                    multi_url: 'usersmanage/keyslog/multi',
                    table: 'keys_log',
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
                        {field: 'user_id', title: __('User_id'),visible:false},
                        {field: 'users.nickname', title: __('昵称'),formatter: Controller.api.formatter.search_id},
                        {field: 'get_nums', title: __('Get_nums')},
                        {field: 'get_type', title: __('类型'),
                            searchList:{ "0": "购买","1": "后台赠送","2": "开宝箱消耗"},
                            formatter: Controller.api.formatter.get_type,
                        },
                        {field: 'now_nums', title: __('Now_nums'), operate:'BETWEEN'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'adduser', title: __('Adduser')},
                        //{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                get_type:function (value, row, index) {
                    var  msg = '购买';
                    if (value == 1){
                        msg = '后台赠送';
                    }else if(value == 2){
                        msg = '开宝箱消耗';
                    }
                    return msg;
                },
                search_id: function (value, row, index) {
                    var field = 'user_id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.user_id + '">' + value + '</a>';
                },
            },
        }
    };
    return Controller;
});