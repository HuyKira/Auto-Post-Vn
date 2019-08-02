jQuery(document).ready(function () {
    var html = '<div class="form-group">';
    html += '<label for="link">Nhập link bài viết</label>';
    html += '<input required="required" name="link[]" type="url" class="form-control" id="" placeholder="Nhập link vào đây">';
    html += '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
    html += '</div>';
    jQuery('.click-more').click(function () {
        jQuery('.list-input').append(html);
    });
    jQuery('form').on('click', '.glyphicon-remove', function (event) {
        jQuery(this).parent('.form-group').remove();
    });
    var html2 = '<div class="col-xs-12 col-sm-12 col-md-6"><div class="input-hk form-group"><input type="text" class="form-control" name="add_menu_hk[list-op][]" value=""><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div></div>';
    jQuery('.click-add').click(function () {
        jQuery('.list-hihi').append(html2);
    });

    jQuery('#rss_source').on('change', function () {
        var source = jQuery(this).val();
        jQuery.ajax({
            type: 'POST',
            data: {action: 'gnv_dropdown_rss_link', source: source},
            url: ajaxurl,
            success: function (html) {
                jQuery('#rss_link').html(html);
            }
        })
    });

    jQuery('#rss_process').on('click', function () {
        jQuery('#process-done').addClass('hidden');
        jQuery('#current_scan').html(0);
        jQuery('#rss_process_status').addClass('hidden');
        jQuery(this).attr('disabled', 'disabled');
        var rss_link = jQuery('#rss_link').val();
        var rss_cat = jQuery('#rss_cat').val();
        var rss_status = jQuery('#rss_status').val();
        if (!rss_link || !rss_cat || !rss_status) {
            jQuery(this).removeAttr('disabled');
            return true;
        }
        jQuery.ajax({
            type: 'POST',
            data: {
                action: 'gnv_rss_process',
                rss_link: rss_link,
            },
            url: ajaxurl,
            dataType: 'json',
            success: function (resp) {
                jQuery('#rss_process_status').removeClass('hidden');
                jQuery('#total_scan').html(resp.total);
                jQuery('#total_rss').html(resp.total_rss);
                jQuery('#rejected').html(resp.rejected);
                if (resp.total_rss == resp.rejected) {
                    //stop
                    jQuery('#process-done').removeClass('hidden');
                    jQuery('#rss_process').removeAttr('disabled');
                    return;
                }
                getNewsCallback(0, resp.links, rss_cat, rss_status,function(resp2){
                    var current_scan = parseInt(jQuery('#current_scan').html()) + 1;
                    jQuery('#current_scan').html(parseInt(current_scan));
                    if(current_scan == resp.total) {
                        jQuery('#process-done').removeClass('hidden');
                        jQuery('#rss_process').removeAttr('disabled');
                        return;
                    }
                })
            }
        })
    });

    function getNewsCallback(index, links, cat, status, callback) {
        if (index > links.length - 1) {
            console.log('done');
            return;
        }
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'gnv_news_process',
                cat: cat,
                status: status,
                link: links[index]
            },
            dataType: 'json',
            success: function (resp) {
                if (callback)
                    callback(resp);
                index++;
                setTimeout(function(){
                    getNewsCallback(index, links, cat, status, callback);
                }, 2000);
            }
        })
    }
});