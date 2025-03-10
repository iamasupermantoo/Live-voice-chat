define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'financemanage/tixian/index' + location.search,
                    // add_url: 'financemanage/tixian/add',
                    edit_url: 'financemanage/tixian/edit',
                    // del_url: 'financemanage/tixian/del',
                    multi_url: 'financemanage/tixian/multi',
                    batch_url : 'usersmanage/users/batch',
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
                        {field: 'user_id', title: __('User_id'),visible:false},
                        {field: 'users.nickname', title: __('User_id'),formatter: Controller.api.formatter.search_id},
                        {field: 'users.nickname', title: __("用户房间礼物记录"),operate:'like',formatter: Controller.api.formatter.uid},
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
            // 获取选中项
            $(document).on("click", ".btn-selected", function () {
                //Layer.alert(JSON.stringify(table.bootstrapTable('getSelections')));
                var arr = table.bootstrapTable('getSelections');
                var ids = '';
                $.each(arr, function(i,n){
                    ids += n.id+',';
                });

                layer.open({
                    type: 1,
                    area: ['500px', '200px'],
                    title: ['批量审核', 'background-color:#dae7f5'],
                    btn: ['确定', '取消'],
                    content: $('#batch'),
                    yes: function (index, layero) {
                        var tixian_code = $('#tixian_code').val();
                        if (!tixian_code){
                            layer.msg('请输入短信验证码');return;
                        }
                        $.ajax({
                            type: "POST",
                            url: "financemanage/tixian/batch_do",
                            data: {
                                tixian_code:tixian_code,
                                ids:ids,
                            },
                            dataType: "json",
                            success: function (data) {
                                if (data.code == 1){
                                    layer.alert(data.msg);
                                    setTimeout(function(){
                                        window.location.reload(1);
                                    }, 5000);

                                }else{
                                    layer.msg(data.msg);
                                }
                            }
                        });
                    },

                    btn2: function (index) {
                        layer.close(index);
                    }
                });
            });
        },
        batch: function () {
            Controller.api.bindevent();
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
                },
                uid: function (value, row, index) {
                    //这里手动构造URL
                    url = "giftsmanage/giftlogs?uid=" + row.user_id;

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('%s', value) + '</a>';
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