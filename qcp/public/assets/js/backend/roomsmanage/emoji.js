define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'roomsmanage/emoji/index' + location.search,
                    add_url: 'roomsmanage/emoji/add',
                    edit_url: 'roomsmanage/emoji/edit',
                    del_url: 'roomsmanage/emoji/del',
                    multi_url: 'roomsmanage/emoji/multi',
                    table: 'emoji',
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
                        {field: 'pid', title: __('pid'),visible: false},//visible 列表是否显示,operate 是否允许搜索
                        {field: 'enable', title: __('Enable'), operate: false, formatter: Controller.api.formatter.custom},
                        {field: 'id', title: __('下级'),formatter: Controller.api.formatter.getEmojiList, operate: false},
                        {field: 'name', title: __('Name'), operate:'like'},
                        {field: 'pname', title: __('Pname'), operate:false},
                        {field: 'emoji', title: __('Emoji'), formatter: Table.api.formatter.image, operate: false},
                        {field: 't_length', title: __('T_length'), operate:false},
                        {field: 'sort', title: __('Sort')},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'roomsmanage/emoji/detail'
                            }],
                            formatter: Table.api.formatter.operate
                        }
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
                custom: function (value, row, index) {
                    return '<a class="btn-change text-success" data-url="roomsmanage/emoji/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                //获取下级动态表情
                getEmojiList:function (value, row, index) {
                    //这里手动构造URL
                    var url1 = "roomsmanage/emoji?pid=" + value;
                    // var url2 = "financemanage/exchange?user_id=" + value;
                    // var url3 = "usersmanage/follows?user_id=" + value;
                    // var url4 = "usersmanage/follows?followed_user_id=" + value;

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    var a1 =  '<a href="' + url1 + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('动态表情', value) + '</a>';
                    // var a2 =  '&nbsp;&nbsp;<a href="' + url2 + '" class="label label-warning addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('兑换', value) + '</a>';
                    // var a3 =  '&nbsp;&nbsp;<a href="' + url3 + '" class="label label-danger addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('关注', value) + '</a>';
                    // var a4 =  '&nbsp;&nbsp;<a href="' + url4 + '" class="label label-primary addtabsit" title="' + __("Search %s", value) + '"><i class="fa fa-list"></i>' + __('粉丝', value) + '</a>';

                    // return a1+a2+a3+a4;
                    return a1;
                },
            },
        }
    };
    return Controller;
});