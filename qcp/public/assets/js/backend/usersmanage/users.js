define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/users/index' + location.search,
                    // add_url: 'usersmanage/users/add',
                    edit_url: 'usersmanage/users/edit',
                    //del_url: 'usersmanage/users/del',
                    multi_url: 'usersmanage/users/multi',
                    updorder_url : 'usersmanage/users/updorder',
                    table: 'users',
                }
            });

            var table = $("#table");

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里可以获取从服务端获取的JSON数据
                //console.log(data);
                //这里我们手动设置底部的值
                $("#personnum").text(data.extend.personnum);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                exportDataType: 'basic',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'isOnline', title: __('在线状态'),
                            searchList:{ "1": "离线","2": "登出",'0':'在线'},
                            formatter: Controller.api.formatter.getisOline},
                        {field: 'headimgurl', title: __('Headimgurl'),formatter: Table.api.formatter.image,operate: false},
                        {field: 'nickname', title: __('Nickname'), formatter: Controller.api.formatter.search_id},
                        // {field: 'nickname', title: __('Nickname'), formatter: Table.api.formatter.search,operate: 'LIKE'},
                        {field: 'sex', title: __('Sex'),
                            searchList:{ "1": "男","2": "女"},
                            formatter: Controller.api.formatter.getSex
                        },


                        {
                            field: 'id', title: __('按钮'), table: table, buttons:
                                [
                                    {
                                        name: 'addname', text: '修改钱包', title: '修改钱包', icon: 'fa fa-list',
                                        classname: 'btn btn-xs btn-primary btn-dialog', url: 'usersmanage/users/updmoney',
                                    },
                                    {
                                        name: 'addname', text: '赠送钥匙', title: '赠送钥匙', icon: 'fa fa-list',
                                        classname: 'btn btn-xs btn-primary btn-dialog', url: 'usersmanage/users/sendkeys',
                                    }
                                ], 
                                
                                operate: false, formatter: Table.api.formatter.buttons
                        },

                        {field: 'id', title: __('记录'),formatter: Controller.api.formatter.getOrderlist, operate: false},
                        {field: 'mizuan', title: __('Mizuan'),sortable:true, operate:'BETWEEN'},
                        {field: 'mibi', title: __('Mibi'),sortable:true, operate:'BETWEEN'},
                        {field: 'r_mibi', title: __('R_mibi'),sortable:true, operate: false},

                        {field: 'keys_num', title: __('钥匙数量'),sortable:true, operate:'BETWEEN'},

                        //{field: 'province', title: __('Province'), operate: 'LIKE'},
                        // {field: 'city', title: __('City'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'name', title: __('Username'), operate: 'LIKE'},
                        {field: 'idno', title: __('Idno'), operate: 'LIKE'},
                        {field: 'is_sign', title: __('Is_sign'),
                            searchList:{ "0": "未签约","1": "已签约"},
                            formatter: Controller.api.formatter.getSign},

                        {field: 'is_idcard', title: __('Is_idcard'),
                            searchList:{ "0": "未审核","1": "已审核"},
                            formatter: Controller.api.formatter.getIsCard},
                        
                        {field: 'channel', title: __('来源渠道'),searchList: $.getJSON("usersmanage/Users/getChannelList")},
                        {field: 'system', title: __('系统'), searchList: $.getJSON("usersmanage/Users/getSystemList")},

                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/users/detail'
                            }],
                            formatter: Table.api.formatter.operate
                        },


                        // {field: 'mykeep', title: __('Mykeep'),visible: false,operate: false},
                        // {field: 'is_leader', title: __('Is_leader'),visible: false,operate: false},
                        // {field: 'updated_at', title: __('Updated_at'),visible: false,operate: false},
                        // {field: 'openid', title: __('Openid'),visible: false,operate: false},
                        // {field: 'unionid', title: __('Unionid'),visible: false,operate: false},
                        // {field: 'pass', title: __('Pass'),visible: false,operate: false},
                        // {field: 'birthday', title: __('Birthday'),visible: false,operate: false},
                        // {field: 'country', title: __('Country'),visible: false,operate: false},
                        // {field: 'constellation', title: __('Constellation'),visible: false,operate: false},
                        // {field: 'token', title: __('Token'),visible: false,operate: false},
                        // {field: 'scale', title: __('Scale'),visible: false,operate: false},
                        {field: 'status', title: __('Status'),visible: false,searchList:{ "1": "正常","2": "封禁"},},

                        {field: 'locktime', title: __('Locktime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,visible: false,operate: false},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        updmoney: function () {
            Controller.api.bindevent();
        },
        sendkeys: function () {
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
                var refreshkey = function (data) {
                    $("input[name='row[badge]']").val(data.eventkey).trigger("change");
                    Layer.closeAll();
                    var keytitle = data.id;
                    var cont = $(".clickbox .create-click:first");
                    $(".keytitle", cont).remove();
                    if (keytitle) {
                        $('#c-badge').val(keytitle);
                        //cont.prepend('<div class="keytitle">' + __('徽章ID') + ':' + keytitle + '</div>');
                        var img= '<img width="115px" height="80px" src="http://47.92.85.75/upload/'+data.show_img+'" />'; 
                        cont.append('<div class="keytitle">' + img + '</div>');
                    }
                };
                $(document).on('click', "#select-resources", function () {
                    var key = $("input[name='row[badge]']").val();
                    parent.Backend.api.open($(this).attr("href") + "?key=" + key, __('Select'), {callback: refreshkey});
                    return false;
                });

                $(document).on('click', "#add-resources", function () {
                    parent.Backend.api.open($(this).attr("href") + "?key=", __('Add'), {callback: refreshkey});
                    return false;
                });
            },
            formatter: {
                getSex:function (value, row, index) {
                    return row.sex == 1 ? "<span class='text-red'>男</span>" : "<span class='text-green'>女</span>";
                },
                //是否签约
                getSign:function (value, row, index) {
                    return row.is_sign == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                //是否审核
                getIsCard:function (value, row, index) {
                    return row.is_idcard == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                //获取充值记录
                getOrderlist:function (value, row, index) {
                    //这里手动构造URL
                    var url1 = "usersmanage/order?user_id=" + value;
                    var url2 = "financemanage/exchange?user_id=" + value;
                    // var url3 = "usersmanage/follows?user_id=" + value;
                    // var url4 = "usersmanage/follows?followed_user_id=" + value;

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    var a1 =  '<a href="' + url1 + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('充值', value) + '</a>';
                    var a2 =  '&nbsp;&nbsp;<a href="' + url2 + '" class="label label-warning addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('兑换', value) + '</a>';
                    // var a3 =  '&nbsp;&nbsp;<a href="' + url3 + '" class="label label-danger addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('关注', value) + '</a>';
                    // var a4 =  '&nbsp;&nbsp;<a href="' + url4 + '" class="label label-primary addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('粉丝', value) + '</a>';

                    return a1+a2;
                },
                search_id: function (value, row, index) {
                    var field = 'id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.id + '">' + value + '</a>';
                },
                getisOline:function (value, row, index) {
                    
                    var msg = '';
                    if (row.isOnline == 1){
                        msg = "<span class='label bg-gray'>离线</span>";
                    }else if(row.isOnline == 2){
                        msg = "<span class='label bg-gray'>登出</span>";
                    }else{
                        msg = "<span class='label bg-red'>在线</span>";
                    }
                    return msg;
                },
            },

        }
    };
    return Controller;
});