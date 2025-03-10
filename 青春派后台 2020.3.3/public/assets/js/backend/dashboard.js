define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'example/customsearch/index',
                    add_url: 'example/customsearch/add',
                    edit_url: 'example/customsearch/edit',
                    del_url: 'example/customsearch/del',
                    multi_url: 'example/customsearch/multi',
                    table: '',
                }
            });

            var table = $("#myTabContent");
            //searchFormVisible: true,
            //searchFormTemplate: 'customformtpl',
            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title : {
                    text: '渠道来源比例图',
                    subtext: '',
                    x:'center'
                },
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    //data: Orderdata.column
                    data: Orderdata.createdata
                },
                series : [
                    {
                        name: '用户来源',
                        type: 'pie',
                        radius : '55%',
                        center: ['50%', '45%'],
                        data:Orderdata.paydata,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);

            //动态添加数据，可以通过Ajax获取数据然后填充
            // setInterval(function () {
            //     Orderdata.column.push((new Date()).toLocaleTimeString().replace(/^\D*/, ''));
            //     var amount = Math.floor(Math.random() * 200) + 20;
            //     Orderdata.createdata.push(amount);
            //     Orderdata.paydata.push(Math.floor(Math.random() * amount) + 1);

            //     //按自己需求可以取消这个限制
            //     if (Orderdata.column.length >= 20) {
            //         //移除最开始的一条数据
            //         Orderdata.column.shift();
            //         Orderdata.paydata.shift();
            //         Orderdata.createdata.shift();
            //     }
            //     myChart.setOption({
            //         xAxis: {
            //             data: Orderdata.column
            //         },
            //         series: [{
            //             name: __('Sales'),
            //             data: Orderdata.paydata
            //         },
            //             {
            //                 name: __('Orders'),
            //                 data: Orderdata.createdata
            //             }]
            //     });
            //     if ($("#echart").width() != $("#echart canvas").width() && $("#echart canvas").width() < $("#echart").width()) {
            //         myChart.resize();
            //     }
            // }, 2000);
            $(window).resize(function () {
                myChart.resize();
            });


            var myCharts = Echarts.init(document.getElementById('echarts'), 'walden');

            // 指定图表的配置项和数据
            var options = {
                title: {
                    text: '每日用户统计折线图',
                    subtext: '',
                    // x:'center',
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data:['注册数','活跃数']
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                toolbox: {
                    feature: {
                        saveAsImage: {}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.days
                },
                yAxis: {
                    type: 'value'
                },
                series: [
                    {
                        name:'注册数',
                        type:'line',
                        stack: '总量',
                        data:Orderdata.register
                    },
                    {
                        name:'活跃数',
                        type:'line',
                        stack: '总量',
                        data:Orderdata.active
                    },
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myCharts.setOption(options);

            //动态添加数据，可以通过Ajax获取数据然后填充
            // setInterval(function () {
            //     Orderdata.column.push((new Date()).toLocaleTimeString().replace(/^\D*/, ''));
            //     var amount = Math.floor(Math.random() * 200) + 20;
            //     Orderdata.createdata.push(amount);
            //     Orderdata.paydata.push(Math.floor(Math.random() * amount) + 1);

            //     //按自己需求可以取消这个限制
            //     if (Orderdata.column.length >= 20) {
            //         //移除最开始的一条数据
            //         Orderdata.column.shift();
            //         Orderdata.paydata.shift();
            //         Orderdata.createdata.shift();
            //     }
            //     myCharts.setOption({
            //         xAxis: {
            //             data: Orderdata.column
            //         },
            //         series: [{
            //             name: __('Sales'),
            //             data: Orderdata.paydata
            //         },
            //             {
            //                 name: __('Orders'),
            //                 data: Orderdata.createdata
            //             }]
            //     });
            //     if ($("#echarts").width() != $("#echarts canvas").width() && $("#echarts canvas").width() < $("#echarts").width()) {
            //         myCharts.resize();
            //     }
            // }, 2000);
            $(window).resize(function () {
                myCharts.resize();
            });
            //用户资金统计折线图
            var myChartsMoney = Echarts.init(document.getElementById('echarts_money'), 'walden');
            var options_money = {
                title: {
                    text: '每日资金统计折线图',
                    subtext: '',
                    // x:'center',
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data:['充值金额','提现金额']
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                toolbox: {
                    feature: {
                        saveAsImage: {}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.days
                },
                yAxis: {
                    type: 'value'
                },
                series: [
                    {
                        name:'充值金额',
                        type:'line',
                        stack: '总量',
                        data:Orderdata.recharge
                    },
                    {
                        name:'提现金额',
                        type:'line',
                        stack: '总量',
                        data:Orderdata.tixian
                    },
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChartsMoney.setOption(options_money);
            


























            $(window).resize(function () {
                myChart.resize();
            });























            $(document).on("click", ".btn-checkversion", function () {
                top.window.$("[data-toggle=checkupdate]").trigger("click");
            });


        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };

    return Controller;
});