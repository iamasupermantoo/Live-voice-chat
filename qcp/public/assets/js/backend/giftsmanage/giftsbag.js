define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'giftsmanage/giftsbag/index' + location.search,
                    add_url: 'giftsmanage/giftsbag/add',
                    edit_url: 'giftsmanage/giftsbag/edit',
                    /*del_url: 'giftsmanage/giftsbag/del',
                    multi_url: 'giftsmanage/giftsbag/multi',*/
                    table: 'gifts_bag',
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
                        {field: 'name', title: __('Name')},
                        {field: 'gift_ids', title: __('礼物id'),operate:'like'},
                        {field: 'gift_names', title: __('礼物名称'),operate:false},
                       /* {field: 'enable', title: __('Enable')},*/
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), 

                            buttons: [
                                {
                                    name: 'detail',
                                    title: __('已获得用户'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'giftsmanage/giftsbag/user_list',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                            ],
                            table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        user_list: function () {
            var table = $("#table");
            var ids   = $("#ids").val();


            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'giftsmanage/giftsbag/user_list?ids='+ids,
                }
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                search: false,
                showExport: false,
                showToggle: false,
                showColumns: false,
                commonSearch: false,
                pagination:false,

                columns: [
                    [
                        {field: 'fromUid', title: '用户id',formatter: Controller.api.formatter.uid},
                        {field: 'nickname', title: '昵称'},
                        
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
            formatter: {//渲染的方法
                uid: function (value, row, index) {
                    //这里手动构造URL
                    var url = "packmanage/pack?user_id=" + row.fromUid;
                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", row.fromUid) + '">' + __('%s', value) + '</a>';
                },

            },
        }
    };
    return Controller;
});