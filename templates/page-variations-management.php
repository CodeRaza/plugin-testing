<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Variations Management</title>

    <style>
        .display-block {
            display: block;
        }

        .hidden {
            display: none;
        }

        .text-blue {
            color: blue;
        }
    </style>
</head>

<body>

    <div>

        <div class="display-block">
            <label>Product Type</label>
            <input type="text" class="product-type" value="t-shirt" />
        </div>
        <div class="display-block">
            <label>Product Color</label>
            <input type="text" class="product-color" value="Black" />
        </div>
        <div class="display-block">
            <label>Product Size</label>
            <input type="text" class="product-size" />
        </div>

        <button onclick="searchVariations();">Search</button>
        <button onclick="resetRowColors();">Reset Row Colors</button>


        <div class="hidden">
            <label>New Product Type</label>
            <input type="text" class="new-product-type" value="t-shirt" />
        </div>
        <div class="display-block">
            <label>New Product Color</label>
            <input type="text" class="new-product-color" value="Red" />
        </div>
        <div class="hidden">
            <label>New Product Size</label>
            <input type="text" class="new-product-size" />
        </div>


        <div>
            <table id="results-table" class="results-table">
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Product Type</th>
                    <th>Product Color</th>
                    <th>Product Size</th>
                    <th>Duplicate</th>
                </tr>
            </table>
        </div>

    </div>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.26.0/axios.min.js" integrity="sha512-bPh3uwgU5qEMipS/VOmRqynnMXGGSRv+72H/N260MQeXZIK4PG48401Bsby9Nq5P5fz7hy5UGNmC/W1Z51h2GQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

    <script>
        let theSite = "https://staging.matchkicks.com";
        let pageURL = new URL(window.location.href);
        let filteredVariations = [];
        let auth = {};
        $(document).ready(function() {
            console.log("ready!");
            if (pageURL.searchParams.get("env") === "production") {
                theSite = "https://www.matchkicks.com";
            }

            auth = {
                username: pageURL.searchParams.get("api_key"),
                password: pageURL.searchParams.get("api_secret"),
            };

        });

        function searchVariations() {
            let productType = $(".product-type").val();
            let productColor = $(".product-color").val();
            let productSize = $(".product-size").val();


            filteredVariations = [];
            resetTable();
            // productColor = productColor.toLowerCase();
            // productSize = productSize.toLowerCase();

            console.log("AUTH = ", auth);

            let variations_url = `${theSite}/wp-json/wc/v3/products/45/variations?search=${productType}&orderby=title&per_page=100&consumer_key=${auth.username}&consumer_secret=${auth.password}`;
            axios.get(variations_url + `&page=1`, {
                auth: auth
            }).then(res1 => {

                let _variations = res1.data;

                axios.get(variations_url + `&page=2`, {
                    auth: auth
                }).then(res2 => {
                    for (let p = 0; p < res2.data.length; p++) {
                        _variations.push(res2.data[p]);
                    }

                    axios.get(variations_url + `&page=3`, {
                        auth: auth
                    }).then(res3 => {
                        for (let p = 0; p < res3.data.length; p++) {
                            _variations.push(res3.data[p]);
                        }

                        axios.get(variations_url + `&page=4`, {
                            auth: auth
                        }).then(res4 => {
                            for (let p = 0; p < res4.data.length; p++) {
                                _variations.push(res4.data[p]);
                            }

                            for (let p = 0; p < _variations.length; p++) {
                                let currentVariation = _variations[p];
                                let shouldBeIncluded = [];
                                let shouldBeIncludedBoolean = false;

                                for (let q = 0; q < currentVariation.attributes.length; q++) {
                                    let currentAttribute = currentVariation.attributes[q];
                                    // currentAttribute.option = currentAttribute.option.toLowerCase();
                                    if (currentAttribute.name === 'Color') {
                                        if (currentAttribute.option.includes(productColor)) {
                                            shouldBeIncluded.push(true);
                                        }
                                    }
                                    if (currentAttribute.name === 'Size') {
                                        if (currentAttribute.option.includes(productSize)) {
                                            shouldBeIncluded.push(true);
                                        }
                                    }
                                }

                                if (shouldBeIncluded[0] === true && shouldBeIncluded[1] === true) {
                                    shouldBeIncludedBoolean = true;
                                }

                                if (shouldBeIncludedBoolean) {

                                    filteredVariations.push(currentVariation);

                                }
                            }

                            filteredVariations = _.orderBy(filteredVariations, [item => item.attributes[0].option, item => item.attributes[1].option], ["asc"]);

                            let prevColor = "";
                            for (let p = 0; p < filteredVariations.length; p++) {
                                let textColorClass = "";
                                if (prevColor !== filteredVariations[p].attributes[0].option) {
                                    textColorClass = "text-blue";
                                }
                                prevColor = filteredVariations[p].attributes[0].option;
                                console.log("filteredVariations = ", filteredVariations[p]);
                                $("#results-table").append(
                                    `
                    <tr data-id="${filteredVariations[p].id}" class="${textColorClass}">
                        <td>${p + 1}</td>
                        <td>${filteredVariations[p].id}</td>
                        <td>${filteredVariations[p].attributes[2].option}</td>
                        <td>${filteredVariations[p].attributes[0].option}</td>
                        <td>${filteredVariations[p].attributes[1].option}</td>
                        <td>
                        <button onclick="duplicateVariation(${filteredVariations[p].id});">Duplicate</button>
                        <button onclick="deleteVariation(${filteredVariations[p].id});">Delete</button>
                        </td>
                    </tr>
                    `
                                );
                            }

                        });

                    });

                });

            });


        }

        function duplicateVariation(variationId) {



            let variationToDuplicate = {};
            let modifiedVariationToDuplicate = {};
            let newProductType = $(".new-product-type").val();
            let newProductColor = $(".new-product-color").val();
            let newProductSize = $(".new-product-size").val();

            console.log("duplicate Variation ID = ", variationId);

            for (let p = 0; p < filteredVariations.length; p++) {
                if (filteredVariations[p].id === variationId) {
                    variationToDuplicate = filteredVariations[p];
                }
            }

            console.log("variation to duplicate = ", variationToDuplicate);
            modifiedVariationToDuplicate = JSON.parse(JSON.stringify(variationToDuplicate));
            for (let q = 0; q < modifiedVariationToDuplicate.attributes.length; q++) {
                let currentAttribute = modifiedVariationToDuplicate.attributes[q];

                if (currentAttribute.name === "Color") {
                    currentAttribute.option = newProductColor;
                }
            }
            let variations_url = `${theSite}/wp-json/wc/v3/products/45/variations`;

            delete modifiedVariationToDuplicate.id;
            delete modifiedVariationToDuplicate.sku;

            modifiedVariationToDuplicate.meta_data = _.filter(modifiedVariationToDuplicate.meta_data, function(m) {
                return m.key !== "iconic_cffv_107746_printful_product_id";
            });
            modifiedVariationToDuplicate.meta_data = _.filter(modifiedVariationToDuplicate.meta_data, function(m) {
                return m.key !== "printful_product_name";
            });
            modifiedVariationToDuplicate.meta_data = _.filter(modifiedVariationToDuplicate.meta_data, function(m) {
                return m.key !== "iconic_cffv_107746_printful_backup_product_id";
            });
            modifiedVariationToDuplicate.meta_data = _.filter(modifiedVariationToDuplicate.meta_data, function(m) {
                return m.key !== "printful_backup_product_name";
            });


            console.log("modified variation to duplicate = ", modifiedVariationToDuplicate);

            axios.post(variations_url, modifiedVariationToDuplicate, {
                auth: auth
            }).then(res => {
                console.log("result = ", res);
                $(`tr[data-id="${variationId}"]`).delay(500).css("background-color", "red");
            });

        }

        function resetRowColors() {
            $(`tr`).delay(500).css("background-color", "white");
        }

        function resetTable() {
            $("#results-table").find("tr:gt(0)").remove();
        }

        function deleteVariation(variationId) {
            let text = `Are you sure you want to delete Product Variation with ID = ${variationId}`;
            if (confirm(text) == true) {
                console.log("You pressed OK!");
                let variations_url = `${theSite}/wp-json/wc/v3/products/45/variations/${variationId}`;
                axios.delete(variations_url, {
                    auth: auth
                }).then(res => {
                    console.log("Variation has been deleted");
                    $(`tr[data-id="${variationId}"]`).remove();
                });
            } else {
                console.log("You canceled!");
            }
        }
    </script>
</body>

</html>