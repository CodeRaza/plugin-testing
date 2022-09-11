<?php

use OpenCloud\Common\Constants\Size;

function my_woocommerce_admin_order_item_values_to_be_removed($_product, $item, $item_id = null)
{
    // get the post meta value from the associated product
    $value = get_post_meta($_product->post->ID, 'Design', 1);

    //print_r($item->get_meta("Design"));


    $d = explode("?", $item->get_meta("Design"));



    $_d = explode("?data=", $item->get_meta("Design"));
    $e = explode("&", $_d[1]);
    //print_r($_d);

    // display the value
    if ($d[0]) {

        parse_str(parse_url($item->get_meta("Design"))['query'], $designUrl);
        $image_url = getRenderedImage($item->get_meta("pa_product-color"), $designUrl['file'], $item->get_meta("Shoe"), null, $item->get_meta("pa_product-type"), json_decode($designUrl['data']));


        echo '<td class="kjhasdf"><a target="_blank" href="' . str_replace("us2", "us31", $d[0]) . "?&data=" . urlencode($e[0]) . "&" . $e[1] . "" . '&designWidth=4000"><img src="' . $image_url  . '" style="max-width:100px !important;padding:5px;border:1px solid #ddd;border-radius:10px;" /></a><br/><a href="https://drive.google.com/drive/u/1/search?q=' . $item->get_meta("pa_design-id") . '" target="_blank">' . $item->get_meta("pa_design-id") . '</a> in Drive</td>';
    } else {
        echo '<td>----</td>';
    }
    if ($item->get_meta("In Order") == "No") {
        echo '<td><div class="in-order in-order-No" data-item-id="' . $item_id . '">No</div><small>Item ID #' . $item_id . '</small></td>';
    } else {
        echo '<td><div class="in-order in-order-Yes" data-item-id="' . $item_id . '">Yes</div><small>Item ID #' . $item_id . '</small></td>';
    }
}


function mk_add_img_to_library($image_url, $filename = null, $mime = null)
{

    $upload_dir = wp_upload_dir();

    $image_data = file_get_contents($image_url);


    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    file_put_contents($file, $image_data);

    if (!$mime)
        $mime = wp_check_filetype($filename, null)['type'];

    if (!$filename)
        $filename = basename($image_url);

    $attachment = array(
        'post_mime_type' => $mime,
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $file);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    return $attach_id;
}

// Fix post counts
function fix_post_counts($views)
{
    global $current_user, $wp_query;
    unset($views['mine']);
    $types = array(
        array('status' =>  NULL),
        array('status' => 'publish'),
        array('status' => 'draft'),
        array('status' => 'pending'),
        array('status' => 'trash')
    );
    foreach ($types as $type) {
        $query = array(
            'author'      => $current_user->ID,
            'post_type'   => 'post',
            'post_status' => $type['status']
        );
        $result = new WP_Query($query);
        if ($type['status'] == NULL) :
            $class = ($wp_query->query_vars['post_status'] == NULL) ? ' class="current"' : '';
            $views['all'] = sprintf(
                __('<a href="%s"' . $class . '>All <span class="count">(%d)</span></a>', 'all'),
                admin_url('edit.php?post_type=post'),
                $result->found_posts
            );
        elseif ($type['status'] == 'publish') :
            $class = ($wp_query->query_vars['post_status'] == 'publish') ? ' class="current"' : '';
            $views['publish'] = sprintf(
                __('<a href="%s"' . $class . '>Published <span class="count">(%d)</span></a>', 'publish'),
                admin_url('edit.php?post_status=publish&post_type=post'),
                $result->found_posts
            );
        elseif ($type['status'] == 'draft') :
            $class = ($wp_query->query_vars['post_status'] == 'draft') ? ' class="current"' : '';
            $views['draft'] = sprintf(
                __('<a href="%s"' . $class . '>Draft' . ((sizeof($result->posts) > 1) ? "s" : "") . ' <span class="count">(%d)</span></a>', 'draft'),
                admin_url('edit.php?post_status=draft&post_type=post'),
                $result->found_posts
            );
        elseif ($type['status'] == 'pending') :
            $class = ($wp_query->query_vars['post_status'] == 'pending') ? ' class="current"' : '';
            $views['pending'] = sprintf(
                __('<a href="%s"' . $class . '>Pending <span class="count">(%d)</span></a>', 'pending'),
                admin_url('edit.php?post_status=pending&post_type=post'),
                $result->found_posts
            );
        elseif ($type['status'] == 'trash') :
            $class = ($wp_query->query_vars['post_status'] == 'trash') ? ' class="current"' : '';
            $views['trash'] = sprintf(
                __('<a href="%s"' . $class . '>Trash <span class="count">(%d)</span></a>', 'trash'),
                admin_url('edit.php?post_status=trash&post_type=post'),
                $result->found_posts
            );
        endif;
    }
    return $views;
}

// Fix media counts
function fix_media_counts($views)
{
    $_total_posts = array();
    $_num_posts = array();
    global $wpdb, $current_user, $post_mime_types, $avail_post_mime_types;
    $views = array();
    $count = $wpdb->get_results("
        SELECT post_mime_type, COUNT( * ) AS num_posts 
        FROM $wpdb->posts 
        WHERE post_type = 'attachment' 
        AND post_author = $current_user->ID 
        AND post_status != 'trash' 
        GROUP BY post_mime_type
    ", ARRAY_A);
    foreach ($count as $row)
        $_num_posts[$row['post_mime_type']] = $row['num_posts'];
    $_total_posts = array_sum($_num_posts);
    $detached = isset($_REQUEST['detached']) || isset($_REQUEST['find_detached']);
    if (!isset($total_orphans))
        $total_orphans = $wpdb->get_var("
            SELECT COUNT( * ) 
            FROM $wpdb->posts 
            WHERE post_type = 'attachment' 
            AND post_author = $current_user->ID 
            AND post_status != 'trash' 
            AND post_parent < 1
        ");
    $matches = wp_match_mime_types(array_keys($post_mime_types), array_keys($_num_posts));
    foreach ($matches as $type => $reals)
        foreach ($reals as $real)
            $num_posts[$type] = (isset($num_posts[$type])) ? $num_posts[$type] + $_num_posts[$real] : $_num_posts[$real];
    $class = (empty($_GET['post_mime_type']) && !$detached && !isset($_GET['status'])) ? ' class="current"' : '';
    $views['all'] = "<a href='upload.php'$class>" . sprintf(__('All <span class="count">(%s)</span>', 'uploaded files'), number_format_i18n($_total_posts)) . '</a>';
    foreach ($post_mime_types as $mime_type => $label) {
        $class = '';
        if (!wp_match_mime_types($mime_type, $avail_post_mime_types))
            continue;
        if (!empty($_GET['post_mime_type']) && wp_match_mime_types($mime_type, $_GET['post_mime_type']))
            $class = ' class="current"';
        if (!empty($num_posts[$mime_type]))
            $views[$mime_type] = "<a href='upload.php?post_mime_type=$mime_type'$class>" . sprintf(translate_nooped_plural($label[2], $num_posts[$mime_type]), $num_posts[$mime_type]) . '</a>';
    }
    $views['detached'] = '<a href="upload.php?detached=1"' . ($detached ? ' class="current"' : '') . '>' . sprintf(__('Unattached <span class="count">(%s)</span>', 'detached files'), $total_orphans) . '</a>';
    return $views;
}

function mk_insert_new_records_from_imported_data($records_data)
{
    $post_import_setting = get_option('mk_post_import_setting', array());
    $file_new_records_ids = get_option('mk_file_new_records_ids', array());

    $save_file_loc = 'solecollector.csv';
    $posts_insert_limit = 5;
    $post_type = 'sneaker';
    if (isset($post_import_setting['save_file_loc']) && !empty($post_import_setting['save_file_loc'])) {
        $save_file_loc = $post_import_setting['save_file_loc'];
    }
    if (isset($post_import_setting['posts_insert_limit']) && !empty($post_import_setting['posts_insert_limit']) && is_numeric($post_import_setting['posts_insert_limit'])) {
        $posts_record_limit = $post_import_setting['posts_insert_limit'];
    }
    if (isset($post_import_setting['post_type']) && !empty($post_import_setting['post_type'])) {
        $post_type = $post_import_setting['post_type'];
    }

    $store_data = mk_read_file_records($save_file_loc, 'insert');

    $file_new_records_ids_chunk = array_chunk($file_new_records_ids, $posts_record_limit, true);

    $chunk_id = 0;
    if (isset($records_data['inserted_upto_chunk']) && is_numeric($records_data['inserted_upto_chunk']) && intval($records_data['inserted_upto_chunk']) > -1) {
        $chunk_id = $records_data['inserted_upto_chunk'] + 1;
    }
    $inserted_upto_id = 0;
    if (isset($records_data['inserted_upto_id']) && is_numeric($records_data['inserted_upto_id']) && intval($records_data['inserted_upto_id']) > -1) {
        $inserted_upto_id = $records_data['inserted_upto_id'];
    }
    if (isset($file_new_records_ids_chunk[$chunk_id]) && is_array($file_new_records_ids_chunk[$chunk_id]) && !empty($file_new_records_ids_chunk[$chunk_id])) {
        foreach ($file_new_records_ids_chunk[$chunk_id] as $row_id => $single_row) {
            if (isset($store_data[$row_id]) && is_array($store_data[$row_id]) && !empty($store_data[$row_id])) {
                $check_post = mk_search_post_by_title($store_data[$row_id][0], $post_type);
                if (!$check_post) {
                    $post = array();
                    $post['post_status']   = 'draft';
                    $post['post_type']     = $post_type;
                    $post['post_title']    = trim($store_data[$row_id][0], ' ');
                    $post['post_author']   = 1;
                    // Create Post
                    $post_id = wp_insert_post($post);
                    if ($post_id) {
                        update_field('release_date', trim($store_data[$row_id][1], ' '), $post_id);
                        $cats = $store_data[$row_id][2];
                        $cat_data = explode(',', $cats);
                        foreach ($cat_data as $cat_name) {
                            $cat_name = trim($cat_name, ' ');
                            if (!empty($cat_name)) {
                                $taxonomy = 'sneaker_category';
                                $append = true;
                                $cat  = get_term_by('name', $cat_name, $taxonomy);
                                if ($cat == false) {
                                    $cat = wp_insert_term($cat_name, $taxonomy);
                                    $cat_id = $cat['term_id'];
                                } else {
                                    $cat_id = $cat->term_id;
                                }
                                $res = wp_set_post_terms($post_id, array($cat_id), $taxonomy, $append);
                            }
                        }

                        if (isset($store_data[$row_id][4]) && !empty($store_data[$row_id][4])) {
                            $image_url = trim($store_data[$row_id][4], ' ');
                            $image_name = basename($image_url);
                            $upload_dir = wp_upload_dir();
                            $image_data = file_get_contents($image_url);
                            $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name);
                            $filename         = basename($unique_file_name);
                            if (wp_mkdir_p($upload_dir['path'])) {
                                $file = $upload_dir['path'] . '/' . $filename;
                            } else {
                                $file = $upload_dir['basedir'] . '/' . $filename;
                            }
                            file_put_contents($file, $image_data);
                            $wp_filetype = wp_check_filetype($filename, null);
                            $image_title = $image_name;
                            if (isset($store_data[$row_id][5]) && !empty(trim($store_data[$row_id][5], ' '))) {
                                $image_title = trim($store_data[$row_id][5], ' ');
                            }
                            $attachment = array(
                                'post_mime_type' => $wp_filetype['type'],
                                'post_title'     => $image_title,
                                'post_content'   => '',
                                'post_status'    => 'inherit'
                            );
                            $attach_id = wp_insert_attachment($attachment, $file, $post_id);

                            require_once(ABSPATH . 'wp-admin/includes/image.php');

                            $attach_data = wp_generate_attachment_metadata($attach_id, $file);

                            wp_update_attachment_metadata($attach_id, $attach_data);
                            set_post_thumbnail($post_id, $attach_id);
                        }
                    }
                }
                $inserted_upto_id = $row_id;
                $records_data['inserted_upto_id'] = $inserted_upto_id;
                update_option('mk_records_data', $records_data);
            }
        }
    } else {
        $records_data['today_records_inserted'] = 1;
    }

    $records_data['inserted_upto_chunk'] = $chunk_id;
    $records_data['inserted_upto_id'] = $inserted_upto_id;
    update_option('mk_records_data', $records_data);
}


/* Function to check new records in import data */
function mk_check_new_records_in_imported_data()
{
    $post_import_setting = get_option('mk_post_import_setting', '');
    $posts_record_limit = 100;
    $post_type = 'sneaker';
    $save_file_loc = 'solecollector.csv';
    if (isset($post_import_setting['posts_record_limit']) && !empty($post_import_setting['posts_record_limit']) && is_numeric($post_import_setting['posts_record_limit'])) {
        $posts_record_limit = $post_import_setting['posts_record_limit'];
    }
    if (isset($post_import_setting['post_type']) && !empty($post_import_setting['post_type'])) {
        $post_type = $post_import_setting['post_type'];
    }
    if (isset($post_import_setting['save_file_loc']) && !empty($post_import_setting['save_file_loc'])) {
        $save_file_loc = $post_import_setting['save_file_loc'];
    }

    $store_data = mk_read_file_records($save_file_loc, 'counting');

    $file_data_chunk = array_chunk($store_data, $posts_record_limit, true);
    $file_new_records_ids = get_option('mk_file_new_records_ids', array());
    $records_data = get_option('mk_records_data', array());

    $chunk_id = 0;
    if (isset($records_data['counted_upto_chunk']) && is_numeric($records_data['counted_upto_chunk']) && intval($records_data['counted_upto_chunk']) > -1) {
        $chunk_id = $records_data['counted_upto_chunk'] + 1;
    }
    $counted_upto_id = 0;
    if (isset($records_data['counted_upto_id']) && is_numeric($records_data['counted_upto_id']) && intval($records_data['counted_upto_id']) > -1) {
        $counted_upto_id = $records_data['counted_upto_id'];
    }
    if (isset($file_data_chunk[$chunk_id]) && is_array($file_data_chunk[$chunk_id]) && !empty($file_data_chunk[$chunk_id])) {
        foreach ($file_data_chunk[$chunk_id] as $row_id => $single_record) {
            if ($row_id > 0 && !in_array($row_id, $file_new_records_ids)) {
                $counted_upto_id = $row_id;
                if (isset($single_record[0]) && !empty($single_record[0])) {
                    $row_title = trim($single_record[0], ' ');
                    $search_result = mk_search_post_by_title($row_title, $post_type);
                    if (!$search_result) {
                        $file_new_records_ids[$row_id] = $row_id;
                    }
                }
            }
        }
    } else {
        $records_data['today_count_completed'] = 1;
    }

    $records_data['counted_upto_chunk'] = $chunk_id;
    $records_data['counted_upto_id'] = $counted_upto_id;
    update_option('mk_file_new_records_ids', $file_new_records_ids);
    update_option('mk_records_data', $records_data);
}

// Reads the file records and returns an array
function mk_read_file_records($save_file_loc = '', $target = '')
{
    if (!file_exists($save_file_loc)) {
        $errors = get_option('mk_error_records', '');
        $errors['file_not_found_' . $target] = 'file not found';
        update_option('mk_error_records', $errors);
        return false;
    }
    $file = fopen($save_file_loc, "r");
    $store_data = array();
    while (($line = fgetcsv($file)) !== FALSE) {
        $store_data[] = $line;
    }
    fclose($file);
    return $store_data;
}


function mk_search_post_by_title($title = '', $post_type = '')
{
    if (empty($post_type)) {
        $post_type = 'post';
    }
    $posts = get_posts(array(
        'post_type'  => $post_type,
        'post_status' => 'any',
        'title' => $title
    ));
    if (is_array($posts) && !empty($posts)) {
        foreach ($posts as $matching_post) {
            if (strtolower(trim($matching_post->post_title, ' ')) == strtolower($title)) {
                return $matching_post->ID;
            }
        }
    }
    return false;
}


/* Function to import data */
function mk_import_csv_data_from_url()
{
    $mk_import_post_url = get_option('mk_import_post_url', '');
    if (!empty($mk_import_post_url)) {
        $ch = curl_init($mk_import_post_url);

        $dir = './post-import-data/';
        $file_name = basename($mk_import_post_url);
        $save_file_loc = $dir . $file_name;
        $post_import_setting = get_option('mk_post_import_setting', array());
        $post_import_setting['save_file_loc'] = $save_file_loc;
        update_option('mk_post_import_setting', $post_import_setting);

        $fp = fopen($save_file_loc, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            if (!empty($error_msg)) {
                $save_error['mk_last_import_error'] = date('d-m-Y') . ' ' . $error_msg;
                update_option('mk_error_records', $save_error);
            }
            return false;
        }

        curl_close($ch);
        fclose($fp);
        return true;
    }
    return false;
}

function w3speedup_before_start_optimization($html)
{
    $html = str_replace(array('class="lozad" data-'), array(''), $html);
    return $html;
}

/**
 * Find matching product variation
 *
 * @param $product_id
 * @param $attributes
 * @return int
 */
function mkdv_find_matching_product_variation_id($product_id, $attributes)
{
    if ($attributes['pa_product-type'] == "") {
        return 0;
    }

    // override $product_id --> added for plugin support
    $product_id = mkdv_get_wc_main_product_id_with_variations();
    if (!$product_id) {
        return 0;
    }

    global $wpdb;
    $custom_sql = "SELECT * FROM {$wpdb->prefix}posts WHERE ";
    $custom_sql .= " post_type = 'product_variation' ";
    $custom_sql .= " AND post_status = 'publish'";
    $custom_sql .= " AND post_parent = " . $product_id;
    $custom_sql .= " AND (";
    $custom_sql .= " post_excerpt LIKE '%" . $attributes['pa_product-color'] . "%'";
    $custom_sql .= " OR post_excerpt LIKE '%" . str_replace("-", " ", $attributes['pa_product-color']) . "%'";
    $custom_sql .= ")";

    $custom_sql .= " AND (";
    $custom_sql .= " post_excerpt LIKE '%" . $attributes['pa_product-type'] . "%'";
    $attributes['pa_product-type'] = str_replace("-", " ", $attributes['pa_product-type']);
    $custom_sql .= " OR post_excerpt LIKE '%" . $attributes['pa_product-type'] . "%'";
    $custom_sql .= ")";

    // var_dump($custom_sql);

    $variations = $wpdb->get_results($custom_sql, OBJECT);

    // if (current_user_can("administrator")) {
    //     echo "variation_exists = ";
    //     var_dump($attributes);
    //     var_dump($custom_sql);
    //     echo "<br/><br/>";
    // }

    return sizeof($variations);
}

function dpdv_find_matching_product_variation_id($product_id, $attributes)
{

    $product_id = mkdv_get_wc_main_product_id_with_variations();
    if (!$product_id) {
        return 0;
    }
    $return = (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
        new \WC_Product($product_id),
        $attributes
    );
    return $return;
}

function mkdv_get_most_recent_order_id()
{
    $order_id = null;
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $most_recent_order_id = get_user_meta($user_id, "most_recent_order_id");
        $order_id = end($most_recent_order_id);

        // var_dump($order_id);
        // echo "<br/><br/>";
    } else {
        if (isset($_COOKIE['most_recent_order_id'])) {
            $order_id = $_COOKIE['most_recent_order_id'];
        }
    }

    if (!$order_id) {
        return null;
    }

    $order = wc_get_order($order_id);

    if ($order->get_status() != 'processing' && $order->get_status() != 'on-hold') {
        return null;
    }

    return $order_id;
}


function mkdv_is_addon_order($order, $is_thankyoupage = false)
{
    $is_addon_order = false;
    $method_title = "";
    foreach ($order->get_items('shipping') as $item_id => $item) {
        $method_title = $item->get_method_title();
        if (strpos($item->get_method_title(), "Add to Previous Order") !== false) {
            $is_addon_order = true;
        }
    }

    setcookie('get_payment_method_title', "d" . $order->get_payment_method_title(), time() + (2 * 60 * 60));

    if ($is_thankyoupage) {
        if ($order->get_status() == 'pending' || $order->get_status() == 'wc-pending') {
            $is_addon_order = false;
        }
    }

    return $is_addon_order;
}

function mkdv_order_actions()
{
    global $post;
?>
    <div class="button wipe-order-amount" data-order-id="<?php echo $post->ID; ?>">
        Wipe Order Amount
    </div>


    <script>
        jQuery(".wipe-order-amount").on("click", function() {
            let order_id = jQuery(this).attr('data-order-id');
            if (confirm("Beware! If you Click 'Ok', it will set the cost of all Line Items = 0, and all fees and shipping costs will be removed from the order. This action is non-reversible. Click Ok if you want to proceed.")) {
                jQuery.ajax({
                    url: `/wp-json/dv/v1/wc_wipe_order_amount?order_id=${order_id}`,
                    // data: 'dataSent',
                    cache: false,
                    async: true,
                    type: "GET",
                    success: function(response) {
                        console.log("Order Amount has been wiped.");
                        location.reload();
                    },
                    error: function(xhr) {
                        console.log("ERROR: ---> ", xhr)
                    }
                });
            }
        });
    </script>

<?php
}

/**
 * Insert an attachment from an URL address.
 *
 * @param  String $url
 * @param  Int    $parent_post_id
 * @return Int    Attachment ID
 */
function crb_insert_attachment_from_url($url, $parent_post_id = null)
{

    if (!class_exists('WP_Http'))
        include_once(ABSPATH . WPINC . '/class-http.php');

    $http = new WP_Http();
    $response = $http->request($url);
    if ($response['response']['code'] != 200) {
        return false;
    }

    $upload = wp_upload_bits(basename($url), null, $response['body']);
    if (!empty($upload['error'])) {
        return false;
    }

    $file_path = $upload['file'];
    $file_name = basename($file_path);
    $file_type = wp_check_filetype($file_name, null);
    $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
    $wp_upload_dir = wp_upload_dir();

    $post_info = array(
        'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
        'post_mime_type' => $file_type['type'],
        'post_title'     => $attachment_title,
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    // Create the attachment
    $attach_id = wp_insert_attachment($post_info, $file_path, $parent_post_id);

    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Define attachment metadata
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);

    // Assign metadata to attachment
    wp_update_attachment_metadata($attach_id,  $attach_data);

    return $attach_id;
}

function wpt_sneaker_previews()
{
    global $post; ?>
    <style>
        #cs {
            display: none;
        }

        .pthumbnail {
            background: white;
        }

        .pthumbnail img {
            cursor: crosshair;
        }

        .preview-color {
            width: 100%;
            height: 40px;
        }

        .pthumbnail {
            position: fixed;
            bottom: 0;
            right: 0;
            width: 300px;
            height: 400px;
        }

        .pthumbnail img {
            background: white;
            width: 100%;
        }

        .pthumbnail .result-out {
            font-weight: bold;
            text-align: center;
            margin: 10px;
        }
    </style>
    <script>
        function copy(text) {
            var input = document.createElement('input');
            input.setAttribute('value', text);
            document.body.appendChild(input);
            input.select();
            var result = document.execCommand('copy');
            document.body.removeChild(input);
            return result;
        }


        function mkColorPicker() {

            var sneakerImg = jQuery("#set-post-thumbnail img").attr("src");
            jQuery.get("/scripts/img2base.php", {
                    img: sneakerImg
                })
                .done(function(data) {
                    console.log(data);
                    jQuery(".pthumbnail img").attr("src", data);
                    var img = _('.pthumbnail img'),
                        canvas = _('#cs'),
                        result = _('.result-out'),
                        preview = _('.preview-color'),
                        x = '',
                        y = '';

                    img.addEventListener('click', function(e) {
                        if (e.offsetX) {
                            x = e.offsetX;
                            y = e.offsetY;
                        } else if (e.layerX) {
                            x = e.layerX;
                            y = e.layerY;
                        }
                        useCanvas(canvas, img, function() {
                            var p = canvas.getContext('2d')
                                .getImageData(x, y, 1, 1).data;
                            result.innerHTML = '<span>HEX has been copied: ' + rgbToHex(p[0], p[1], p[2]) +
                                '</span>';
                            var hex = rgbToHex(p[0], p[1], p[2]);
                            copy(hex);
                            preview.style.background = rgbToHex(p[0], p[1], p[2]);
                        });
                    }, false);

                    img.addEventListener('mousemove', function(e) {
                        if (e.offsetX) {
                            x = e.offsetX;
                            y = e.offsetY;
                        } else if (e.layerX) {
                            x = e.layerX;
                            y = e.layerY;
                        }

                        useCanvas(canvas, img, function() {

                            var p = canvas.getContext('2d')
                                .getImageData(x, y, 1, 1).data;
                            preview.style.background = rgbToHex(p[0], p[1], p[2]);
                        });
                    }, false);

                    function useCanvas(el, image, callback) {
                        el.width = image.width;
                        el.height = image.height;
                        el.getContext('2d')
                            .drawImage(image, 0, 0, image.width, image.height);
                        return callback();
                    }

                    function _(el) {
                        return document.querySelector(el);
                    };

                    function componentToHex(c) {
                        var hex = c.toString(16);
                        return hex.length == 1 ? "0" + hex : hex;
                    }

                    function rgbToHex(r, g, b) {
                        return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
                    }

                    function findPos(obj) {
                        var curleft = 0,
                            curtop = 0;
                        if (obj.offsetParent) {
                            do {
                                curleft += obj.offsetLeft;
                                curtop += obj.offsetTop;
                            } while (obj = obj.offsetParent);
                            return {
                                x: curleft,
                                y: curtop
                            };
                        }
                        return undefined;
                    }
                });

        }

        jQuery('body').on('click', '.media-button-select', function() {
            alert("Changed");
            mkColorPicker();
        });

        document.body.addEventListener(
            'load',

            function(event) {
                var elm = event.target;
                if (jQuery(elm).attr("class").includes('attachment-')) {
                    mkColorPicker()
                }
            },
            true // Capture event
        );
        jQuery(document).ready(function() {
            mkColorPicker();
        });
    </script>
    <div class="pthumbnail">
        <img class="lozad" />
        <div class="preview-color"></div>
        <div class="result-out">
            <span>Click a color above to Select it</span>
        </div>
    </div>
    <canvas id="cs"></canvas>

    <?php if (get_post_meta($post->ID, 'mcolors', true)) {

        $q2 = array(
            'post_type' => 'design',
            'posts_per_page' => 8,
            'post_status' => 'publish',
            'orderby' => 'rand'
        );

        $loop2 = new WP_Query($q2);

        // Get Sneakers!
        foreach ($loop2->posts as $post2) {

            $sneakerImage = MKProductImage($post, $post2, 't-shirt');

            echo ' <div style="display:inline-block;" class="box has-hover has-hover box-default box-text-bottom">
            <div class="box-image">
            <a href="/wp-admin/post.php?post=' . $post->ID . '&action=edit"> <div class="">
            <img class="lozad" style="max-width:250px;margin:10px;" src="' . $sneakerImage['preview'] . '"> </div>
            </a> </div>
            </div>';
        }
    } // end if this is a post yet
}

function wpt_design_previews()
{

    global $post;

    $collections = get_post_meta($post->ID, 'collections', true);
    //echo "<pre>" . print_r($collections,1);

    if (empty($collections)) {
        $q2 = array(
            'post_type' => 'sneaker',
            'post_status' => 'publish',
            'posts_per_page' => 8,
            'orderby' => 'rand'
        );

        $loop2 = new WP_Query($q2);

        // Get Sneakers!
        foreach ($loop2->posts as $post2) {

            $sneakerImage = MKProductImage($post2, $post, 't-shirt');

            echo ' <div style="display:inline-block;" class="box has-hover has-hover box-default box-text-bottom">
				<div class="box-image">
				<a href="/wp-admin/post.php?post=' . $post2->ID . '&action=edit"> <div class="">
				<img class="lozad" style="max-width:250px;margin:10px;" src="' . $sneakerImage['preview'] . '"> </div>
				</a> </div>
				</div>';
        }
    } else {
        //echo "Not Empty...<br/>";
        //echo "<pre>" . print_r($collections);
        foreach ($collections as $SneakerID) {
            //echo "<pre>" . print_r($collections,1);

            $sneakerImage = MKProductImage($SneakerID, $post, 't-shirt');
            //echo "<pre>" . print_r($sneakerImage,1);


            echo ' <div style="display:inline-block;" class="box has-hover has-hover box-default box-text-bottom">
				<div class="box-image">
				<a href="/wp-admin/post.php?post=' . $SneakerID . '&action=edit"> <div class="">
				<img class="lozad" style="max-width:250px;margin:10px;" src="' . $sneakerImage['preview'] . '"> </div>
				</a> </div>
				</div>';
        }
    }

    ?>
    <script>
        jQuery(document).ready(function() {
            jQuery("#set-post-thumbnail img").attr("src", "/wp-content/themes/flatsome-child/EditDelete.jpg");
        })
    </script>
<?php }


function wpt_design()
{
    global $post;

    $post_id = $post->ID;
    if ($post->post_status == 'publish') {
        echo "<a href='/scripts/get-svg-admin.php?p=" . $post_id . "' target='_blank' title='Click here to view the raw .SVG file of this design.'><img style='max-width:100%' src='/scripts/designs/" . $post_id . "-sm.jpg?v=1'/>Click here to access the .SVG file on the server currently.</a>";
    }
}


function my_search_pre_get_posts($query)
{

    // If it's not a search return
    if (!isset($query->query_vars['s']))
        return;

    // If it's a search but there's no prefix, return
    if ('#' != substr($query->query_vars['s'], 0, 1))
        return;

    // Validate the numeric value
    $id = absint(substr($query->query_vars['s'], 1));
    if (!$id)
        return; // Return if no ID, absint returns 0 for invalid values

    // If we reach here, all criteria is fulfilled, unset search and select by ID instead
    unset($query->query_vars['s']);
    $query->query_vars['p'] = $id;
}

function unique_id($l = 10)
{
    return substr(md5(uniqid(mt_rand(), true)), 0, $l);
}

function MKGetBestColors($meta, $sneaker = null)
{
    global $_GET;
    if ($sneaker) {
        $meta = get_post_meta($sneaker);
    }

    $colors = array();
    foreach ($meta as $key => $value) {
        if (substr($key, 0, 8) === "mcolors_" && substr($key, -4) == 'type') {
            $colorID = preg_replace('/[^0-9]/', '', $key);
            if ($value[0] == 'color') {
                $colorVal = $meta['mcolors_' . $colorID . '_' . $value[0]][0];
            } else if ($value[0] == 'pattern') {
                $colorVal = wp_get_attachment_url($meta['mcolors_' . $colorID . '_' . $value[0]][0]);
            }
            $colors[] = array(
                "id" => $colorID,
                "type" => $value[0],
                "value" => $colorVal
            );
        }
    }

    // need to make sure they're in the right order as entered in admin.
    foreach ($colors as $color) {
        $newColors[$color['id']] = $color;
    }
    $colors = $newColors;

    //echo "<pre>" . print_r($colors,1); die;
    $i = 0;
    foreach ($colors as $color) {

        if (isset($_GET['color'])) {
            $meta['best_for'][0] = esc_attr($_GET['color']);
        }
        // Only if this is the first color in the sneaker, do we decide what the color of the shirt is
        if ($i < 1) {
            $bestForShirt = "White";
            if (isset($meta['best_for'])) {
                $bestForShirt = $meta['best_for'][0];
            }
            $skipFirst = 1;
            if (isset($meta['skip_first'])) {
                $skipFirst = $meta['skip_first'][0];
            }
            $colorShirt = $color['value'];
            $rgb = HTMLToRGB($colorShirt);
            $hsl = RGBToHSL($rgb);
            if ($hsl->lightness < 20 && $bestForShirt != 'White' && $bestForShirt != 'Grey') {
                $colorShirt = '#000000';
                $colorShirtName = 'Black';
                $startI = 1;
                unset($colors[$i]);
            } else if ($hsl->lightness > 240 && $bestForShirt != 'Black') {
                $colorShirt = '#ffffff';
                $colorShirtName = 'White';

                $startI = 0;
                if ($bestForShirt != 'Grey') {
                    $startI = 1;
                    unset($colors[$i]);
                }
            } else if ($skipFirst != 1) {
                $colorShirt = '#ffffff';
                $colorShirtName = 'White';
                $startI = 0;
            } else if ($skipFirst == 1) {
                $startI = 1;
            }
            if (!empty($bestForShirt) && $bestForShirt != 'Any') {
                $colorShirtName = $bestForShirt;
            }
        }
        $i++;
    }

    // if (current_user_can("administrator")) {

    $doesVariationExist = mkdv_find_matching_product_variation_id(45, array(
        'pa_product-color' => $colorShirtName,
        'pa_product-type' => isset($_SESSION['type']) ? $_SESSION['type'] : 't-shirt'
    ));

    if ($doesVariationExist == 0) {
        $colorShirt = '#ffffff';
        $colorShirtName = 'White';
    }
    // }

    if ($colorShirtName == 'White') {
        foreach ($colors as $key => $color) {
            if ($color['value'] == '#ffffff') {
                unset($colors[$key]);
            }
        }
    } else if ($colorShirtName == 'Black') {
        foreach ($colors as $key => $color) {
            if ($color['value'] == '#000000') {
                unset($colors[$key]);
            }
        }
    }

    $totalColors = count($colors);
    $totalColorsDisplay = $i;

    return array(
        "colors" => $colors,
        "colorShirtName" => $colorShirtName,
        "startColorsAt" => $startI,
        "totalColors" => $totalColors,
        "totalColorsDisplay" => $totalColorsDisplay,
        "meta" => $meta
    );
}

function MKGetLayers($meta, $design = null)
{
    if ($design) {
        $meta = get_post_meta($design);
    }

    $layers = array();
    $i = 0;
    foreach ($meta as $key => $value) {
        if (substr($key, 0, 7) == "layers_") {
            if (strpos($key, "_id")  !== false) {
                $id = $value[0];
            } else if (strpos($key, "_best")  === false) {
                if ($value[0]) {
                    $i++;
                    $layers[] = array("id" => str_replace("&amp;", "_x26_", str_replace(",", "_x2C_", str_replace("'", "_x27_", str_replace(" ", "_", $id)))), "name" => $value[0]);
                }
            }
        }
    }

    return $layers;
}

function MKProductImage($sneaker, $design, $productType = 't-shirt', $brand = 93450, $showMatchingProduct = true, $showLogo = true)
{

    // if ((isset($_GET['optimized_mk_product']) && $_GET['optimized_mk_product'] == 'yes') && (wp_get_current_user()->user_login == "designsvalley" || current_user_can('administrator'))) {
    //     echo "o";

    $args = array(
        'product_type' => $productType,
        'sneaker' => $sneaker,
        'design' => $design,
    );
    if (isset($_GET['productColor'])) {
        $args['product_color'] = $_GET['productColor'];
    }
    $design_pact_product_instance = new Design_Pact_Product($args);

    if (count($design_pact_product_instance->wp_error->get_error_messages()) > 0) {
        return $design_pact_product_instance->wp_error->get_error_messages();
    }

    $design_pact_product_instance->get_product();

    $return = $design_pact_product_instance->old_formatted_output;

    // var_dump($return);

    return $return;
    // }

    // Convert to ID's if needed
    if (!is_object($sneaker)) {
        $sneaker = get_post($sneaker);
    }
    if (!is_object($design)) {
        $design = get_post($design);
    }

    $logoUrl = "";
    if ($brand != 93450) {
        $attachmentURL = wp_get_attachment_url(get_post_meta($brand, 'logo', true));
        $logoUrl = str_replace("https://nyc3.digitaloceanspaces.com/matchkicks-s3/", "", $attachmentURL);
    }

    // sneaker
    $sneakerImg = get_the_post_thumbnail_url($sneaker->ID, 'medium');
    if (!$sneakerImg) {
        $sneakerImg = get_post_meta($sneaker->ID, 'image_link', true);
    }
    $meta = get_post_meta($sneaker->ID);

    // Get Colors from our Function
    $colorData = MKGetBestColors($meta);

    $layers = MKGetLayers('', $design->ID);

    $data = array();
    $i = $colorData['startColorsAt'];

    foreach ($layers as $layer) {
        if ($i > $colorData['totalColors']) {
            $i = $colorData['startColorsAt'];
        }
        // if ($i >= (sizeof($colorData['colors'])) - 1) {
        //     break;
        // }
        if (isset($colorData['colors'][$i])) {
            switch ($colorData['colors'][$i]['type']) {
                case "color":
                    $value = ltrim($colorData['colors'][$i]['value'], "#");
                    $property = 'background-colour';
                    break;
                case "pattern":
                    $value = remove_wp_upload_base_url($colorData['colors'][$i]['value']);
                    $property = 'background-image';
                    break;
            }
            $i++;
            $data[] = array("id" => $layer['id'], "value" => $value, "property" => $property, "i" => $i);
        }
    }

    // $designImage = remove_wp_upload_base_url(get_the_post_thumbnail_url($design->ID, "full"));
    $designImage = remove_wp_upload_base_url(mkdv_get_the_post_thumbnail_url($design->ID));
    if (!$designImage) {
        $designImage = get_post_meta($design->ID, 'image_link', true);
    }

    $imgPath = node_api_base_url() . '/convertSvgToPng?data=' . json_encode($data) . "&file=" . $designImage . "&v=2";

    $finalImage = getRenderedImage($colorData['colorShirtName'], $designImage, $sneakerImg, $design->post_modified, $productType, $data, $logoUrl, $showMatchingProduct, $showLogo);

    $mockupGeneratorURL = mkdv_get_mockup_generator_url($colorData['colorShirtName'], $designImage, $sneakerImg, $design->post_modified, $productType, $data, $logoUrl, $showMatchingProduct, $showLogo);

    $product_page_url = dpdv_prepare_single_product_link($sneaker, $design, $productType);

    return array(
        "data" => $data,
        "colors" => $colorData['colors'],
        "sneakerImg" => $sneakerImg,
        "product" => $productType,
        "png" => $imgPath,
        "preview" => $finalImage,
        "preview_v3" => $mockupGeneratorURL,
        "design" => json_decode(json_encode($design), 1),
        "sneaker" => json_decode(json_encode($sneaker), 1),
        "colorShirtName" => $colorData['colorShirtName'],
        "totalColors" => $colorData['totalColors'],
        "totalColorsDisplay" => $colorData['totalColorsDisplay'],
        "product_page_url" => $product_page_url,
    );
}

function MKProductImageQuad($sneaker, $designs, $productType = 't-shirt')
{

    // Convert to ID's if needed
    if (!is_object($sneaker)) {
        $sneaker = get_post($sneaker);
    }

    $designArray = array();
    foreach ($designs as $design) {
        $designArray[] = get_post($design);
    }

    // sneaker
    $sneakerImg = get_the_post_thumbnail_url($sneaker->ID, 'medium');

    $meta = get_post_meta($sneaker->ID);

    // Get Colors from our Function
    $colorData = MKGetBestColors($meta);

    $API = array();
    $image = 1;
    foreach ($designArray as $design) {
        $layers = MKGetLayers('', $design->ID);

        $data = array();
        $i = $colorData['startColorsAt'];

        foreach ($layers as $layer) {
            if ($i > $colorData['totalColors']) {
                $i = $colorData['startColorsAt'];
            }

            switch ($colorData['colors'][$i]['type']) {
                case "color":
                    $value = ltrim($colorData['colors'][$i]['value'], "#");
                    $property = 'background-colour';
                    break;
                case "pattern":
                    $value = remove_wp_upload_base_url($colorData['colors'][$i]['value']);
                    $property = 'background-image';
                    break;
            }
            $i++;
            $data[] = array("id" => $layer['id'], "value" => $value, "property" => $property, "i" => $i);
        }

        $designImage = remove_wp_upload_base_url(get_the_post_thumbnail_url($design->ID, "full"));

        $API['image' . $image] = json_encode(array(
            "productType" => $productType,
            "background" => strtolower($colorData['colorShirtName']),
            "designUrl" => $designImage,
            "sneakerUrl" => remove_wp_upload_base_url($sneakerImg),
            "designData" => json_encode($data),
            "scalingFactor" => 1
        ), JSON_UNESCAPED_SLASHES);
        $image++;
    }
    $API['scalingFactor'] = 1;
    $finalImage = node_api_base_url() . '/get4by4Image?' . http_build_query($API);

    return array(
        "colors" => $colorData['colors'],
        "sneakerImg" => $sneakerImg,
        "product" => $productType,
        "preview" => $finalImage,
        "sneaker" => json_decode(json_encode($sneaker), 1),
        "colorShirtName" => $colorData['colorShirtName'],
        "totalColors" => $colorData['totalColors'],
        "totalColorsDisplay" => $colorData['totalColorsDisplay']
    );
}

function HTMLToRGB($htmlCode)
{
    if (!is_string($htmlCode)) {
        return $htmlCode;
    }
    if ($htmlCode[0] == '#')
        $htmlCode = substr($htmlCode, 1);

    if (strlen($htmlCode) == 3) {
        $htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
    }

    $r = hexdec($htmlCode[0] . $htmlCode[1]);
    $g = hexdec($htmlCode[2] . $htmlCode[3]);
    $b = hexdec($htmlCode[4] . $htmlCode[5]);

    return $b + ($g << 0x8) + ($r << 0x10);
}

function RGBToHSL($RGB)
{
    $r = 0xFF & ($RGB >> 0x10);
    $g = 0xFF & ($RGB >> 0x8);
    $b = 0xFF & $RGB;

    $r = ((float)$r) / 255.0;
    $g = ((float)$g) / 255.0;
    $b = ((float)$b) / 255.0;

    $maxC = max($r, $g, $b);
    $minC = min($r, $g, $b);

    $l = ($maxC + $minC) / 2.0;

    if ($maxC == $minC) {
        $s = 0;
        $h = 0;
    } else {
        if ($l < .5) {
            $s = ($maxC - $minC) / ($maxC + $minC);
        } else {
            $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
        }
        if ($r == $maxC)
            $h = ($g - $b) / ($maxC - $minC);
        if ($g == $maxC)
            $h = 2.0 + ($b - $r) / ($maxC - $minC);
        if ($b == $maxC)
            $h = 4.0 + ($r - $g) / ($maxC - $minC);

        $h = $h / 6.0;
    }

    $h = (int)round(255.0 * $h);
    $s = (int)round(255.0 * $s);
    $l = (int)round(255.0 * $l);

    return (object) array('hue' => $h, 'saturation' => $s, 'lightness' => $l);
}

function getRenderedImage($colorShirtName, $designImage, $sneakerImg, $post = null, $productType = 't-shirt', $designData = null, $logoPath = null, $showMatchingProduct = true, $showLogo = true)
{
    $dpdv_options = get_option('dpdv_options');

    $designImage = remove_wp_upload_base_url($designImage);
    $designPath = node_api_base_url() . '/convertSvgToPng?data=' . json_encode($designData) . "&file=" . $designImage . "&v=2";
    $sneakerImg = remove_wp_upload_base_url($sneakerImg);
    $logoPath = remove_wp_upload_base_url($dpdv_options['design_pact_node_api_logo']);

    $render = array(
        "mockupType" => $productType,
        "backgroundColor" => $colorShirtName,
        "sneakerPath" => "assets/" . $sneakerImg,
        "designPath" => $designPath,
        "scalingFactor" => "1.7",
    );

    if ($showMatchingProduct === false) {
        $render['sneakerPath'] = "";
    }

    if ($logoPath) {
        $render['logoPath'] = $logoPath;
    }

    if ($showLogo == false) {
        $render['showLogoOverlay'] = "false";
    }

    $render['v'] = 1;

    $generateQuery = http_build_query($render);

    //echo "<div class=\"wps_dv_get_post_views\" style=\"display:none;\">" . wps_dv_get_post_views(get_the_ID()) . "</div>";
    return node_api_base_url() . '/mockupGenerator?' . $generateQuery;
}

function mkdv_get_mockup_generator_url($colorShirtName, $designImage, $sneakerImg, $post = null, $productType = 't-shirt', $designData, $logoPath = null, $showMatchingProduct = true, $showLogo = true)
{
    $dpdv_options = get_option('dpdv_options');

    $designImage = remove_wp_upload_base_url($designImage);
    $sneakerImg = remove_wp_upload_base_url($sneakerImg);
    $logoPath = remove_wp_upload_base_url($dpdv_options['design_pact_node_api_logo']);

    $render = array(
        "productType" => $productType,
        "background" => $colorShirtName,
        "sneakerUrl" => "assets/" . $sneakerImg,
        "designUrl" => $designImage,
        "designData" => json_encode($designData),
        "scalingFactor" => "0.5",
        "getFilesFrom" => "url"
    );

    if ($showMatchingProduct === false) {
        $render['sneakerUrl'] = "";
    }

    if ($logoPath) {
        $render['logoPath'] = $logoPath;
    }

    if ($showLogo == false) {
        $render['showLogoOverlay'] = "false";
    }

    $render['v'] = 1;

    $generateQuery = http_build_query($render);

    // var_dump($render['logoPath']);
    // print_r($generateQuery);
    // die;

    //echo "<div class=\"wps_dv_get_post_views\" style=\"display:none;\">" . wps_dv_get_post_views(get_the_ID()) . "</div>";
    return node_api_base_url() . '/mockupGenerator?' . $generateQuery;
}

function file_get_contents_curl($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function getAllAttributesArray($reverse = true)
{
    $attributes = array(
        "Product Type" => "pa_product-type",
        "Color" => "pa_product-color",
        "Size" => "pa_product-size"
    );

    foreach ($attributes as $key => $value) {

        $productListPre = get_terms(array(
            'taxonomy' => $value,
            'hide_empty' => false,
        ));

        if ($reverse == true) {
            $productList = array_reverse($productListPre);
        } else {
            $productList = $productListPre;
        }
        foreach ($productList as $individualItem) {
            $finalAttributes[$value][$individualItem->slug] = $individualItem;
        }
    }


    $sortedArray = array();
    foreach ($finalAttributes['pa_product-type'] as $typeKey => $typeValue) {
        foreach ($finalAttributes['pa_product-size'] as $sizeKey => $sizeValue) {
            foreach ($finalAttributes['pa_product-color'] as $colorKey => $colorValue) {
                $sortedArray[$typeKey][$colorKey][$sizeKey] = 0;
            }
        }
    }

    return $sortedArray;
}

function get_private_order_notes($order_id)
{
    global $wpdb;

    $table_perfixed = $wpdb->prefix . 'comments';
    $results = $wpdb->get_results("
        SELECT *
        FROM $table_perfixed
        WHERE  `comment_post_ID` = $order_id
        AND  `comment_type` LIKE  'order_note'
    ");

    foreach ($results as $note) {
        $order_note[]  = array(
            'note_id'      => $note->comment_ID,
            'note_date'    => $note->comment_date,
            'note_author'  => $note->comment_author,
            'note_content' => $note->comment_content,
        );
    }
    return $order_note;
}

function valid_phone($str, $international = false)
{
    $str = trim($str);
    $str = preg_replace('/\s+(#|x|ext(ension)?)\.?:?\s*(\d+)/', ' ext \3', $str);

    $us_number = preg_match('/^(\+\s*)?((0{0,2}1{1,3}[^\d]+)?\(?\s*([2-9][0-9]{2})\s*[^\d]?\s*([2-9][0-9]{2})\s*[^\d]?\s*([\d]{4})){1}(\s*([[:alpha:]#][^\d]*\d.*))?$/', $str, $matches);

    if ($us_number) {
        return $matches[4] . '-' . $matches[5] . '-' . $matches[6] . (!empty($matches[8]) ? ' ' . $matches[8] : '');
    }

    if (!$international) {
        /* SET ERROR: The field must be a valid U.S. phone number (e.g. 888-888-8888) */
        return false;
    }

    $valid_number = preg_match('/^(\+\s*)?(?=([.,\s()-]*\d){8})([\d(][\d.,\s()-]*)([[:alpha:]#][^\d]*\d.*)?$/', $str, $matches) && preg_match('/\d{2}/', $str);

    if ($valid_number) {
        return trim($matches[1]) . trim($matches[3]) . (!empty($matches[4]) ? ' ' . $matches[4] : '');
    }

    /* SET ERROR: The field must be a valid phone number (e.g. 888-888-8888) */
    return false;
}

function mk_text_customer($phone, $message)
{
    $dpdv_options = get_option('dpdv_options');

    $twilio_account_id = $dpdv_options['twilio_account_id'];
    $twilio_messaging_service_id = $dpdv_options['twilio_messaging_service_id'];
    $twilio_api_key = $dpdv_options['twilio_api_key'];

    if (empty($twilio_account_id) || empty($twilio_messaging_service_id) || empty($twilio_api_key)) {
        print_r('DPDV Core is not configured properly');
    }

    // text the customer 
    $curl = curl_init();

    $requestData = array(
        "To" => valid_phone($phone),
        "MessagingServiceSid" => $twilio_messaging_service_id,
        "Body" => str_replace("https://", "", get_bloginfo('url')) . ": " . $message
    );

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.twilio.com/2010-04-01/Accounts/$twilio_account_id/Messages.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($requestData),
        CURLOPT_HTTPHEADER => array(
            "authorization: Basic $twilio_api_key",
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
            "postman-token: 707ca112-2a10-750a-2e58-7df89de0df9e"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
}

function mk_email_customer($email, $email_subject = "Note from Match Kicks Team", $message = "Please contact us on our website as soon as possible.")
{
    // email the customer

    $headers  = 'From: Match Kicks <' . get_option('dpdv_info_email') . '>' . "\r\n";
    $headers .= 'Content-Type: text/html' . "\r\n";
    $headers .= 'Reply-To:  ' . get_option('dpdv_support_email') . ' ' . "\r\n";
    ob_start();

    wc_get_template('emails/email-header.php', array('email_heading' => $email_subject));
    $email_body_template_header = ob_get_clean();

    ob_start();

    wc_get_template('emails/email-footer.php');
    $email_body_template_footer = ob_get_clean();

    $site_title                 = get_bloginfo('name');
    $email_body_template_footer = str_ireplace('{site_title}', $site_title, $email_body_template_footer);

    $middle = "<br/><br/>Hi There, <br/><br/><b>" . $message . "</b><br/><br/>Please <a href='/chat'>Click Here</a> to respond and get in touch with customer service if you need any additional help.<br/><br/><br/>";
    $final_email_body = $email_body_template_header . $middle . $email_body_template_footer;
    wc_mail($email, $email_subject, $final_email_body, $headers);
}

function mkdv_get_the_post_thumbnail_url($post_id)
{
    global $wpdb;
    $posts_table = $wpdb->prefix . "posts";
    $postmeta_table = $wpdb->prefix . "postmeta";
    $qqqq = "SELECT p.id AS post_id, pm2.meta_value AS URL FROM `$posts_table` AS p INNER JOIN `$postmeta_table` AS pm1 ON p.id = pm1.post_id INNER JOIN `$postmeta_table` AS pm2 ON pm1.meta_value = pm2.post_id AND pm2.meta_key = '_wp_attached_file' AND pm1.meta_key = '_thumbnail_id' WHERE p.id = " . '%d' . " ORDER BY p.id DESC";
    $results = $wpdb->get_results(
        $wpdb->prepare($qqqq, array($post_id))
    );
    $the_url = "";
    if (sizeof($results) > 0) {
        $the_url = $results[0]->URL;
    }
    return $the_url;
}




// dddd
function customer_upload_own_sneaker_old()
{
    wp_enqueue_script("customer-color-picker", "/wp-content/themes/flatsome-child/js/customer-color-picker.js", false, "1.1.19");
    wp_enqueue_style("customer-color-picker", "/wp-content/themes/flatsome-child/css/customer-color-picker.css", false, "1.1.11");



    global $wpdb;

    $otherContent = '';
    $replace = [];
    $best_for = '';
    $otherContentEnd = '';

    if (!is_user_logged_in()) {
        return do_shortcode('[page_header type="share" bg="112395" bg_overlay="rgba(0,0,0,.5)" bg_pos="51% 68%"][section][row][col span__sm="12"]<h2>Sign in or sign up for a free Match Kicks account to create your own sneaker apparel!</h2><br/><br/>[button text="Sign In" color="secondary" class="preview-popup" link="/my-account"][button text="Create Account" color="primary" class="preview-popup" link="/my-account"][/col][/row][/section]');
    }

    if (!$_GET['id']) {
        return do_shortcode('[page_header type="share" bg="112395" bg_overlay="rgba(0,0,0,.5)" bg_pos="51% 68%"][section][row][col span__sm="12"][wpforms id="112507" title="false"][/col][/row][/section]');
    } else {

        $otherContent .= '<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script><script src="/wp-content/themes/flatsome-child/js/jquery.ui.touch-punch.min.js"></script>';

        $otherContent .= "<form id='customer-create-sneaker' method='post' action='/scripts/customer-create-sneaker.php'>";

        $entry = $wpdb->get_results("SELECT * FROM `mk_wpforms_entries` WHERE `entry_id` = " . sanitize_text_field($_GET['id']));


        $data = json_decode($entry[0]->fields, 1);

        //echo "<pre>" . print_r($data,1);

        if (!$data[2]['value']) {
            return "Invalid Entry.";
        }

        $find[] = "{sneaker_image}";

        $replace[] .= '<input type="hidden" name="name" value="' . $data[1]['value'] . '" /><input type="hidden" name="image_id" value="' . $data[3]['value_raw'][0]['attachment_id'] . '" /><input type="hidden" value="' . $data[3]['value'] . '" class="original-upload-link" /><div class="pthumbnail">
        <img class="lozad" />
        <div class="preview-color"></div>
        <div class="result-out">
            <span>Pinch and zoom. Tap on up to 5 colors.</span>
					</div></div>
							<canvas id="cs"></canvas><div class="selected_colors"></div>';

        $find[] = "{material_colors}";

        $posts_table = $wpdb->prefix . "posts";
        $materials = unserialize($wpdb->get_results("SELECT `post_content` FROM `$posts_table` WHERE `ID` = 24440")[0]->post_content);


        foreach ($materials['choices'] as $key => $value) {
            $best_for .= '<input required="required" type="radio" id="' . $key . '" name="best_for" value="' . $key . '">
					  <label for="' . $key . '">' . $value . '</label><br>';
        }

        $replace[] = $best_for;

        $find[] = "{skip_first_color}";

        $replace[] = "<input type='checkbox' value='1' name='skip_first' id='skip_first' /> <label for='skip_first'>The first color I selected is also the color of the shirt material.</label>";

        $otherContentEnd .= "</form>";

        return str_replace($find, $replace, $otherContent . do_shortcode('[block id="upload-your-own-sneaker"]') . $otherContentEnd);
    }
}






function content_sneaker_image($parsed_data)
{
    var_dump($parsed_data);
    echo "<br/><br/>";

?>
    <input type="hidden" name="name" value="<?php echo $data[1]['value']; ?>" />
    <input type="hidden" name="image_id" value="<?php echo $data[3]['value_raw'][0]['attachment_id']; ?>" />
    <input type="hidden" value="<?php echo $data[3]['value']; ?>" class="original-upload-link" />
    <div class="pthumbnail">
        <img id="the-sneaker-image" class="lozad" src="<?php echo $parsed_data['Upload a Clear Photo']; ?>" />
        <!-- <img id="the-sneaker-image" class="lozad" src="/scripts/printful/temp/sneaker-image.png" /> -->
        <!-- <img id="the-sneaker-image" class="lozad" src="		https://matchkicks-s3-backups.nyc3.digitaloceanspaces.com/2021-10-05-02:38:15AM/assets/2020/08/rainbow-stripes-wallpaper-mural.jpg" /> -->

        <div class="preview-color"></div>
        <div class="result-out">
            <span>Pinch and zoom. Tap on up to 5 colors.</span>
        </div>

        <div id="sneaker-colors">

        </div>
    </div>
    <canvas id="cs"></canvas>
    <div class="selected_colors">
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.0/color-thief.umd.js"></script>
    <script nonce="8IBTHwOdqNKAWeKl7plt8g==">
        const imgURL = "/scripts/printful/temp/sneaker-image.png";

        const colorThief = new ColorThief();
        const image = document.getElementById('the-sneaker-image');
        image.crossOrigin = "Anonymous";

        const swatches = 12;


        image.onload = () => {
            URL.revokeObjectURL(image.src);
            const colors = colorThief.getPalette(image, swatches);
            colors.forEach(color => {
                const rgbColor = `rgb(${color[0]}, ${color[1]}, ${color[2]})`;
                jQuery("#sneaker-colors").append(`<div style="background: ${rgb2hex(rgbColor)}">${rgb2hex(rgbColor)}</div>`);
            });

        }
        // img.addEventListener('load', function() {
        // 	var vibrant = new Vibrant(img);
        // 	var swatches = vibrant.swatches()
        // 	for (var swatch in swatches) {
        // 		console.log("Swatches === ", swatches[swatch]);
        // 		if (swatches.hasOwnProperty(swatch) && swatches[swatch]) {
        // 			console.log(swatch, swatches[swatch].getHex());
        // 			jQuery("#sneaker-colors").append(`<div style="background: ${swatches[swatch].getHex()}">${swatch}</div>`);
        // 		}
        // 	}
        // });


        //Function to convert rgb color to hex format
        function rgb2hex(rgb) {
            console.log("=======", rgb);
            rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
        }

        function hex(x) {
            var hexDigits = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");
            return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
        }
    </script>
    <?php
}

function content_material_colors()
{
    global $wpdb;
    $posts_table = $wpdb->prefix . 'posts';
    $materials = unserialize($wpdb->get_results("SELECT `post_content` FROM `$posts_table` WHERE `ID` = 24440")[0]->post_content);

    foreach ($materials['choices'] as $key => $value) {
    ?>
        <input required="required" type="radio" id="<?php echo $key; ?>" name="best_for" value="<?php echo $key; ?>">
        <label for="<?php echo $key; ?>"><?php echo $value; ?>
        </label>
        <br>
    <?php
    }
}

function content_skip_first_colors()
{
    ?>
    <input type='checkbox' value='1' name='skip_first' id='skip_first' />
    <label for='skip_first'>The first color I selected is also the color of the shirt material.</label>
<?php
}

function content_of_shortcode($ux_block)
{
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="/wp-content/themes/flatsome-child/js/jquery.ui.touch-punch.min.js"></script>
    <form id='customer-create-sneaker' method='post' action='/scripts/customer-create-sneaker.php'>
        <?php echo $ux_block; ?>
    </form>
<?php
}


// Post views function
function wps_dv_set_post_views($postID)
{
    $count_key = 'dv_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if ($count == '') {
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

// Get post views
function wps_dv_get_post_views($postID)
{
    $count_key = 'dv_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if ($count == '') {
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return '0';
    }
    return $count;
}


function mkdv_generate_random_live_mockup($product_url, $mockup_type = 't-shirt')
{

    // uncomment the following check if you want to make it available only for the admins
    if (!current_user_can('administrator') || !isset($_GET['for_admins'])) {
        return $product_url;
    }

    $valid_mockup_types = ['t-shirt', 'hoodie', 'crop-top'];
    if (!in_array($mockup_type, $valid_mockup_types)) {
        return $product_url;
    }

    $valid_appends = mkdv_get_valid_mock_appends($mockup_type);

    $random_append = $valid_appends[rand(0, sizeof($valid_appends) - 1)];

    // return $product_url;

    $product_url = str_replace("mockupType=$mockup_type", "mockupType=$mockup_type-$random_append", $product_url);
    return $product_url;
}

function mkdv_get_valid_mock_appends($mockup_type, $limited = false)
{
    $valid_appends = array();
    if (in_array($mockup_type, ['t-shirt'])) {
        $valid_appends = [
            "black-female-1",
            "black-female-2",
            "black-female-3",
            "black-female-4",
            "black-male-1",
            "black-male-2",
            "black-male-3",
            "black-male-4",
            "black-male-5",
            "spanish-female-1",
            "spanish-female-2",
            "white-female-1",
            "white-female-2",
            "white-female-3",
            "white-female-4",
            "white-male-1",
            "white-male-2",
            "white-male-3",
            "white-male-4",
            "white-male-5",
        ];
        if ($limited == true) {
            $valid_appends = [
                "black-female-1",
                "black-female-2",
                // "black-female-3",
                // "black-female-4",
                "black-male-1",
                "black-male-2",
                // "black-male-3",
                // "black-male-4",
                // "black-male-5",
                "spanish-female-1",
                // "spanish-female-2",
                "white-female-1",
                // "white-female-2",
                // "white-female-3",
                // "white-female-4",
                "white-male-1",
                "white-male-2",
                // "white-male-3",
                // "white-male-4",
                // "white-male-5",
            ];
        }
    } else if (in_array($mockup_type, ['hoodie'])) {
        $valid_appends = [
            "black-female-1",
            "black-female-2",
            "black-male-1",
            "black-male-2",
            "black-male-3",
            "black-male-4",
            "black-male-5",
            "spanish-female-1",
            "spanish-female-2",
            "white-female-1",
            "white-female-2",
            "white-female-3",
            "white-female-4",
            "white-male-1",
            "white-male-2",
            "white-male-3",
            "white-male-4",
            "white-male-5",
        ];
        if ($limited == true) {
            $valid_appends = [
                "black-female-1",
                "black-female-2",
                "black-male-1",
                "black-male-2",
                // "black-male-3",
                // "black-male-4",
                // "black-male-5",
                "spanish-female-1",
                // "spanish-female-2",
                "white-female-1",
                // "white-female-2",
                // "white-female-3",
                // "white-female-4",
                "white-male-1",
                "white-male-2",
                // "white-male-3",
                // "white-male-4",
                // "white-male-5",
            ];
        }
    } else if (in_array($mockup_type, ['crop-top'])) {
        $valid_appends = [
            "black-female-1",
            "black-female-2",
            "black-female-3",
            "spanish-female-1",
            "spanish-female-2",
            "spanish-female-3",
            "spanish-female-4",
            "white-female-1",
            "white-female-2",
            "white-female-3",
        ];
        if ($limited == true) {
            $valid_appends = [
                "black-female-1",
                "black-female-2",
                // "black-female-3",
                "spanish-female-1",
                "spanish-female-2",
                // "spanish-female-3",
                // "spanish-female-4",
                "white-female-1",
                "white-female-2",
                // "white-female-3",
            ];
        }
    }
    return $valid_appends;
}

function mkdv_get_wc_main_product_id_with_variations()
{
    $dpdv_plugin_options = get_option('dpdv_options');
    if (!isset($dpdv_plugin_options['field_wc_product_id'])) {
        return 0;
    }

    // override $product_id --> added for plugin support
    $product_id = $dpdv_plugin_options['field_wc_product_id'];

    $_pf = new WC_Product_Factory();
    $_product = $_pf->get_product($product_id);
    if (!$_product) {
        $return = new WP_Error('broke', __("Invalid WC Product ID in Design Pact Plugin settings", "dpdv"));
        if (is_wp_error($return)) {
            print_r($return->get_error_message());
            die();
        }
        return 0;
    }

    return $product_id;
}

function get_current_url()
{
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return $actual_link;
}
function change_url_parameter($url, $parameterName, $parameterValue)
{
    $url = parse_url($url);
    parse_str($url["query"], $parameters);
    unset($parameters[$parameterName]);
    $parameters[$parameterName] = $parameterValue;
    return  sprintf(
        "%s://%s%s?%s",
        $url["scheme"],
        $url["host"],
        $url["path"],
        http_build_query($parameters)
    );
}
