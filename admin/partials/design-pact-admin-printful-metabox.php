<?php
if (!$args['view']) {
    return;
}
$site = "https://" . $_SERVER['HTTP_HOST'];
global $post;
?>
<?php
if ($args['view'] == 'actions') {

?>
    <div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>
                            Current Status
                        </th>
                        <th>
                            Information
                        </th>
                        <th>
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php

                            $printful_service = new DPDV_Printful_Service($post->ID);
                            $pf_order_response = $printful_service->fetch_pf_order($printful_service->pf_order_id);
                            $pf_order_response_status_code = wp_remote_retrieve_response_code($pf_order_response);
                            $pf_order_response_body = json_decode(wp_remote_retrieve_body($pf_order_response), true);
                            if (!is_wp_error($pf_order_response)) {
                                $printful_service->set_pf_order($pf_order_response_body['result']);
                            }


                            if ($printful_service->pf_order_exists) {
                                echo "<span style='background:green;color:white;padding:5px;font-size:16px;'>IN PRINTFUL</span>";
                            } else {
                                echo "<span style='background:#ddd;color:black;padding:5px;font-size:16px;'>NOT USING</span>";
                            }
                            ?>
                        </td>
                        <td class="printfulInfo">
                            <?php

                            if ($printful_service->pf_order_exists) {
                            ?>
                                Printful Order Status: <b><u><?php echo strtoupper($printful_service->pf_order['status']); ?></u></b><br />
                                Shipping Service: <?php echo $printful_service->pf_order['shipping_service_name']; ?><br />
                                Total Order Cost: $<?php echo $printful_service->pf_order['costs']['total']; ?>
                            <?php
                            } else {
                                echo "More information about the order in Printful will appear here when you push it.";
                            } ?>
                        </td>
                        <td>
                            <?php if ($printful_service->pf_order_exists) { ?>
                                <a href="<?= $printful_service->pf_order['dashboard_url']; ?>&output_type=array" target="_blank">View in Printful Dashboard</a><br /><br />
                            <?php } ?>
                            <?php if ($printful_service->is_pf_order_editable()) { ?>
                                <a href="/wp-json/dpdv/v1/printful/orders/cancel?order_id=<?= $post->ID; ?>&reset_order_status=true&output_type=array" target="_blank">Cancel Printful Fulfilment</a>
                            <?php } else { ?>
                                <a href="/wp-json/dpdv/v1/printful/orders/create?order_id=<?= $post->ID; ?>&force_push=true&confirm=1&output_type=array" target="_blank">Switch to Printful </a>
                                <a href="/wp-json/dpdv/v1/printful/orders/create?order_id=<?= $post->ID; ?>&force_push=true&reset_pf_push_attempts=true&confirm=1&output_type=array" style="color:red;" target="_blank">Force</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php if (wp_get_current_user()->user_login == "designsvalley" || 1 == 1) { ?>
                        <tr class="pf-webhook-data">
                            <td colspan="3">
                                <h3>Webhook History</h3>
                            </td>
                        </tr>
                        <tr class="pf-webhook-data">
                            <td>
                                <select class="inline-block">
                                    <option>No Data</option>
                                </select>
                            </td>
                            <td>
                                <div class="button load-pf-webhook-data inline-block">Load Webhook Data</div>
                            </td>
                            <td>
                                <div class="button sync-pf-order-status inline-block">Sync Order Status</div>
                            </td>

                            <script>
                                jQuery(`.pf-webhook-data .load-pf-webhook-data`).on('click', function() {
                                    jQuery.ajax({
                                        url: `/wp-json/dpdv/v1/printful/sync-order-statuses?order_id=<?= $post->ID; ?>`,
                                        success: function(response) {
                                            jQuery(".pf-webhook-data select").empty();
                                            let ret = ``;
                                            ret += `<option data-payload=''>Select Event</option>`;
                                            response.forEach((item, i) => {
                                                ret += `<option data-payload='${JSON.stringify(item)}'>${item.type}</option>`;
                                            });
                                            jQuery('.pf-webhook-data select').append(ret);
                                        },
                                        error: function(xhr) {}
                                    });
                                });
                                jQuery(`.pf-webhook-data .sync-pf-order-status`).on('click', function() {
                                    let payload = jQuery('.pf-webhook-data select').find(':selected').attr('data-payload');
                                    if (payload == '') {
                                        return;
                                    }
                                    jQuery.post({
                                        url: `/wp-json/dpdv/v1/printful/api-webhook`,
                                        data: JSON.parse(payload),
                                        success: function(response) {
                                            console.log(response);
                                        },
                                        error: function(xhr) {}
                                    });
                                });
                            </script>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        // In your Javascript (external .js resource or <script> tag)
        jQuery(document).ready(function() {




        });
    </script>

<?php

    return;
}

?>