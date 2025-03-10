define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'giftsmanage/gifts/index' + location.search,
                    add_url: 'giftsmanage/gifts/add',
                    edit_url: 'giftsmanage/gifts/edit',
                    del_url: 'giftsmanage/gifts/del',
                    multi_url: 'giftsmanage/gifts/multi',
                    table: 'gifts',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'enable asc,id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'enable', title: __('上架/下架'),
                            searchList:{ "1": "上架","2": "下架"},
                            formatter: Controller.api.formatter.custom},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'),
                            searchList:{ "1": "普通礼物","2": "热门礼物"},
                            formatter: Controller.api.formatter.typestr},
                        {field: 'price', title: __('Price'),sortable:true},
                        {field: 'img', title: __('Img'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'show_img', title: __('Show_img'), formatter: Table.api.formatter.image, operate: false},
                        //{field: 'show_img2', title: __('Show_img2'), formatter: Table.api.formatter.image, operate: false},


                        {field: 'hot', title: __('Hots')},
                        {field: 'is_play', title: __('is_play'),
                            searchList:{ "0": "不播报","1": "全服播报"},
                            formatter: Controller.api.formatter.playstr},
                        {field: 'vip_level', title: __('vip_level')},
                        {field: 'sort', title: __('Sort')},

                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'giftsmanage/gifts/detail'
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
            formatter: {//渲染的方法
                typestr:function (value, row, index) {
                    return row.type == 2 ? "<span class='label label-warning'>热门</span>" : "<span class='label label-success'>普通</span>";
                },
                playstr:function (value, row, index) {
                    return row.is_play == 1 ? "<span class='label label-warning'>全服播报</span>" : "<span class='label label-success'>不播报</span>";
                },
                custom: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="giftsmanage/gifts/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
        }
    };
    return Controller;
});