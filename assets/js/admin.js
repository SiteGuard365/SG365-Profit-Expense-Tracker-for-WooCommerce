window.WCPI = window.WCPI || {};
(function($){
    WCPI.renderTable = function(rows){
        var html = '<table class="widefat striped wcpi-table"><thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Revenue</th><th>Cost</th><th>Profit</th><th>Margin %</th></tr></thead><tbody>';
        if(!rows || !rows.length){
            html += '<tr><td colspan="7">No data available.</td></tr>';
        }else{
            rows.forEach(function(row){
                var profitClass = parseFloat(row.profit) >= 0 ? 'wcpi-positive' : 'wcpi-negative';
                html += '<tr>'+
                    '<td>'+(row.name||'')+'</td>'+
                    '<td>'+(row.sku||'')+'</td>'+
                    '<td>'+(row.quantity_sold||0)+'</td>'+
                    '<td>'+(row.revenue||0)+'</td>'+
                    '<td>'+(row.cost||0)+'</td>'+
                    '<td class="'+profitClass+'">'+(row.profit||0)+'</td>'+
                    '<td>'+(parseFloat(row.margin_percent||0).toFixed(2))+'%</td>'+
                '</tr>';
            });
        }
        html += '</tbody></table>';
        return html;
    };

    WCPI.renderSummaryTable = function(rows){
        var html = '<table class="widefat striped wcpi-table"><thead><tr><th>Date</th><th>Orders</th><th>Items</th><th>Revenue</th><th>Expenses</th><th>Net Profit</th><th>Margin %</th></tr></thead><tbody>';
        if(!rows || !rows.length){
            html += '<tr><td colspan="7">No summary rows available.</td></tr>';
        }else{
            rows.forEach(function(row){
                html += '<tr>'+
                    '<td>'+row.summary_date+'</td>'+
                    '<td>'+row.orders_count+'</td>'+
                    '<td>'+row.items_sold+'</td>'+
                    '<td>'+row.gross_revenue+'</td>'+
                    '<td>'+row.expenses_total+'</td>'+
                    '<td>'+(row.net_profit)+'</td>'+
                    '<td>'+parseFloat(row.margin_percent||0).toFixed(2)+'%</td>'+
                '</tr>';
            });
        }
        html += '</tbody></table>';
        return html;
    };

    WCPI.simpleLineChart = function(canvas, labels, series, colors){
        if(!canvas){ return; }
        var ctx = canvas.getContext('2d');
        var w = canvas.width = canvas.offsetWidth;
        var h = canvas.height = canvas.offsetHeight || 320;
        ctx.clearRect(0,0,w,h);
        ctx.fillStyle = '#fff';
        ctx.fillRect(0,0,w,h);
        var padding = {top:20,right:20,bottom:30,left:40};
        var allVals = [];
        series.forEach(function(s){ allVals = allVals.concat(s.data || []); });
        var max = Math.max.apply(null, allVals.concat([1]));
        var min = Math.min.apply(null, allVals.concat([0]));
        if(max === min){ max += 1; min -= 1; }

        ctx.strokeStyle = '#e2e4e7';
        ctx.beginPath();
        ctx.moveTo(padding.left, padding.top);
        ctx.lineTo(padding.left, h - padding.bottom);
        ctx.lineTo(w - padding.right, h - padding.bottom);
        ctx.stroke();

        function x(i){ return padding.left + ((w - padding.left - padding.right) * i / Math.max(1, labels.length - 1)); }
        function y(v){ return h - padding.bottom - ((v - min)/(max - min)) * (h - padding.top - padding.bottom); }

        series.forEach(function(s, idx){
            ctx.strokeStyle = colors[idx] || '#2271b1';
            ctx.lineWidth = 2;
            ctx.beginPath();
            (s.data || []).forEach(function(v,i){
                if(i === 0){ ctx.moveTo(x(i), y(parseFloat(v))); } else { ctx.lineTo(x(i), y(parseFloat(v))); }
            });
            ctx.stroke();
        });

        ctx.fillStyle = '#646970';
        ctx.font = '11px sans-serif';
        if(labels.length){
            ctx.fillText(labels[0], padding.left, h - 10);
            ctx.fillText(labels[labels.length-1], w - padding.right - 60, h - 10);
        }
    };

    WCPI.simpleDonut = function(canvas, rows){
        if(!canvas){ return; }
        var ctx = canvas.getContext('2d');
        var w = canvas.width = canvas.offsetWidth;
        var h = canvas.height = canvas.offsetHeight || 320;
        var cx = w/2, cy = h/2, r = Math.min(w,h)/3;
        ctx.clearRect(0,0,w,h);
        var total = 0;
        (rows||[]).forEach(function(row){ total += parseFloat(row.total||0); });
        if(total <= 0){
            ctx.fillStyle = '#646970';
            ctx.font = '14px sans-serif';
            ctx.fillText('No expense data', 20, 30);
            return;
        }
        var colors = ['#2271b1','#72aee6','#8c8f94','#3858e9','#00a32a','#d63638','#f0b849','#4f94d4','#826eb4'];
        var start = -Math.PI/2;
        rows.forEach(function(row,i){
            var slice = (parseFloat(row.total||0)/total) * Math.PI * 2;
            ctx.beginPath();
            ctx.strokeStyle = colors[i % colors.length];
            ctx.lineWidth = r/2;
            ctx.arc(cx, cy, r, start, start + slice);
            ctx.stroke();
            start += slice;
        });
    };
})(jQuery);
