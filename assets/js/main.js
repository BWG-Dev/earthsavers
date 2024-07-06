jQuery( function( $ ) {

    const es_subscription = {
        $signup_btn: $('#es_submit_service'),
        $invoice_info_btn: $('#send_invoice_info'),
        $btn_send_business_info: $('#btn_send_business_info'),
        $btn_send_business_selection: $('#btn_send_business_selection'),
        $btn_add_subaccount: $('#add_subaccount'),
        $btn_export_accounts: $('#export_accounts'),
        $input_user_search: $('#es_search_user'),
        $save_relationship: $('#es_save_relationship'),
        $es_approve_business: $('#es_approve_business'),
        $es_deny_business: $('#es_deny_business'),
        chart1:null,
        root1:null,

        init: function () {

            console.log('File started!')

            this.$signup_btn.on('click', this.addService);
            this.$invoice_info_btn.on('click', this.invoiceInfo);
            this.$btn_send_business_info.on('click', this.send_business_info);
            this.$btn_send_business_selection.on('click', this.send_business_selection);
            this.$btn_add_subaccount.on('click', this.addSubaccount);
            this.$btn_export_accounts.on('click', this.exportAccounts);
            $(document).on('click', '.remove-user-btn', this.removeAccount);
            $('#collect_inside_yes').attr('checked', true);

            this.$input_user_search.on('keyup', this.list_users);
            this.$save_relationship.on('click', this.save_relationship);
            this.$es_approve_business.on('click', this.approve_deny_business);
            this.$es_deny_business.on('click', this.approve_deny_business);

            $(document).on('click', function (e) {
                if ($(e.target).closest(".es_user_list").length === 0) {
                    $(".es_user_list").hide();
                }
            });

            $('.invoice_checkbox').on('change', this.save_payers);

           /* $('#chartdiv2 canvas').on('mouseover', function(e){

                setTimeout(function(){

                    console.log('XXYYYX');

                    $('.all_invoice').css('font-size', '45px !important');
                    $('#am5-html-container a').css('font-size', '45px !important');

                    console.log(e.target)
                }, 1000);
            })*/


            this.getLinealGraphData();
            this.getSeriesGraphData();

            $('.total-line').on('click', function() {
                if (es_subscription.root1.dom.id == 'chartdiv2') {
                    es_subscription.root1.dispose();
                }
                var oustanding = $(this).data('outstanding')
                var overdue = $(this).data('overdue')
                var count = $(this).data('count')
                es_subscription.renderOutstandingChart(oustanding, overdue, count)
            });
        },
        getLinealGraphData(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'lineal_graph_data',
                },
                dataType: "json",
                success: function (response) {
                    if(response.success){
                        es_subscription.renderOutstandingChart(response.outstanding, response.overdue, response.count);
                        $('.total_outstanding').html('$' + response.km);
                        $('.term_30').attr('data-outstanding', response.term_30).attr('data-overdue', response.overdue).attr('data-count', response.count_30).html('$' + response.term_30)
                        $('.term_3160').attr('data-outstanding', response.term_3160).attr('data-overdue', response.overdue).attr('data-count', response.count_3160).html('$' + response.term_3160)
                        $('.term_6190').attr('data-outstanding', response.term_6190).attr('data-overdue', response.overdue).attr('data-count', response.count_6190).html('$' + response.term_6190)
                        $('.term_90').attr('data-outstanding', response.term_91).attr('data-overdue', response.overdue).attr('data-count', response.count_91).html('$' + response.term_91)

                    }else{
                        alert('The chart could not be loaded!');
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            });

        },
        getSeriesGraphData(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'series_graph_data',
                },
                dataType: "json",
                success: function (response) {
                    if(response.success){
                        es_subscription.renderSeriesChart(response.data);

                    }else{
                        alert('The chart could not be loaded!');
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            });

        },
        renderOutstandingChart(outstanding, overdue, count){
            am5.ready(function() {
                es_subscription.root1 = am5.Root.new("chartdiv2");


                var myTheme = am5.Theme.new(es_subscription.root1);

                myTheme.rule("Grid", ["base"]).setAll({
                    strokeOpacity: 0.1
                });

                es_subscription.chart1 = es_subscription.root1.container.children.push(am5xy.XYChart.new(es_subscription.root1, {
                    panX: false,
                    panY: false,
                    wheelX: "panY",
                    wheelY: "zoomY",
                    layout: es_subscription.root1.verticalLayout
                }));

                es_subscription.chart1.get("colors").set("colors", [
                    am5.color(0xFC8D9E),
                    am5.color(0xFFD964),
                ]);


                var data = [{
                    "year": "",
                    "outstanding": outstanding,
                    "overdue": overdue,
                }]

                var yRenderer = am5xy.AxisRendererY.new(es_subscription.root1, {});
                var yAxis = es_subscription.chart1.yAxes.push(am5xy.CategoryAxis.new(es_subscription.root1, {
                    categoryField: "year",
                    renderer: yRenderer,
                    tooltip: am5.Tooltip.new(es_subscription.root1, {})
                }));

                yRenderer.grid.template.setAll({
                    location: 1
                })

                yAxis.data.setAll(data);

                var xAxis = es_subscription.chart1.xAxes.push(am5xy.ValueAxis.new(es_subscription.root1, {
                    min: 0,
                    renderer: am5xy.AxisRendererX.new(es_subscription.root1, {
                        strokeOpacity: 0.1
                    })
                }));

                var legend = es_subscription.chart1.children.push(am5.Legend.new(es_subscription.root1, {
                    centerX: am5.p50,
                    x: am5.p50
                }));


                function makeSeries(name, fieldName, outstanding, overdue, count) {

                    var tooltip = am5.Tooltip.new(es_subscription.root1, {
                        labelHTML: "<p>"+count+" Oustanding Invoice $"+outstanding+"</p>" +
                            "<a class='all_invoice' style='color: white' href='https://earthsavers.org/wp-admin/edit.php?post_status=wc-pending&post_type=shop_order'>View Invoices</a>",
                        cursorOverStyle: 'pointer'

                    });



                    if(name === 'Overdue'){
                        tooltip = am5.Tooltip.new(es_subscription.root1, {
                            labelHTML: "<p> Overdue invoice: $"+ overdue+ "</p>" +
                                "<a class='all_invoice' style='color: white' href='https://earthsavers.org/wp-admin/edit.php?s&post_status=all&post_type=shop_order&action=-1&m=0&overdue=_overdue_status&_customer_user&shop_order_subtype&filter_action=Filter&paged=1&action2=-1'>View Invoices</a>",
                            cursorOverStyle: 'pointer'
                        });
                    }

                    var series = es_subscription.chart1.series.push(am5xy.ColumnSeries.new(es_subscription.root1, {
                        name: name,
                        stacked: true,
                        xAxis: xAxis,
                        yAxis: yAxis,
                        baseAxis: yAxis,
                        valueXField: fieldName,
                        categoryYField: "year",
                        tooltip: tooltip,
                        keepTargetHover: true,
                        interactiveChildren: true,
                        keepTargetHover: true,
                    }));


                    series.columns.template.setAll({
                        tooltipText: "{name}: ${valueX}",
                        tooltipY: am5.percent(90),
                    });

                    if(name === 'Outstanding'){

                        series.columns.template.events.on("click", function(ev) {
                            console.log(ev.target.id)
                            $(ev.target).css('font-size', '25px !important');
                            window.location = "https://earthsavers.org/wp-admin/edit.php?post_status=wc-pending&post_type=shop_order";
                        });
                    }

                    if(name === 'Overdue'){

                        series.columns.template.events.on("click", function(ev) {
                            console.log(ev.target.id)
                            $(ev.target).css('font-size', '25px !important');
                            window.location = "https://earthsavers.org/wp-admin/edit.php?s&post_status=all&post_type=shop_order&action=-1&m=0&overdue=_overdue_status&_customer_user&shop_order_subtype&filter_action=Filter&paged=1&action2=-1";
                        });
                    }

                    series.data.setAll(data);

                    series.appear();

                    series.bullets.push(function() {
                        return am5.Bullet.new(es_subscription.root1, {
                            sprite: am5.Label.new(es_subscription.root1, {
                                text: "${valueX}",
                                fill: es_subscription.root1.interfaceColors.get("alternativeText"),
                                centerY: am5.p50,
                                centerX: am5.p50,
                                populateText: true
                            })
                        });
                    });

                    legend.data.push(series);
                }

                makeSeries("Outstanding", "outstanding", outstanding, overdue, count);
                makeSeries("Overdue", "overdue", outstanding, overdue);

                es_subscription.chart1.appear(1000, 100);

            });
        },
        renderSeriesChart: function(data){
            am5.ready(function() {
                var root = am5.Root.new("chartdiv");

                var chart = root.container.children.push(am5xy.XYChart.new(root, {
                    panX: true,
                    panY: true,
                    wheelX: "panX",
                    wheelY: "zoomX",
                    layout: root.verticalLayout,
                    pinchZoomX:true
                }));

                var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                    behavior: "none"
                }));
                cursor.lineY.set("visible", false);

                var colorSet = am5.ColorSet.new(root, {});


                var xRenderer = am5xy.AxisRendererX.new(root, {});
                xRenderer.grid.template.set("location", 0.5);
                xRenderer.labels.template.setAll({
                    location: 0.5,
                    multiLocation: 0.5
                });

                var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                    categoryField: "month",
                    renderer: xRenderer,
                    tooltip: am5.Tooltip.new(root, {})
                }));

                xAxis.data.setAll(data);

                var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                    maxPrecision: 0,
                    renderer: am5xy.AxisRendererY.new(root, {})
                }));

                var series = chart.series.push(am5xy.LineSeries.new(root, {
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "value",
                    categoryXField: "month",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "${valueY}",
                        dy:-5
                    })
                }));

                series.strokes.template.setAll({
                    templateField: "strokeSettings",
                    strokeWidth: 2
                });

                series.fills.template.setAll({
                    visible: true,
                    fillOpacity: 0.5,
                    templateField: "fillSettings"
                });


                series.bullets.push(function() {
                    return am5.Bullet.new(root, {
                        sprite: am5.Circle.new(root, {
                            templateField: "bulletSettings",
                            radius: 5
                        })
                    });
                });

                series.data.setAll(data);
                series.appear(1000);

               /* chart.set("scrollbarX", am5.Scrollbar.new(root, {
                    orientation: "horizontal",
                    marginBottom: 20
                }));*/

                chart.appear(1000, 100);

            }); // end am5.ready()
        },
        approve_deny_business: function(action){
            var user_id = $('#es_user_id').val();

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_approve_deny_business',
                    'action_account': $(this).data('action'),
                    'user_id': user_id,
                },
                dataType: "json",
                success: function (response) {

                    if(response.success){
                        location.reload();
                    }else{
                        alert(response.msg);
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        save_relationship: function(){
            var note = $('#es_customer_relationship').val();
            var user_id = $('#es_user_id').val();

             $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_save_relationship',
                    'note': note,
                    'user_id': user_id,
                },
                dataType: "json",
                success: function (response) {

                    if(response.success){
                        location.reload();
                    }else{
                        alert(response.msg);
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        exportAccounts: function(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_export_users_csv',
                },
                dataType: "json",
                success: function (response) {

                    if(response.success){
                        window.location = response.path;
                    }else{
                        alert(response.msg);
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        removeAccount: function(){
            var user_id = $(this).data('id');

            $btn = $(this);

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_remove_subaccount',
                    'user_id' : user_id,
                },
                dataType: "json",
                beforeSend: function(){
                    $btn.text("Removing...");
                    $btn.attr('disabled', 'disabled');
                },
                success: function (response) {

                    if(response.success){
                         window.location = '/my-account/my-accounts';
                    }else{
                        alert(response.msg);
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        addSubaccount: function(){
            var name = $('#account_name').val();
            var email = $('#account_email').val();
            var user_id = $('#user_id').val();
            var phone = $('#account_phone').val();



            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_add_subaccount',
                    'user_id' : user_id,
                    'name': name,
                    'email': email,
                    'phone': phone
                },
                dataType: "json",
                beforeSend: function(){
                    $("#modal-loading-mask").css('display', 'flex');
                },
                complete: function(){
                    $("#modal-loading-mask").css('display', 'none');
                },
                success: function (response) {
                    if(response.success){
                        $('#addAccount').modal('toggle');
                        window.location = '/my-account/my-accounts';
                    }else{
                        alert(response.msg);
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        addService: function(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_add_service',
                    'id' : $("input.service_choice:checked").val(),
                    'interval': $("input.interval_choice:checked").val()
                },
                dataType: "json",
                beforeSend: function () {},
                complete: function () {},
                success: function (response) {

                    if(response.success){
                        window.location = '/checkout';
                    }else{
                        alert("There was an error, please try again!");
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        invoiceInfo: function(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'invoice_info',
                    'stop' : $("#do_not_send_invoice_emails").is(':checked'),
                    'user_id' : $("#user_id").val(),
                },
                dataType: "json",
                beforeSend: function () {},
                complete: function () {},
                success: function (response) {

                    if(response.success){
                        toastr.info('Saved!');
                    }else{
                        alert("There was an error, please try again!");
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        save_payers: function(){

            var payers = [];

            $.each( $('.invoice_checkbox'), function(index, check){
                if( $(check).is(':checked') ){
                    payers.push($(check).data('email'));
                }
            });

            console.log('Save Payer!')

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'save_payers',
                    'payers' : payers,
                    'user_id' : $("#user_id").val(),
                },
                dataType: "json",
                beforeSend: function () {
                    $('.mask-content').css('display', 'flex');
                },
                complete: function () {
                    $('.mask-content').css('display','none');
                },
                success: function (response) {

                    if(response.success){
                        toastr.info(response.msg);
                    }else{
                        alert("There was an error, please try again!");
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        send_business_info: function(e){

            e.preventDefault();

            var es_find = [];
            $("input[name='es_find']:checked").each(function(){
                    es_find.push($(this).val());
            })

            var es_items = [];
            $("input[name='es_items']:checked").each(function(){
                    es_items.push($(this).val());
            })

            console.log(es_items.join(", "), es_find.join(", "));

            if(es_subscription.check_form_fields(e)){
                $.ajax( {
                    type: 'POST',
                    url:  parameters.ajax_url,
                    data:{
                        'action':'es_add_business',
                        'first' : $('#es_first').val(),
                        'last' : $('#es_last').val(),
                        'company' : $('#es_company').val(),
                        'business_type' : $('#es_business_type').val(),
                        'email' : $('#es_email').val(),
                        'phone' : $('#es_phone').val(),
                        'address' : $('#es_address').val(),
                        'es_number_of_employees' : $('#es_number_of_employees').val(),
                        'es_referred' : $('#es_referred').val(),
                        'description' : $('#es_description').val(),
                        'es_find' : es_find.join(", "),
                        'es_items' : es_items.join(", "),

                    },
                    dataType: "json",
                    beforeSend: function () {
                        $('#es_business_form .mask').css('display','flex');
                    },
                    complete: function () {
                        $('#es_business_form .mask').css('display','none');
                    },
                    success: function (response) {
                        if(response.success){
                            $('#es_business_form').empty();
                            $('#es_business_form').append('<h3>Your request was sent, we will contact you soon</h3>');

                        }else{
                            toastr.warning(response.msg);
                        }
                    },
                    error : function(jqXHR, exception){
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = 'Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = 'Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = 'Time out error.';
                        } else if (exception === 'abort') {
                            msg = 'Ajax request aborted.';
                        } else {
                            msg = 'Uncaught Error.\n' + jqXHR.responseText;
                        }
                        console.log(msg);
                    }

                } );
            }

        },
        send_business_selection(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_business_selection',
                    'collect_inside' : $('#collect_inside_yes').is(":checked") ? 'yes' : 'no'
                },
                dataType: "json",
                beforeSend: function () {
                    //$('#es_business_form .mask').css('display','flex');
                },
                complete: function () {
                    //$('#es_business_form .mask').css('display','none');
                },
                success: function (response) {
                    if(response.success){
                        window.location = '/checkout';

                    }else{
                        toastr.warning(response.msg);
                    }
                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        check_form_fields: function(e){
            var $first = $('#es_first');
            var $last = $('#es_last');
            var $company = $('#es_company');
            var $business_type = $('#es_business_type');
            var $email = $('#es_email');
            var $phone = $('#es_phone');
            var $address = $('#es_address');
            var $number_of_employees = $('#es_number_of_employees');
            var $es_referred = $('#es_referred');
            var $description = $('#es_description');
            var $terms = $('#es_terms');

            console.log($terms.prop('checked'))

            if($first.val() === '' || $last.val() === ''){
                toastr.warning('Check your name please');
                return false;
            }

            var filter_email = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if($email.val() === '' || !filter_email.test($email.val())){
                toastr.warning('Check your name email please');
                return false;
            }

            var filter_phone = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;
            if($phone.val() === '' || !filter_phone.test($phone.val())){
                toastr.warning('Check your phone please');
                return false;
            }

            if(! $terms.prop('checked')){
                toastr.warning('You need to check the terms to proceed');
                return false;
            }

            if(! $number_of_employees.val()){
                toastr.warning('Enter your number of employee');
                return false;
            }

            if(! $address.val()){
                toastr.warning('Check your address please');
                return false;
            }

            if(! $description.val()){
                toastr.warning('Enter a description please');
                return false;
            }

            /*if(!es_subscription.pristine.pristine.validate()){
                toastr.warning('Please check the field, you have some errors');
                return false;
            }*/

            return true;

        },
        list_users: function(){

            if (window.xhr) { // if any previous ajaxRequest is running, abort
                window.xhr.abort();
            }



            window.xhr = $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_list_users',
                    'search': $('#es_search_user').val()
                },
                dataType: "json",
                beforeSend: function () {
                    $('.es_user_list').empty().show();
                    $('.es_user_list').empty();
                    $('.es_user_list').append('<li id="es-no-item-loading">Loading...</li>');
                },
                complete: function () {
                    $('#es-no-item-loading').remove();
                },
                success: function (response) {

                    if(response.success){
                        var users = response.users;
                        $('.es_user_list').empty().show();


                        for(var i = 0; i< users.length; i++){
                            var user = users[i];
                            var company_output = '';
                            if(user.es_company){
                                company_output = '' + user.es_company + ' - ';
                            }
                            $('.es_user_list').append('<li><a href="/wp-admin/admin.php?page=es-users&user_id=' + user.ID + '">' + company_output + user.display_name + ' (' + user.user_email + ') - '+ user.es_address +'<a></li>');
                        }
                    }else{
                        alert(response.msg);
                    }

                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
    }


    $(window).ready(function(){

        es_subscription.init();

    });
});
