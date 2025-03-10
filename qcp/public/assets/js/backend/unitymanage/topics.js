define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unitymanage/topics/index' + location.search,
                    add_url: 'unitymanage/topics/add',
                    edit_url: 'unitymanage/topics/edit',
                    del_url: 'unitymanage/topics/del',
                    multi_url: 'unitymanage/topics/multi',
                    table: 'topics',
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
                        {field: 'recom', title: __('是否推荐'),
                            searchList:{ "0": "非推荐","1": "推荐"},
                            formatter: Controller.api.formatter.recomstr},
                        {field: 'topic_img', title: __('Topic_img'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'labels.name', title: __('Tags'),operate: 'like'},
                        {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'unitymanage/topics/detail'
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
                recomstr: function (value, row, index) {
                    return value == 1 ? "<span class='label bg-red'>推荐</span>" : "<span class='label bg-gray'>非推荐</span>";
                },
            },
        }
    };
    return Controller;
});