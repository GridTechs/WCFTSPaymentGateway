/*
 * Copyright (c) 2018, Ryo Currency Project
*/
function ftscoin_showNotification(message, type='success') {
    var toast = jQuery('<div class="' + type + '"><span>' + message + '</span></div>');
    jQuery('#ftscoin_toast').append(toast);
    toast.animate({ "right": "12px" }, "fast");
    setInterval(function() {
        toast.animate({ "right": "-400px" }, "fast", function() {
            toast.remove();
        });
    }, 2500)
}
function ftscoin_showQR(show=true) {
    jQuery('#ftscoin_qr_code_container').toggle(show);
}
function ftscoin_fetchDetails() {
    var data = {
        '_': jQuery.now(),
        'order_id': ftscoin_details.order_id
    };
    jQuery.get(ftscoin_ajax_url, data, function(response) {
        if (typeof response.error !== 'undefined') {
            console.log(response.error);
        } else {
            ftscoin_details = response;
            ftscoin_updateDetails();
        }
    });
}

function ftscoin_updateDetails() {

    var details = ftscoin_details;

    jQuery('#ftscoin_payment_messages').children().hide();
    switch(details.status) {
        case 'unpaid':
            jQuery('.ftscoin_payment_unpaid').show();
            jQuery('.ftscoin_payment_expire_time').html(details.order_expires);
            break;
        case 'partial':
            jQuery('.ftscoin_payment_partial').show();
            jQuery('.ftscoin_payment_expire_time').html(details.order_expires);
            break;
        case 'paid':
            jQuery('.ftscoin_payment_paid').show();
            jQuery('.ftscoin_confirm_time').html(details.time_to_confirm);
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'confirmed':
            jQuery('.ftscoin_payment_confirmed').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired':
            jQuery('.ftscoin_payment_expired').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired_partial':
            jQuery('.ftscoin_payment_expired_partial').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
    }

    jQuery('#ftscoin_exchange_rate').html('1 FTS = '+details.rate_formatted+' '+details.currency);
    jQuery('#ftscoin_total_amount').html(details.amount_total_formatted);
    jQuery('#ftscoin_total_paid').html(details.amount_paid_formatted);
    jQuery('#ftscoin_total_due').html(details.amount_due_formatted);

    jQuery('#ftscoin_integrated_address').html(details.integrated_address);

    if(ftscoin_show_qr) {
        var qr = jQuery('#ftscoin_qr_code').html('');
        new QRCode(qr.get(0), details.qrcode_uri);
    }

    if(details.txs.length) {
        jQuery('#ftscoin_tx_table').show();
        jQuery('#ftscoin_tx_none').hide();
        jQuery('#ftscoin_tx_table tbody').html('');
        for(var i=0; i < details.txs.length; i++) {
            var tx = details.txs[i];
            var height = tx.height == 0 ? 'N/A' : tx.height;
	    var explorer_url = ftscoin_explorer_url+'/transaction.html?hash='+tx.txid;
            var row = ''+
                '<tr>'+
                '<td style="word-break: break-all">'+
                '<a href="'+explorer_url+'" target="_blank">'+tx.txid+'</a>'+
                '</td>'+
                '<td>'+height+'</td>'+
                '<td>'+tx.amount_formatted+' FTS</td>'+
                '</tr>';

            jQuery('#ftscoin_tx_table tbody').append(row);
        }
    } else {
        jQuery('#ftscoin_tx_table').hide();
        jQuery('#ftscoin_tx_none').show();
    }

    // Show state change notifications
    var new_txs = details.txs;
    var old_txs = ftscoin_order_state.txs;
    if(new_txs.length != old_txs.length) {
        for(var i = 0; i < new_txs.length; i++) {
            var is_new_tx = true;
            for(var j = 0; j < old_txs.length; j++) {
                if(new_txs[i].txid == old_txs[j].txid && new_txs[i].amount == old_txs[j].amount) {
                    is_new_tx = false;
                    break;
                }
            }
            if(is_new_tx) {
                ftscoin_showNotification('Transaction received for '+new_txs[i].amount_formatted+' FTS');
            }
        }
    }

    if(details.status != ftscoin_order_state.status) {
        switch(details.status) {
            case 'paid':
                ftscoin_showNotification('Your order has been paid in full');
                break;
            case 'confirmed':
                ftscoin_showNotification('Your order has been confirmed');
                break;
            case 'expired':
            case 'expired_partial':
                ftscoin_showNotification('Your order has expired', 'error');
                break;
        }
    }

    ftscoin_order_state = {
        status: ftscoin_details.status,
        txs: ftscoin_details.txs
    };

}
jQuery(document).ready(function($) {
    if (typeof ftscoin_details !== 'undefined') {
        ftscoin_order_state = {
            status: ftscoin_details.status,
            txs: ftscoin_details.txs
        };
        setInterval(ftscoin_fetchDetails, 30000);
        ftscoin_updateDetails();
        new ClipboardJS('.clipboard').on('success', function(e) {
            e.clearSelection();
            if(e.trigger.disabled) return;
            switch(e.trigger.getAttribute('data-clipboard-target')) {
                case '#ftscoin_integrated_address':
                    ftscoin_showNotification('Copied destination address!');
                    break;
                case '#ftscoin_total_due':
                    ftscoin_showNotification('Copied total amount due!');
                    break;
            }
            e.clearSelection();
        });
    }
});
