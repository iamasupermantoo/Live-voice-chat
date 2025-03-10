define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'financemanage/tixian/index' + location.search,
                    add_url: 'financemanage/tixian/add',
                    edit_url: 'financemanage/tixian/edit',
                    del_url: 'financemanage/tixian/del',
                    multi_url: 'financemanage/tixian/multi',
                    table: 'tixian',
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
                        {field: 'status', title: __('Status'),searchList: {"1":__('新申请'),"2":__('已提现'),"3":__('取消')}, formatter: Controller.api.formatter.getStatus},
                        {field: 'users.nickname', title: __('User_id'),operate:'like'},
                        {field: 'order_no', title: __('order_no'),operate:'like'},
                        {field: 'money', title: __('Money'), operate:false},

                        {field: 'tx_time', title: __('tx_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'financemanage/tixian/detail'
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
                getStatus:function (value, row, index) {
                    var msg = "";
                    if (value == 1){
                        msg += "<span class='text-red'>新申请</span>";
                    }else if(value == 2){
                        msg += "<span class='text-green'>已提现</span>";
                    }else if(value == 3){
                        msg += "<span class='text-grey'>取消</span>";
                    }
                    return msg;
                }
            },
        }
    };
    return Controller;
});