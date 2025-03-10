define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'roomsmanage/rooms/index' + location.search,
                    //add_url: 'roomsmanage/rooms/add',
                    edit_url: 'roomsmanage/rooms/edit',
                    //del_url: 'roomsmanage/rooms/del',
                    multi_url: 'roomsmanage/rooms/multi',
                    table: 'rooms',
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
                        {field: 'room_status', title: __('Room_status'),
                            searchList:{ "1": "正常","2": "上锁","3": "封禁","4": "关闭"},
                            formatter: Controller.api.formatter.status},
                        {field: 'numid', title: __('Numid')},
                        {field: 'users.nickname', title: __('Uid'), formatter: Controller.api.formatter.uid,operate:false},
                        {field: 'roomVisitor', title: __('房内人员'), visible:false, operate: 'like'},
                        {field: 'room_name', title: __('Room_name'),operate: 'like'},
                        {field: 'room_cover', title: __('Room_cover'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'room_intro', title: __('Room_intro'),visible: false, operate: false},
                        {field: 'room_pass', title: __('Room_pass'),visible: false, operate: false},
                        {field: 'category.name', title: __('Room_type'), formatter: Controller.api.formatter.gettypes},
                        {field: 'room_welcome', title: __('Room_welcome'),visible: false, operate: false},
                        {field: 'freshTime', title: __('Freshtime'),visible: false, operate: false},
                        {field: 'updated_at', title: __('Updated_at'),visible: false, operate: false},
                         {field: 'is_tj', title: __('是否推荐'),
                            searchList:{ "1": "是","2": "否"},
                            formatter: Controller.api.formatter.is_tj},
                        {field: 'week_star', title: __('Week_star'),
                            searchList:{ "1": "是","2": "否"},
                            formatter: Controller.api.formatter.getStar},

                        {field: 'is_popular', title: __('Is_popular'),
                            searchList:{ "1": "是","2": "否"},
                            formatter: Controller.api.formatter.getPop},
                        {field: 'secret_chat', title: __('Secret_chat'),
                            searchList:{ "1": "是","2": "否"},
                            formatter: Controller.api.formatter.getSecret},
                        {field: 'openid', title: __('Openid'),visible: false, operate: false},
                        {field: 'room_background', title: __('Room_background'),visible: false, operate: false},
                        {field: 'is_top', title: __('Is_top'),
                            searchList:{ "1": "是","2": "否"},
                            formatter: Controller.api.formatter.getTop},
                        //{field: 'ranking', title: __('Ranking'),operate: false,formatter: Controller.api.formatter.getRanking},
                        {field: 'microphone', title: __('Microphone'),visible: false, operate: false},
                        {field: 'is_prohibit_sound', title: __('Is_prohibit_sound'),visible: false, operate: false},
                        /*{field: 'commission_proportion', title: __('Commission_proportion'),visible: false, operate: false},*/
                        //{field: 'super_uid', title: __('Super_uid'),operate: false,formatter: Controller.api.formatter.getSuper},
                        {field: 'is_afk', title: __('Is_afk'),visible: false, operate: false},
                        {field: 'hot', title: __('Hot'),visible: false, operate: false},
                        {field: 'created_at', title: __('Created_at'),operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'roomsmanage/rooms/detail'
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
                $(document).on("change", "#c-room_class", function () {
                    $("#c-room_type option[data-type='all']").prop("selected", true);
                    $("#c-room_type option").removeClass("hide");
                    $("#c-room_type option[data-type!='" + $(this).val() + "'][data-type!='all']").addClass("hide");
                    $("#c-room_type").data("selectpicker") && $("#c-room_type").selectpicker("refresh");
                });
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {//渲染的方法
                status:function (value, row, index) {
                    var returnHtml = '';
                    if (row.room_status == 1){
                        returnHtml = "<span class='text-green'>正常</span>";
                    }else if (row.room_status == 2){
                        returnHtml = "<span class='text-blue'>上锁</span>";
                    }else if (row.room_status == 3){
                        returnHtml = "<span class='text-red'>封禁</span>";
                    }else if (row.room_status == 4){
                        returnHtml = "<span class='text-gray'>关闭</span>";
                    }
                    return returnHtml;
                },
                uid: function (value, row, index) {
                    //这里手动构造URL
                    var url = "usersmanage/users?id=" + row.uid;
                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    return '<a href="' + url + '" class="label label-success addtabsit" title="' + __("Search %s", row.uid) + '">' + __('%s', value) + '</a>';
                },
                getStar: function (value, row, index) {
                    return row.week_star == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                is_tj: function (value, row, index) {
                    return row.is_tj == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                getPop: function (value, row, index) {
                    return row.is_popular == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                getSecret: function (value, row, index) {
                    return row.secret_chat == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                getTop: function (value, row, index) {
                    return row.is_top == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                getSuper: function (value, row, index) {
                    return row.super_uid == 1 ? "<span class='label bg-red'>是</span>" : "<span class='label bg-gray'>否</span>";
                },
                getRanking: function (value, row, index) {
                    return value > 0 ? value : "<span class='text-red'>不参与</span>";
                },
                gettypes: function (value, row, index) {
                    return value;
                    //return  row.categorys.name+"<span class= 'text-red'>[</span>"+value+"<span class= 'text-red'>]</span>";
                },

            },
        }
    };
    return Controller;
});