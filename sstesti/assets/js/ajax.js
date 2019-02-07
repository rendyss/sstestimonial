jQuery(function () {

    //Form submit testimonial clicked
    jQuery('body').on('click', '.btnsend', function (e) {
        var btn = jQuery(this),
            btnCaption = btn.html(),
            frm = jQuery(this).closest('form'),
            parentform = frm.closest('.parentform'),
            notif = parentform.find('.ntf'),
            dataObj = jQuery(frm).serializeArray(),
            inputs = jQuery(frm).find('input[type=text], input[type=email], textarea');

        notif.html('');
        inputs.prop('disabled', true);
        btn.prop("disabled", true).html("Loading...");
        var ajaxSubmittestimonial = jQuery.ajax({
            url: my_ajax_object.ajax_url,
            method: 'POST',
            data: {
                'action': 'sstestimonials',
                'data': dataObj
            },
            // contentType: "application/json; charset=utf-8",
            dataType: "json"
        })

        ajaxSubmittestimonial.done(function (data) {
            inputs.prop("disabled", false);
            if (!data.is_error) {
                inputs.val('');
            }
            notif.html("<strong>" + data.message + "</strong>");
            btn.prop("disabled", false).html(btnCaption);
        })

        ajaxSubmittestimonial.fail(function (data) {
            inputs.prop("disabled", false);
        })
    });
})