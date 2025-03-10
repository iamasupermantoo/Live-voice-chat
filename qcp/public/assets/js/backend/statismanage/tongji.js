define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'statismanage/tongji/index' + location.search,
                    add_url: 'statismanage/tongji/add',
                    // edit_url: 'statismanage/tongji/edit',
                    // del_url: 'statismanage/tongji/del',
                    multi_url: 'statismanage/tongji/multi',
                    table: 'tongji',
                }
            });

            var table = $("#table");
            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                //这里可以获取从服务端获取的JSON数据
                //console.log(data);
                //这里我们手动设置底部的值
                $("#register").text(data.extend.statics.register);
                $("#active").text(data.extend.statics.active);
                $("#recharge").text(data.extend.statics.recharge);
                $("#tixian").text(data.extend.statics.tixian);
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
                        {field: 'logtime', title: __('Logtime'), operate: false},
                        {field: 'register', title: __('Register'), operate: false},
                        {field: 'active', title: __('Active'), operate: false},
                        {field: 'recharge', title: __('Recharge'), operate: false},
                        {field: 'tixian', title: __('Tixian'), operate: false},
                        
                        {field: 'addtime', title: __('选择日期'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            }
        }
    };
    return Controller;
});