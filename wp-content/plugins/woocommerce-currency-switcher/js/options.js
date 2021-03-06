jQuery(function ($) {
    $('#woocs_is_multiple_allowed').change(function () {
        var woocs_is_multiple_allowed = parseInt($(this).val(), 10);
        var woocs_is_fixed_enabled = parseInt($('#woocs_is_fixed_enabled').val(), 10);
        //***
        if (woocs_is_multiple_allowed) {
            $('#woocs_is_fixed_enabled').parents('tr').show(200);
            $('#woocs_is_fixed_coupon').parents('tr').show(200);
            $('#woocs_is_fixed_shipping').parents('tr').show(200);
            if (woocs_is_fixed_enabled) {
                $('#woocs_force_pay_bygeoip_rules').parents('tr').show(200);
            }
        } else {
            $('#woocs_is_fixed_enabled').parents('tr').hide(200);
            $('#woocs_is_fixed_coupon').parents('tr').hide(200);
            $('#woocs_is_fixed_shipping').parents('tr').hide(200);
            $('#woocs_force_pay_bygeoip_rules').parents('tr').hide(200);
        }
    });


    $('#woocs_is_fixed_enabled').change(function () {
        var val = parseInt($(this).val(), 10);

        if (val) {
            if (!confirm("Native woocommerce price filter doesn't see data generated by this feature (for the time being) - its does not see fixed prices. Do you agree use this feature anyway?")) {
                val = 0;
                $(this).val(val);
            }
        }

        if (val) {
            $('#woocs_force_pay_bygeoip_rules').parents('tr').show(200);
        } else {
            $('#woocs_force_pay_bygeoip_rules').parents('tr').hide(200);
        }
    });


    $('#woocs_is_geoip_manipulation').change(function () {
        var val = parseInt($(this).val(), 10);

        if (val) {
            if (!confirm("Native woocommerce price filter doesn't see data generated by this feature (for the time being) - its does not see prices defined by individual geo ip rules. Do you agree use this feature anyway?")) {
                val = 0;
                $(this).val(val);
            }
        }

    });


});

