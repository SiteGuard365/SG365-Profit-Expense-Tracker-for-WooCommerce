(function($){
    function renderDashboard(payload){
        if(!payload){ return; }
        WCPI.simpleLineChart(document.getElementById('wcpiRevenueProfitChart'), payload.chart_labels, [
            {name:'Revenue', data: payload.chart_revenue},
            {name:'Profit', data: payload.chart_profit}
        ], ['#2271b1','#00a32a']);
        WCPI.simpleLineChart(document.getElementById('wcpiMarginChart'), payload.chart_labels, [
            {name:'Margin', data: payload.chart_margin}
        ], ['#826eb4']);
        WCPI.simpleLineChart(document.getElementById('wcpiSalesTrendChart'), payload.chart_labels, [
            {name:'Revenue', data: payload.chart_revenue}
        ], ['#3858e9']);
        WCPI.simpleDonut(document.getElementById('wcpiExpenseDonutChart'), payload.expense_breakdown);
        if($('#wcpi-top-products').length){ $('#wcpi-top-products').html(WCPI.renderTable(payload.top_products)); }
        if($('#wcpi-low-margin').length){ $('#wcpi-low-margin').html(WCPI.renderTable(payload.low_margin)); }
    }

    $(function(){
        if(window.wcpiDashboardBootstrap){
            renderDashboard(window.wcpiDashboardBootstrap);
        }

        $('#dashboard-apply').on('click', function(e){
            e.preventDefault();
            $.post(wcpiAdmin.ajaxUrl, {
                action: 'wcpi_dashboard_data',
                nonce: wcpiAdmin.nonce,
                preset: $('#dashboard-preset').val(),
                from: $('#dashboard-from').val(),
                to: $('#dashboard-to').val()
            }).done(function(response){
                if(response.success){
                    renderDashboard(response.data);
                }
            });
        });

        $('#wcpi-rebuild-trigger').on('click', function(e){
            e.preventDefault();
            $.post(wcpiAdmin.ajaxUrl, {
                action: 'wcpi_rebuild_summaries',
                nonce: wcpiAdmin.nonce,
                from: $('#dashboard-from').val(),
                to: $('#dashboard-to').val()
            }).done(function(response){
                alert(response.data.message || 'Done');
            });
        });
    });
})(jQuery);
