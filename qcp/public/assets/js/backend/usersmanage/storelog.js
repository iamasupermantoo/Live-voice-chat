define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/storelog/index' + location.search,
                    /*add_url: 'usersmanage/storelog/add',
                    edit_url: 'usersmanage/storelog/edit',
                    del_url: 'usersmanage/storelog/del',*/
                    multi_url: 'usersmanage/storelog/multi',
                    table: 'store_log',
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
                        {field: 'user_id', title: __('用户id'),visible: false},
                        {field: 'users.nickname', title: __('User_id'),operate:'like',formatter: Controller.api.formatter.search_id},
                        {field: 'get_nums', title: __('变动金额'), operate:'BETWEEN',operate:false},
                        {field: 'get_type', title: __('Get_type'),
                            searchList: $.getJSON("usersmanage/storelog/get_types"),
                        },
                        {field: 'now_nums', title: __('Now_nums'),operate:false},
                        {field: 'addtime', title:  __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title:  __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/storelog/detail'
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
                getName:function (value, row, index) {
                    return $.trim(value).length !== 0 ? value : row.users.name;
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