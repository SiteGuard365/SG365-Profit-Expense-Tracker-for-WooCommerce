(function($){
    function loadReports(){
        if(!$('#wcpi-reports-page').length){ return; }
        $('#wcpi-report-top-products, #wcpi-report-low-margin, #wcpi-report-high-revenue, #wcpi-report-most-sold, #wcpi-report-summary').html('<p>'+wcpiAdmin.i18n.loading+'</p>');
        $.post(wcpiAdmin.ajaxUrl, {
            action: 'wcpi_reports_data',
            nonce: wcpiAdmin.nonce,
            preset: $('#reports-preset').val(),
            from: $('#reports-from').val(),
            to: $('#reports-to').val()
        }).done(function(response){
            if(!response.success){ return; }
            $('#wcpi-report-top-products').html(WCPI.renderTable(response.data.top_products));
            $('#wcpi-report-low-margin').html(WCPI.renderTable(response.data.low_margin));
            $('#wcpi-report-high-revenue').html(WCPI.renderTable(response.data.high_revenue));
            $('#wcpi-report-most-sold').html(WCPI.renderTable(response.data.most_sold));
            $('#wcpi-report-summary').html(WCPI.renderSummaryTable(response.data.summary_rows));
        }).fail(function(){
            $('#wcpi-report-summary').html('<p>'+wcpiAdmin.i18n.error+'</p>');
        });
    }

    $(function(){
        $('#reports-apply').on('click', function(e){
            e.preventDefault();
            loadReports();
        });
        if($('#wcpi-reports-page').length){
            loadReports();
        }
    });
})(jQuery);
