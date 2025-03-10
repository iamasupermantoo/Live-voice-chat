define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'packmanage/pack/index' + location.search,
                    add_url: 'packmanage/pack/add',
                    edit_url: 'packmanage/pack/edit',
                    del_url: 'packmanage/pack/del',
                    multi_url: 'packmanage/pack/multi',
                    table: 'pack',
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
                        {field: 'users.nickname', title: __('昵称'),operate:false,formatter: Controller.api.formatter.search_id},
                        {field: 'type', title: __('Type'),
                            //1宝石3卡卷4头像框5气泡框6进场特效7麦上光圈8徽章
                            searchList: $.getJSON("wares/getTypeList"),
                            //formatter: Controller.api.formatter.getType
                        },
                        {field: 'target_id', title: __('物品id')},
                        {field: 'val', title: __('物品'),operate:false},
                        {field: 'get_type', title: __('获取途径'),
                            //1vip等级自动获取2活动3宝箱4购买5=后台添加
                            // formatter: Controller.api.formatter.getGetType,
                            searchList: $.getJSON("wares/wares_get_way"),
                        },
                        {field: 'num', title: __('Num')},
                        {field: 'expire', title: __('Expire'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
                //Form.api.bindevent($("form[role=form]"));
                $(document).on("change", "#c-type", function () {
                    $("#c-target_id option[data-type='all']").prop("selected", true);
                    $("#c-target_id option").removeClass("hide");
                    $("#c-target_id option[data-type!='" + $(this).val() + "'][data-type!='all']").addClass("hide");
                    $("#c-target_id").data("selectpicker") && $("#c-target_id").selectpicker("refresh");
                });
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                // getType:function (value, row, index) {
                //     var msg = "";
                //     if (value == 1){
                //         msg += "宝石";
                //     }else if(value == 2){
                //         msg += "礼物";
                //     }else if(value == 3){
                //         msg += "卡卷";
                //     }else if(value == 4){
                //         msg += "头像框";
                //     }else if(value == 5){
                //         msg += "气泡框";
                //     }else if(value == 6){
                //         msg += "进场特效";
                //     }else if(value == 7){
                //         msg += "麦上光圈";
                //     }else if(value == 8){
                //         msg += "徽章";
                //     }
                //     return msg;
                // },
                // getGetType:function (value, row, index) {
                //     var msg = "";
                //     if (value == 1){
                //         msg += "vip等级自动获取";
                //     }else if(value == 2){
                //         msg += "活动";
                //     }else if(value == 3){
                //         msg += "宝箱";
                //     }else if(value == 4){
                //         msg += "购买";
                //     }else if(value == 5){
                //         msg += "后台修改";
                //     }else if(value == 6){
                //         msg += "限时购买";
                //     }else if(value == 7){
                //         msg += "宝箱积分兑换";
                //     }else if(value == 8){
                //         msg += "cp等级解锁";
                //     }
                //     return msg;
                // },
                search_id: function (value, row, index) {
                    var field = 'user_id';
                    return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', value) + '" data-field="' + field + '" data-value="' + row.user_id + '">' + value + '</a>';
                },
            },
        }
    };
    return Controller;
});