define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unitymanage/dynamics/index' + location.search,
                    add_url: 'unitymanage/dynamics/add',
                    edit_url: 'unitymanage/dynamics/edit',
                    del_url: 'unitymanage/dynamics/del',
                    multi_url: 'unitymanage/dynamics/multi',
                    table: 'dynamics',
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
                        {field: 'users.nickname', title: __('User_id'), formatter: Controller.api.formatter.uid,operate:'like'},
                        {field: 'image', title: __('image'), formatter: Table.api.formatter.images, operate: false},
                        {field: 'audio', title: __('Audio'), formatter: Table.api.formatter.audio,operate:false},


                        /*{field: 'audio_time', title: __('Audio_time')},*/
                        {field: 'video', title: __('Video'), formatter: Table.api.formatter.video,operate:false},
                        {field: 'tags', title: __('Tags'),formatter: Table.api.formatter.label,operate:false},
                        {field: 'content', title: __('Content'),operate:'like'},
                        {field: 'is_top', title: __('Is_top'),
                            searchList:{ "1": "置顶","2": "非置顶"},
                            formatter: Controller.api.formatter.topstr},
                        {field: 'is_tj', title: __('Is_tj'),
                            searchList:{ "1": "推荐","2": "非推荐"},
                            formatter: Controller.api.formatter.tjstr},
                        {field: 'praise', title: __('Praise'),operate:false},
                        {field: 'reads', title: __('Reads'),operate:false},
                        /*{field: 'awesome', title: __('Awesome')},
                        {field: 'collection', title: __('Collection')},*/
                        {field: 'share', title: __('Share'),operate:false},
                        /*{field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updated_at', title: __('Updated_at')},*/
                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('添加评论'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-pencil btn-dialog',
                                url: 'unitymanage/dynamiccomments/edits'
                            },
                                {
                                    name: 'detail',
                                    text: __('Detail'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'unitymanage/dynamics/detail'
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
                    var imgurl = row.audio;
                    if (imgurl){
                        var imgurlHead = imgurl.substring(0,8);
                        if (imgurlHead != '/upload/' && imgurlHead != 'http://4'){
                            imgurl = 'http://47.92.85.75/upload/'+imgurl;
                        }
                    }

                    return "<audio height='100px' width='100px' controls=''><source src='"+imgurl+"'></audio>";
                },*/
                /*video: function (value, row, index) {
                    var imgurl = row.video;
                    if (imgurl){
                        var imgurlHead = imgurl.substring(0,8);
                        if (imgurlHead != '/upload/' && imgurlHead != 'http://4'){
                            imgurl = 'http://47.92.85.75/upload/'+imgurl;
                        }
                    }

                    return "<video height='100px' width='100px' controls=''><source src='"+imgurl+"'></video>";
                },*/
                uid: function (value, row, index) {
                    //这里手动构造URL
                    url = "usersmanage/users?id=" + row.user_id;

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", value) + '">' + __('%s', value) + '</a>';
                },
                topstr: function (value, row, index) {
                    return row.is_top == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                tjstr: function (value, row, index) {
                    return row.is_tj == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
            },
        }
    };
    return Controller;
});