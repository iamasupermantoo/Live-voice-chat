define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/vip/index' + location.search,
                    add_url: 'usersmanage/vip/add',
                    edit_url: 'usersmanage/vip/edit',
                    del_url: 'usersmanage/vip/del',
                    multi_url: 'usersmanage/vip/multi',
                    table: 'vip',
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
                        {field: 'type', title: __('类型'),
                            searchList:{ "1": "星锐","2": "金锐","3":'vip'},
                            formatter: function (value) {
                                if (value == 1) {
                                    return '星锐';
                                } else if (value == 2) {
                                    return '金锐';
                                } else {
                                    return 'vip';
                                }
                            },
                        },
                        {field: 'level', title: __('等级')},
                        {field: 'exp', title: __('Exp')},
                        {field: 'mizuan', title: __('Mizuan')},
                        {field: 'rmb', title: __('Rmb')},
                        {field: 'addtime', title: __('创建时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/vip/detail'
                            }],
                            formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // var options = table.bootstrapTable(tableOptions);
                var typeStr = $(this).attr("href").replace('#','');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    // params.filter = JSON.stringify({type: typeStr});
                    params.type = typeStr;

                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });
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