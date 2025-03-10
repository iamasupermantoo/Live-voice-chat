define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wares/index' + location.search,
                    add_url: 'wares/add',
                    edit_url: 'wares/edit',
                    del_url: 'wares/del',
                    multi_url: 'wares/multi',
                    table: 'wares',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'enable asc,id desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'enable', title: __('Enable'),formatter: Controller.api.formatter.isEnable,
                            searchList:{ "1": "启用","2": "禁用"},
                        },
                        {field: 'get_type', title: __('Get_type'),
                            /*searchList:{ "1": "vip等级自动获取","2": "活动","3": "宝箱","4": "购买"},*/
                            searchList: $.getJSON("wares/wares_get_way"),
                        },
                        {field: 'type', title: __('类型'),visible: false,
                            searchList: $.getJSON("wares/getTypeList"),
                        },
                        {field: 'name', title: __('Name'),operate:'like'},
                        {field: 'title', title: __('Title'),operate:'like'},
                        {field: 'price', title: __('price')},
                        {field: 'score', title: __('score')},
                        {field: 'level', title: __('Level')},
                        {field: 'show_img', title: __('Show_img'),formatter: Table.api.formatter.image,operate:false},
                        {field: 'img1', title: __('Img1'),formatter: Table.api.formatter.image,operate:false},
                        {field: 'img2', title: __('Img2'),formatter: Table.api.formatter.image,operate:false},
                        {field: 'img3', title: __('Img3'),formatter: Table.api.formatter.image,operate:false},
                        {field: 'color', title: __('Color')},
                        {field: 'expire', title: __('Expire'),formatter: Controller.api.formatter.get_expire},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wares/index?type=8',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'name', title: '名称'},
                        {field: 'show_img', title: '图片',formatter: Table.api.formatter.image},
                        {field: 'operate', title: __('Operate'), events: {
                                'click .btn-chooseone': function (e, value, row, index) {
                                    Fast.api.close(row);
                                },
                            }, formatter: function () {
                                return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                            }}
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
                isEnable: function (value, row, index) {
                        return '<a class="btn-change text-success" data-url="wares/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                get_expire:function (value, row, index) {
                    var msg = "";
                    if (value == 0 || !value){
                        msg += "<span class='text-red'>永久</span>";
                    }else {
                        msg += "<span>"+value+"天</span>";
                    }
                    return msg;
                },
            },
        }
    };
    return Controller;
});