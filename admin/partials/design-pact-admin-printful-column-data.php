<?php
// Supported view types = ['product-data', 'edit-button', 'javascript']
if (!$args['view']) {
    return;
}
?>
<!-- ########################################################## -->
<!-- CASE view='product-data' -->
<!-- ########################################################## -->
<?php
if ($args['view'] == 'product-data') {

    // $_product = $args['_product'];
    $product = $args['product'];
    $options = $args['options'];
?>
    <!-- If !$product, then show nothing -->
    <?php
    if (!$product) {
        echo "N/A- Not Assigned";
        return;
    }
    ?>
    <!-- Show printful product data -->
    <?php
    $randID = md5(rand());
    ?>
    <div id="<?= $randID; ?>"></div>

    <script>
        jQuery(document).ready(function() {

            let options = <?= json_encode($options); ?>;
            jQuery.ajax({
                type: "GET",
                contentType: "application/json; charset=utf-8",
                url: "/wp-json/dpdv/v1/printful/get-variation",
                data: {
                    variation_id: "<?= $product; ?>"
                },
                success: function(result) {
                    jQuery("#<?= $randID; ?>").html("");
                    // let obj = JSON.parse(result);
                    let obj = result.body;
                    let availabilityStatus = obj.result.variant.availability_status;
                    var arrayLength = availabilityStatus.length;

                    jQuery("#<?= $randID; ?>").append(`<br/><span style='background:${options.background};color:${options.color};'>${options.text}</span> <b>` + obj.result.variant.name + "</b><br/>");
                    jQuery("#<?= $randID; ?>").append("<i>Our Cost: $" + obj.result.variant.price + "</i> - Stock Status Below<br/>");

                    for (var i = 0; i < arrayLength; i++) {
                        console.log(availabilityStatus[i]['region']);

                        let stateColor = 'red';
                        if (availabilityStatus[i]['status'] == "in_stock") {
                            stateColor = 'green';
                        }
                        console.log(availabilityStatus[i]['status']);

                        jQuery("#<?= $randID; ?>").append("<div style='background:" + stateColor + ";width:10px;height:10px;display:inline-block;border-radius:20px;'> </div> " + availabilityStatus[i]['region'] + "<br/>");
                    }
                }
            });
        });
    </script>

<?php
    return;
}
?>
<!-- ########################################################## -->
<!-- CASE view='edit-button' -->
<!-- ########################################################## -->
<?php
if ($args['view'] == 'edit-button') {
    $_product = $args['_product'];
    if (!$_product || !$_product->get_id()) {
        return;
    }
?>
    <style>
        .hide {
            display: none !important;
        }
    </style>

    <br />
    <span data-product-id="<?= $_product->get_id(); ?>" class="button edit_variations">Edit Variations</span>
    <div data-product-id="<?= $_product->get_id(); ?>" class="variations_to_edit hide">
        <hr>
        <label>Set New Primary Printful Product</label>
        <select data-product-id="<?= $_product->get_id(); ?>" data-type="primary" class="printful-select2-variations" name="new_primary">
            <option></option>
        </select>
        <br />
        <br />
        <label>Set New Backup Printful Product</label>
        <select data-product-id="<?= $_product->get_id(); ?>" data-type="backup" class="printful-select2-variations" name="new_backup">
            <option></option>
        </select>
    </div>
    <br />

<?php
    return;
}
?>
<!-- ########################################################## -->
<!-- CASE view='javascript' -->
<!-- ########################################################## -->
<?php
if ($args['view'] == 'javascript') {
?>
    <script>
        jQuery(document).ready(function() {
            jQuery('.printful-select2-variations').select2({
                "width": "400px",
                ajax: {
                    url: "/wp-json/dpdv/v1/printful/get-cached-variations",
                    dataType: 'json',
                    processResults: function(data) {
                        let $currentSelect2 = jQuery(jQuery(this)[0].$element);
                        let results = [];
                        Object.keys(data).forEach(key => {
                            for (let i = 0; i < data[key].variants.length; i++) {
                                let _variant = data[key].variants[i];

                                let _result = {
                                    id: _variant.id,
                                    text: _variant.title
                                };

                                results.push(_result);
                            }
                        });
                        return {
                            results: results
                        };
                    },
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            q: params.term
                        }

                        return query;
                    },
                }
            });

            jQuery(`.edit_variations`).click(function(e) {
                // alert("Loading Variations...");
                let dataVariation = jQuery(this).attr("data-product-id");
                jQuery(`.edit_variations[data-product-id=${dataVariation}]`).addClass("hide");
                // for (let j = 1; j <= 3; j++) {
                //     jQuery.ajax({
                //         url: '/scripts/printful/printful_cached_variations_' + j + '.txt',
                //         type: 'GET',
                //         dataType: 'json',
                //         success: function(json) {
                //             jQuery.each(json, function(i, value) {
                //                 jQuery(`.printful-select2-variations[data-product-id=${dataVariation}]`).append(jQuery('<optgroup>').attr('label', `value.product.title-${j}`));
                //                 jQuery.each(value.variants, function(i, variant) {
                //                     jQuery(`.printful-select2-variations[data-product-id=${dataVariation}]`).append(jQuery('<option>').text(variant.title).attr('value', variant.id));
                //                 });
                //             });
                //         }
                //     });
                // }

                jQuery(`.variations_to_edit[data-product-id=${dataVariation}]`).removeClass("hide");
            });

            jQuery(`.printful-select2-variations`).on('select2:select', function(e) {
                // alert("Changing");
                let dataType = jQuery(this).attr("data-type");
                let dataVariation = jQuery(this).attr("data-product-id");
                let dataPrintful = jQuery(this).val();

                let data = {
                    type: dataType,
                    wc_variation_id: dataVariation,
                    pf_variation_id: dataPrintful,
                    pf_variation_name: dataPrintful
                };

                console.log("data ===>", data);

                jQuery.ajax({
                    type: "GET",
                    url: "/wp-json/dpdv/v1/printful/map-variation",
                    data: data,
                    success: function(result) {
                        //alert("Saved!");
                    }
                });

            });
        });
    </script>
<?php
    return;
}
?>