<?php
$cart_item = $args['cart_item'];
$cart_item_key = $args['cart_item_key'];

$variation_id = $cart_item['variation_id'];
$lightbox_link = "edit-variation-" . $variation_id;
$return = "
[button size='xxsmall' text='Edit Type/Color/Size' link='#$lightbox_link']
[lightbox id='$lightbox_link' width='600px' padding='20px']
<label>Change Product Type</label>
<select class='cart-item-type-dropdown' data-variation-id='" . $cart_item['variation_id'] . "' data-cart-item-key='" . $cart_item_key . "'>
</select>
<label>Change Product Color</label>
<select class='cart-item-color-dropdown' data-variation-id='" . $cart_item['variation_id'] . "' data-cart-item-key='" . $cart_item_key . "'>
</select>
<label>Change Product Size</label>
<select class='cart-item-size-dropdown' data-variation-id='" . $cart_item['variation_id'] . "' data-cart-item-key='" . $cart_item_key . "'>
</select>
<div class='mkdv-cart-item-error-message' style='color: red;'></div>
[/lightbox]
";
echo do_shortcode($return);



?>
<script>
    var mkdvAllVariations = [];

    if (typeof mkdvLoadVariations != 'function') {
        function mkdvLoadVariations() {
            jQuery.ajax({
                url: "/wp-json/mkdv/feeds/v1/customizer_page",
                // data: dataSent,
                cache: false,
                async: true,
                type: "GET",
                success: function(response) {
                    mkdvAllVariations = response.actualVariations;
                    console.log("Response.actualVariations ---> ", response.actualVariations);

                    jQuery(".cart-item-type-dropdown").each(function(index) {
                        let variationID = jQuery(this).data("variation-id");

                        let currentVariation = {};
                        for (let p = 0; p < mkdvAllVariations.length; p++) {
                            if (mkdvAllVariations[p].value === variationID) {
                                currentVariation = mkdvAllVariations[p]
                            }
                        }
                        let filteredVariations = getFilteredVariationsForProductTypes();
                        for (let p = 0; p < filteredVariations.length; p++) {
                            jQuery(this).append(`<option value="${filteredVariations[p]['data-type']}">${filteredVariations[p]['data-typeName']}</option>`);
                        }
                        jQuery(this).val(currentVariation['data-type']);
                    });
                    jQuery(".cart-item-color-dropdown").each(function(index) {
                        let variationID = jQuery(this).data("variation-id");
                        let currentVariation = {};
                        for (let p = 0; p < mkdvAllVariations.length; p++) {
                            if (mkdvAllVariations[p].value === variationID) {
                                currentVariation = mkdvAllVariations[p]
                            }
                        }
                        let filteredVariations = getFilteredVariationsForProductColors(currentVariation['data-type']);
                        for (let p = 0; p < filteredVariations.length; p++) {
                            jQuery(this).append(`<option value="${filteredVariations[p]['data-color']}">${filteredVariations[p]['data-colorName']}</option>`);
                        }
                        jQuery(this).val(currentVariation['data-color']);

                    });
                    jQuery(".cart-item-size-dropdown").each(function(index) {
                        let variationID = jQuery(this).data("variation-id");

                        let currentVariation = {};
                        for (let p = 0; p < mkdvAllVariations.length; p++) {
                            if (mkdvAllVariations[p].value === variationID) {
                                currentVariation = mkdvAllVariations[p];
                            }
                        }

                        let filteredVariations = getFilteredVariationsForProductSizes(currentVariation['data-type'], currentVariation['data-color']);

                        for (let p = 0; p < filteredVariations.length; p++) {
                            jQuery(this).append(`<option value="${filteredVariations[p].value}">${filteredVariations[p]['data-sizeName']}</option>`);
                        }
                        jQuery(this).val(variationID);
                    });
                },
                error: function(xhr) {
                    console.log("ERROR: ---> ", xhr)
                }
            });
        }
    }


    jQuery(".cart-item-type-dropdown").on('change', function() {
        let cartItemKey = jQuery(this).data("cart-item-key");

        let currentProductColorDropdown = jQuery(`.cart-item-color-dropdown[data-cart-item-key='${cartItemKey}']`);
        let currentProductSizeDropdown = jQuery(`.cart-item-size-dropdown[data-cart-item-key='${cartItemKey}']`);
        let currentProductType = jQuery(`.cart-item-type-dropdown[data-cart-item-key='${cartItemKey}']`).val();
        currentProductColorDropdown.find('option').remove();
        let filteredVariations = getFilteredVariationsForProductColors(currentProductType);
        if (filteredVariations.length === 0) {
            let currentProductTypeName = makeFirstLetterOfEachWordCapital(currentProductType);
            jQuery(".mkdv-cart-item-error-message").text(`Sorry, ${currentProductTypeName} is not available currently. Please change your Product Type`);
        }
        for (let p = 0; p < filteredVariations.length; p++) {
            jQuery(".mkdv-cart-item-error-message").text(``);
            currentProductColorDropdown.append(`<option value="${filteredVariations[p]['data-color']}">${filteredVariations[p]['data-colorName']}</option>`);
        }

        currentProductColorDropdown.val('');

        currentProductSizeDropdown.find('option').remove();
        currentProductSizeDropdown.val('');
    });

    jQuery(".cart-item-color-dropdown").on('change', function() {
        let cartItemKey = jQuery(this).data("cart-item-key");

        let currentProductSizeDropdown = jQuery(`.cart-item-size-dropdown[data-cart-item-key='${cartItemKey}']`);
        let currentProductType = jQuery(`.cart-item-type-dropdown[data-cart-item-key='${cartItemKey}']`).val();
        let currentProductColor = jQuery(`.cart-item-color-dropdown[data-cart-item-key='${cartItemKey}']`).val();


        currentProductSizeDropdown.find('option').remove();
        let filteredVariations = getFilteredVariationsForProductSizes(currentProductType, currentProductColor);

        if (filteredVariations.length === 0) {
            let currentProductTypeName = makeFirstLetterOfEachWordCapital(currentProductType);
            let currentProductColorName = makeFirstLetterOfEachWordCapital(currentProductColor);
            jQuery(".mkdv-cart-item-error-message").text(`Sorry, No sizes for ${currentProductColorName} ${currentProductTypeName}. Please change Product Type and Color`);
        }
        for (let p = 0; p < filteredVariations.length; p++) {
            currentProductSizeDropdown.append(`<option value="${filteredVariations[p].value}">${filteredVariations[p]['data-sizeName']}</option>`);
        }

        currentProductSizeDropdown.val('');
    });
    jQuery(".cart-item-size-dropdown").on('change', function() {
        console.log("changed", {
            'cart-item-key': jQuery(this).data('cart-item-key'),
            'oldVariationID': jQuery(this).data('variation-id'),
            'newVariationID': parseInt(jQuery(this).val())
        });

        let dataSent = {
            action: "change_cart_item_size",
            old_cart_item_key: jQuery(this).data('cart-item-key'),
            variationID: parseInt(jQuery(this).val())
        };
        if (!dataSent.variationID) {
            return;
        }
        // console.log("DataSent ---> ", dataSent);
        jQuery.ajax({
            url: "/wp-admin/admin-ajax.php",
            data: dataSent,
            cache: false,
            async: false,
            type: "POST",
            success: function(response) {
                console.log("Response ---> ", response);
                jQuery(document.body).trigger("wc_fragment_refresh");
                location.reload();
            },
            error: function(xhr) {
                console.log("ERROR: ---> ", xhr)
            }
        });

    });


    if (typeof getFilteredVariationsForProductTypes != 'function') {
        function getFilteredVariationsForProductTypes() {
            let filteredTypes = [];
            let filteredVariations = [];
            for (let p = 0; p < mkdvAllVariations.length; p++) {
                if (!filteredTypes.includes(mkdvAllVariations[p]['data-type'])) {
                    mkdvAllVariations[p]['data-typeName'] = makeFirstLetterOfEachWordCapital(mkdvAllVariations[p]['data-type']);
                    filteredTypes.push(mkdvAllVariations[p]['data-type']);
                    filteredVariations.push(mkdvAllVariations[p]);
                }
            }
            filteredVariations.sort((a, b) => a['data-type'].localeCompare(b['data-type']));
            return filteredVariations;
        }
    }

    if (typeof getFilteredVariationsForProductColors != 'function') {
        function getFilteredVariationsForProductColors(productType) {
            let filteredColors = [];
            let filteredVariations = [];
            for (let p = 0; p < mkdvAllVariations.length; p++) {
                if (!filteredColors.includes(mkdvAllVariations[p]['data-color']) && mkdvAllVariations[p]['data-type'] === productType) {
                    mkdvAllVariations[p]['data-colorName'] = makeFirstLetterOfEachWordCapital(mkdvAllVariations[p]['data-color']);
                    filteredColors.push(mkdvAllVariations[p]['data-color']);
                    filteredVariations.push(mkdvAllVariations[p]);
                }
            }
            filteredVariations.sort((a, b) => a['data-color'].localeCompare(b['data-color']));
            return filteredVariations;
        }
    }

    if (typeof getFilteredVariationsForProductSizes != 'function') {
        function getFilteredVariationsForProductSizes(productType, productColor) {
            let filteredVariations = [];
            for (let p = 0; p < mkdvAllVariations.length; p++) {
                if (mkdvAllVariations[p]['data-type'] === productType && mkdvAllVariations[p]['data-color'] === productColor) {
                    mkdvAllVariations[p]['data-sizeName'] = makeFirstLetterOfEachWordCapital(mkdvAllVariations[p]['data-size']);
                    filteredVariations.push(mkdvAllVariations[p]);
                }
            }
            filteredVariations.sort((a, b) => a['data-size'].localeCompare(b['data-size']));
            return filteredVariations;
        }
    }

    if (typeof makeFirstLetterOfEachWordCapital != 'function') {
        function makeFirstLetterOfEachWordCapital(original) {
            let temp = original;
            temp = temp.replace(/\-/g, " ");

            let tempWords = temp.split(" ");

            temp = "";

            for (let q = 0; q < tempWords.length; q++) {
                let word = tempWords[q];
                temp += word[0].toUpperCase() + word.substring(1);
                if (q < tempWords.length - 1) {
                    temp += " ";
                }
            }

            return temp;
        }
    }
</script>