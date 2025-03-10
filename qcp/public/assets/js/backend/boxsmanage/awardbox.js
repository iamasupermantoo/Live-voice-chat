define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'boxsmanage/awardbox/index' + location.search,
                    add_url: 'boxsmanage/awardbox/add',
                    edit_url: 'boxsmanage/awardbox/edit',
                    del_url: 'boxsmanage/awardbox/del',
                    multi_url: 'boxsmanage/awardbox/multi',
                    table: 'award_box',
                }
            });

            var table = $("#table");
            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                $("#nums").text(data.extend.nums);
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
                        {field: 'wares_id', title: __('Wares_id')},
                        {field: 'is_special', title: __('是否特殊礼物'),
                            searchList:{ "0": "否","1": "是"},
                            formatter: Controller.api.formatter.get_is_special
                        },
                        {field: 'img', title: __('Img'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'val', title: __('物品'),operate:false},
                        {field: 'type', title: __('Type'),
                            //1宝石2礼物3卡卷4头像框5气泡框6进场特效7麦上光圈8徽章
                            /*searchList: {"1":__('宝石'),"2":__('礼物'),"3":__('卡卷'),"4":__('头像框'),
                            "5":__('气泡框'),"6":__('进场特效'),"7":__('麦上光圈'),"8":__('徽章')},*/
                            searchList: $.getJSON("wares/getTypeList"),
                            //formatter: Controller.api.formatter.getType

                        },
                        
                        {field: 'box_type', title: __('Box_type'),
                            searchList:{ "1": "普通宝箱","2": "守护宝箱"},
                            formatter: Controller.api.formatter.getBoxtype
                        },
                        {field: 'is_play', title: __('Is_play'),
                            searchList:{ "0": "不播报","1": "全服播报"},
                            formatter: Controller.api.formatter.getIsplay
                        },
                        {field: 'is_public_play', title: __('是否公屏'),
                            searchList:{ "0": "否","1": "是"},
                            formatter: Controller.api.formatter.getIspublic
                        },
                        {field: 'num', title: __('num'),operate:false},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, 
                            
                            buttons: [
                                {
                                    name: '生成奖池数据',
                                    title: __('生成奖池数据'),
                                    text: '生成奖池数据',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'boxsmanage/awardbox/saveAwardData',
                                    success: function (data, ret) {
                                        //alert(data);
                                        Layer.alert(ret.msg);
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },
                                    error: function (data, ret) {
                                        //console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                            ],
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
                $(document).on("change", "#c-type", function () {
                    $("#c-wares_id option[data-type='all']").prop("selected", true);
                    $("#c-wares_id option").removeClass("hide");
                    $("#c-wares_id option[data-type!='" + $(this).val() + "'][data-type!='all']").addClass("hide");
                    $("#c-wares_id").data("selectpicker") && $("#c-wares_id").selectpicker("refresh");
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
                getBoxtype:function(value, row, index){
                    var msg = "";
                    if (value == 1){
                        msg += "普通宝箱";
                    }else if(value == 2){
                        msg += "守护宝箱";
                    }
                    return msg;
                },
                getIsplay:function(value, row, index){
                    var msg = "";
                    if (value == 0){
                        msg += "不播报";
                    }else if(value == 1){
                        msg += "全服播报";
                    }
                    return msg;
                },
                getIspublic:function(value, row, index){
                    var msg = "";
                    if(value == 1){
                        msg += "<span class='text-red'>是</span>";
                    }else{
                        msg += "否";
                    }
                    return msg;
                },
                get_is_special:function(value, row, index){
                    var msg = "";
                    if(value == 1){
                        msg += "<span class='text-red'>是</span>";
                    }else{
                        msg += "否";
                    }
                    return msg;
                },

            }
        }
    };
    return Controller;
});