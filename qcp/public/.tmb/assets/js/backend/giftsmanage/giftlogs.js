define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'giftsmanage/giftlogs/index' + location.search,
                    add_url: 'giftsmanage/giftlogs/add',
                    edit_url: 'giftsmanage/giftlogs/edit',
                    del_url: 'giftsmanage/giftlogs/del',
                    multi_url: 'giftsmanage/giftlogs/multi',
                    table: 'gift_logs',
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
                        {field: 'gifts.name', title: __('Giftid'),operate:'LIKE'},
                        {field: 'users.nickname', title: __('Uid')},
                        {field: 'giftName', title: __('Giftname'),operate:'LIKE'},
                        {field: 'giftNum', title: __('Giftnum')},
                        {field: 'giftPrice', title: __('Giftprice'), operate:'BETWEEN'},
                        {field: 'users2.nickname', title: __('User_id'),operate:'LIKE'},
                        {field: 'users3.nickname', title: __('Fromuid'),operate:'LIKE'},
                        {field: 'is_play', title: __('Is_play'),
                            searchList:{ "1": "已播报","2": "未播报"},
                            formatter: Controller.api.formatter.playstr},
                        {field: 'platform_obtain', title: __('Platform_obtain'), operate:'BETWEEN'},
                        {field: 'fromUid_obtain', title: __('Fromuid_obtain'), operate:'BETWEEN'},
                        {field: 'uid_obtain', title: __('Uid_obtain'), operate:'BETWEEN'},
                        {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'giftsmanage/giftlogs/detail'
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
                playstr:function (value, row, index) {
                    return row.is_play == 1 ? "<span class='label label-warning'>已播报</span>" : "<span class='label label-success'>未播报</span>";
                },
            },
        }
    };
    return Controller;
});