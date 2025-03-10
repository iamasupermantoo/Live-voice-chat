define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/order/index' + location.search,
                   /* add_url: 'usersmanage/order/add',*/
                    /*edit_url: 'usersmanage/order/edit',
                    del_url: 'usersmanage/order/del',*/
                    multi_url: 'usersmanage/order/multi',
                    table: 'order',
                }
            });

            var table = $("#table");

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里可以获取从服务端获取的JSON数据
                //console.log(data);
                //这里我们手动设置底部的值
                $("#money").text(data.extend.money);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'order_no', title: __('Order_no'),operate:'like'},
                        //1未付款  2已付款 3后台充值 4冻结
                        {field: 'status', title: __('Status'),
                            searchList: {"1":__('未付款'),"2":__('已付款'),"3":__('后台充值'),"4":__('冻结')},
                            formatter: Controller.api.formatter.getStatus},
                        {field: 'user_id', title: __('充值者ID'),visible: false},
                        {field: 'users.nickname', title: __('User_id'),formatter: Controller.api.formatter.search_id},
                        {field: 'mizuan', title: __('Mizuan'), operate:'BETWEEN',operate:false},
                        {field: 'price', title: __('Price'), operate:'BETWEEN',operate:false},
                        {field: 'pay_type', title: __('Pay_type'),
                            searchList: {"1":__('支付宝'),"2":__('微信'),"3":__('苹果'),"4":__('微信公众号')},
                            formatter: Controller.api.formatter.getPaytype},
                        /*{field: 'remark', title: __('Remark'),operate:'like'},*/

                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/order/detail'
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
                getPaytype:function (value, row, index) {
                    var pay_type = row.pay_type;
                    if(pay_type == 4){
                        return "<span class='text-red'>微信公众号</span>";
                    }else{
                        return (pay_type == 3 ? "<span class='text-red'>苹果</span>" : (pay_type == 2 ? "<span class='text-green'>微信</span>" : "<span class='text-blue'>支付宝</span>"));

                    }
                },
                getStatus:function (value, row, index) {
                    var pay_status = row.status;
                    var msg = "";
                    if (pay_status == 4){
                        msg += "<span class='text-gray'>冻结</span>";
                    }else if(pay_status == 3){
                        msg += "<span class='text-blue'>后台充值</span>";
                    }else if(pay_status == 2){
                        msg += "<span class='text-green'>已付款</span>";
                    }else{
                        msg += "<span class='text-red'>未付款</span>";
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