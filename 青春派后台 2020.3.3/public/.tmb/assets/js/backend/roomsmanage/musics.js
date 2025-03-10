define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'roomsmanage/musics/index' + location.search,
                    add_url: 'roomsmanage/musics/add',
                    edit_url: 'roomsmanage/musics/edit',
                    del_url: 'roomsmanage/musics/del',
                    multi_url: 'roomsmanage/musics/multi',
                    table: 'musics',
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
                        {field: 'enable', title: __('Enable'),
                            searchList:{ "1": "启用","2": "停用"},
                            formatter: Controller.api.formatter.enable},
                        {field: 'music_url', title: __('试听'), formatter: Table.api.formatter.audio,operate:false},
                        {field: 'music_name', title: __('Music_name'),operate:'like'},
                        {field: 'upload_user', title: __('Upload_user'),operate:'like'},
                        {field: 'type', title: __('Type'),formatter: Controller.api.formatter.typestr},
                        {field: 'singer', title: __('Singer'),operate:'like'},
                        {field: 'is_default', title: __('Is_default'),
                            searchList:{ "1": "默认","2": "非默认"},
                            formatter: Controller.api.formatter.defaultstr},
                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'roomsmanage/musics/detail'
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
                /*custom: function (value, row, index) {
                    var imgurl = row.music_url;
                    var imgurlHead = imgurl.substring(0,8);
                    if (imgurlHead != '/upload/' && imgurlHead != 'http://4'){
                        imgurl = 'http://47.92.85.75/upload/'+imgurl;
                    }
                    return "<audio height='100px' width='100px' controls=''><source src='"+imgurl+"'></audio>";
                },*/
                enable: function (value, row, index) {
                    return '<a class="btn-change text-success" data-url="roomsmanage/musics/change?id=' + row.id + '" data-id="' + row.enable + '"><i class="fa ' + (row.enable == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                typestr:function (value, row, index) {
                    return row.type == 1 ? "<span class='label label-warning'>音乐</span>" : "<span class='label label-success'>音效</span>";
                },
                defaultstr:function (value, row, index) {
                    return '<a class="btn-change text-success" data-url="roomsmanage/musics/changeDefault?id=' + row.id + '" data-id="' + row.is_default + '"><i class="fa ' + (row.is_default == '2' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
            },
        }
    };
    return Controller;
});