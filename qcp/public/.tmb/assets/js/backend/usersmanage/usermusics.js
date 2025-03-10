define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'usersmanage/usermusics/index' + location.search,
                    add_url: 'usersmanage/usermusics/add',
                    edit_url: 'usersmanage/usermusics/edit',
                    del_url: 'usersmanage/usermusics/del',
                    multi_url: 'usersmanage/usermusics/multi',
                    table: 'user_musics',
                }
            });

            var table = $("#table");

            //在普通搜索提交搜索前
            table.on('common-search.bs.table', function (event, table, query) {
                //这里可以获取到普通搜索表单中字段的查询条件
                console.log(query);
            });

            //在普通搜索渲染后
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='title']", form).addClass("selectpage").data("source", "auth/adminlog/selectpage").data("primaryKey", "title").data("field", "title").data("orderBy", "id desc");
                $("input[name='users.nickname']", form).addClass("selectpage").data("source", "usersmanage/users/index").data("primaryKey", "nickname").data("field", "nickname").data("orderBy", "id desc");
                Form.events.cxselect(form);
                Form.events.selectpage(form);
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
                        {field: 'users.nickname', title: __('User_id'), formatter: Table.api.formatter.search},
                        //{field: 'username', title: __('管理员'), formatter: Table.api.formatter.search},
                        {field: 'music_url', title: __('Music_url'), formatter: Table.api.formatter.audio, operate: false},
                        {field: 'enable', title: __('Enable'),
                            searchList:{ "1": "启用","2": "禁用"},
                            formatter: Controller.api.formatter.enablestr},
                        {field: 'music_name', title: __('Music_name'), operate: 'LIKE'},
                        {field: 'singer', title: __('Singer'), operate: 'LIKE'},
                        {field: 'upload_user', title: __('Upload_user')},
                        {field: 'updated_at', title: __('Updated_at'), operate: false},
                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'usersmanage/usermusics/detail'
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
                enablestr:function (value, row, index) {
                    return value == 1 ? "<span class='label bg-red'>启用</span>" : "<span class='label bg-gray'>禁用</span>";
                },
                /*custom: function (value, row, index) {
                    var imgurl = row.music_url;
                    var imgurlHead = imgurl.substring(0,8);
                    if (imgurlHead != '/upload/' && imgurlHead != 'http://4'){
                        imgurl = 'http://47.92.85.75/upload/'+imgurl;
                    }
                    return "<audio height='100px' width='100px' controls=''><source src='"+imgurl+"'></audio>";
                },*/
            },
        }
    };
    return Controller;
});