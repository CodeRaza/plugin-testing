<?php

// a replacement class for MKProductImage() function that does much more than MKProductImage()

class Design_Pact_Product
{

    public $args = array();
    public $product = array(
        'title' => null,
    );
    public $product_title = null;
    public $product_image = null;
    public $sneaker = null;
    public $design = null;

    public $old_formatted_output = array();

    public $wp_error;

    function __construct($args)
    {
        $this->wp_error = new WP_Error();
        $this->set_args($args);

        return $this;
    }

    function set_args($args)
    {
        $this->args = $args;

        if (!isset($args['product_type'])) {
            $args['product_type'] = 't-shirt';
        }

        if (!isset($args['sneaker'])) {
            $this->wp_error->add(404, 'invalid sneaker');
        }

        if (!isset($args['design'])) {
            $this->wp_error->add(404, 'invalid design');
        }

        if (!isset($args['show_log'])) {
            $args['show_log'] = true;
        }

        if (!isset($args['show_matching_product'])) {
            $args['show_matching_product'] = true;
        }

        $this->parsed_args = $args;
    }

    function init()
    {
        $this->sneaker = $this->parsed_args['sneaker'];
        if (!is_object($this->sneaker)) {
            $this->sneaker = get_post($this->sneaker);
        }

        $this->design = $this->parsed_args['design'];
        if (!is_object($this->design)) {
            $this->design = get_post($this->design);
        }
    }

    function get_product()
    {
        $this->init();
        $this->sneaker->image = $this->get_sneaker_image();
        $this->sneaker->meta = get_post_meta($this->sneaker->ID);
        $this->sneaker->colors = $this->get_sneaker_colors();

        $this->design->meta = get_post_meta($this->design->ID);
        $this->design->layers = $this->get_design_layers();
        $this->design->data = $this->get_design_data();
        $this->design->svg_image = $this->get_design_svg_image();
        $this->design->image = $this->get_design_png_image();

        $this->product_image = $this->product_image();
        $this->old_formatted_output = $this->get_output_in_old_format();
    }


    private function product_image()
    {
        $dpdv_options = get_option('dpdv_options');

        $logoPath = remove_wp_upload_base_url($dpdv_options['design_pact_node_api_logo']);

        if (isset($this->parsed_args['product_color'])) {
            $this->sneaker->shirt_color_name = $this->parsed_args['product_color'];
        }

        $render = array(
            "mockupType" => $this->parsed_args['product_type'],
            "backgroundColor" => $this->sneaker->shirt_color_name,
            "sneakerPath" => "assets/" . $this->sneaker->image,
            "designPath" => $this->design->image,
            "scalingFactor" => "1.7",
        );

        if ($this->parsed_args['show_matching_product'] == false) {
            $render['sneakerPath'] = "";
        }

        if ($logoPath) {
            $render['logoPath'] = $logoPath;
        }

        if ($this->parsed_args['show_logo'] == false) {
            $render['showLogoOverlay'] = "false";
        }

        $render['v'] = 1;

        $generateQuery = http_build_query($render);

        return node_api_base_url() . '/mockupGenerator?' . $generateQuery;
    }

    // generates the single-product url
    function product_url($product_type, $sneaker, $design)
    {
    }

    private function get_sneaker_image()
    {
        $sneaker_image = get_the_post_thumbnail_url($this->sneaker->ID, 'medium');
        if (!$sneaker_image) {
            $sneaker_image = get_post_meta($this->sneaker->ID, 'image_link', true);
        }
        $sneaker_image = remove_wp_upload_base_url($sneaker_image);
        return $sneaker_image;
    }

    private function get_design_svg_image()
    {
        $design_image = get_the_post_thumbnail_url($this->design->ID, 'medium');
        if (!$design_image) {
            $design_image = get_post_meta($this->design->ID, 'image_link', true);
        }
        $design_image = remove_wp_upload_base_url($design_image);

        return $design_image;
    }

    private function get_design_png_image()
    {
        $image = node_api_base_url() . '/convertSvgToPng?data=' . json_encode($this->design->data) . "&file=" . $this->design->svg_image . "&v=2";
        return $image;
    }

    private function get_sneaker_colors()
    {

        $mcolors = array();
        $meta = $this->sneaker->meta;
        foreach ($meta as $key => $value) {
            if (substr($key, 0, 8) === "mcolors_" && substr($key, -4) == 'type') {
                $colorID = preg_replace('/[^0-9]/', '', $key);
                if ($value[0] == 'color') {
                    $colorVal = $meta['mcolors_' . $colorID . '_' . $value[0]][0];
                } else if ($value[0] == 'pattern') {
                    $colorVal = wp_get_attachment_url($meta['mcolors_' . $colorID . '_' . $value[0]][0]);
                }
                $mcolors[] = array(
                    "id" => $colorID,
                    "type" => $value[0],
                    "value" => $colorVal
                );
            }
        }

        // need to make sure they're in the right order as entered in admin.
        foreach ($mcolors as $color) {
            $newColors[$color['id']] = $color;
        }
        $mcolors = $newColors;
        $meta_best_for = $this->sneaker->meta['best_for'][0];
        $meta_skip_first = $this->sneaker->meta['skip_first'][0];

        // $mcolors = get_field('mcolors', $this->sneaker->ID);
        // $meta_best_for = get_field('best_for', $this->sneaker->ID);
        // $meta_skip_first = get_field('skip_first', $this->sneaker->ID);

        $best_for_shirt = "White";
        if (isset($meta_best_for)) {
            $best_for_shirt = $meta_best_for;
        }

        $skip_first = 1;
        if (isset($meta['skip_first'])) {
            $skip_first = $meta['skip_first'][0];
        }

        if (!empty($best_for_shirt) && $best_for_shirt != 'Any') {
            $shirt_color_name = $best_for_shirt;
        }

        foreach ($mcolors as $i => $m_color) {
            $m_color['id'] = $i;

            // if ($m_color['type'] == 'color') {
            //     $m_color['value'] = $m_color['color'];
            // } else {
            //     $m_color['value'] = wp_get_attachment_url($m_color['pattern']);
            // }

            $mcolors[$i] = $m_color;

            if ($i > 0) {
                continue;
            }

            $shirt_color = $m_color['value'];
            $rgb = HTMLToRGB($shirt_color);
            $hsl = RGBToHSL($rgb);

            if ($hsl->lightness < 20 && $best_for_shirt != 'White' && $best_for_shirt != 'Grey') {
                $shirt_color = '#000000';
                $shirt_color_name = 'Black';
                $startI = 1;
                unset($colors[$i]);
            } else if ($hsl->lightness > 240 && $best_for_shirt != 'Black') {
                $shirt_color = '#ffffff';
                $shirt_color_name = 'White';

                $startI = 0;
                if ($best_for_shirt != 'Grey') {
                    $startI = 1;
                    unset($colors[$i]);
                }
            } else if ($skip_first != 1) {
                $shirt_color = '#ffffff';
                $shirt_color_name = 'White';
                $startI = 0;
            } else if ($skip_first == 1) {
                $startI = 1;
            }
        }

        $does_variation_exist = $this->does_variations_exist(45, array(
            'pa_product-color' => $shirt_color_name,
            'pa_product-type' => isset($_SESSION['type']) ? $_SESSION['type'] : 't-shirt'
        ));

        if ($does_variation_exist == 0) {
            $shirt_color = '#ffffff';
            $shirt_color_name = 'White';
        }

        if ($shirt_color_name == 'White') {
            foreach ($mcolors as $key => $color) {
                if ($color['value'] == '#ffffff') {
                    unset($mcolors[$key]);
                    break;
                }
            }
        } else if ($shirt_color_name == 'Black') {
            foreach ($mcolors as $key => $color) {
                if ($color['value'] == '#000000') {
                    unset($mcolors[$key]);
                    break;
                }
            }
        }

        $this->sneaker->start_color_at = $startI;
        $this->sneaker->mcolors = $mcolors;
        $this->sneaker->shirt_color = $shirt_color;
        $this->sneaker->shirt_color_name = $shirt_color_name;
        $this->sneaker->skip_first = $meta_skip_first;
    }

    function get_design_layers()
    {
        $layers = array();
        $i = 0;
        foreach ($this->design->meta as $key => $value) {
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

    function get_design_data()
    {
        $data = array();
        $i = $this->sneaker->start_color_at;
        $layers = $this->design->layers;
        foreach ($layers as $layer) {
            if ($i > count($this->sneaker->mcolors)) {
                $i = $this->sneaker->start_color_at;
            }
            if (isset($this->sneaker->mcolors[$i])) {
                switch ($this->sneaker->mcolors[$i]['type']) {
                    case "color":
                        $value = ltrim($this->sneaker->mcolors[$i]['value'], "#");
                        $property = 'background-colour';
                        break;
                    case "pattern":
                        $value = remove_wp_upload_base_url($this->sneaker->mcolors[$i]['value']);
                        $property = 'background-image';
                        break;
                }
                $i++;
                $data[] = array(
                    "id" => $layer['id'],
                    "value" => $value,
                    "property" => $property,
                    "i" => $i
                );
            }
        }

        return $data;
    }

    function does_variations_exist($product_id, $attributes)
    {
        if ($attributes['pa_product-type'] == "") {
            return 0;
        }

        // $product_id = mkdv_get_wc_main_product_id_with_variations();
        $product_id = 45;
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
        $attributes['pa_product-color'] = str_replace("-", " ", $attributes['pa_product-color']);
        $custom_sql .= " OR post_excerpt LIKE '%" . $attributes['pa_product-color'] . "%'";
        $custom_sql .= ")";

        $custom_sql .= " AND (";
        $custom_sql .= " post_excerpt LIKE '%" . $attributes['pa_product-type'] . "%'";
        $attributes['pa_product-type'] = str_replace("-", " ", $attributes['pa_product-type']);
        $custom_sql .= " OR post_excerpt LIKE '%" . $attributes['pa_product-type'] . "%'";
        $custom_sql .= ")";

        $variations = $wpdb->get_results($custom_sql, OBJECT);

        return sizeof($variations);
    }

    function get_output_in_old_format()
    {
        return array(
            "data" => $this->design->data,
            "colors" => $this->sneaker->mcolors,
            "sneakerImg" => $this->sneaker->image,
            "product" => $this->parsed_args['product_type'],
            "png" => $this->design->image,
            "preview" => $this->product_image,
            "design" => json_decode(json_encode($this->design), 1),
            "sneaker" => json_decode(json_encode($this->sneaker), 1),
            "colorShirtName" => $this->sneaker->shirt_color_name,
            "totalColors" => count($this->sneaker->mcolors),
            // "totalColorsDisplay" => $colorData['totalColorsDisplay'],
            // "product_page_url" => $product_page_url,
        );
    }
}
