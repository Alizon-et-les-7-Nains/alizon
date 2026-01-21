var flockingPrices = {
    productPrice: 0,
    flockingPrice: 0,
    flockingShortPrice: 0,
    lfpLogoPrice: 0
};

var selectedProduct = null;
var selectedProductId = null;
var block_flocage_name = null;
var block_flocage_num = null;
var current_nb_lettre = 9;

$(document).on('ready', function () {

    var $personalizations = $('[name^="add_product_personalization"]');

    // Unactivate rest of script
    if ($personalizations.length === 0) {
        return false;
    }

    if ($('#nq_product_personalization').length) {
        // Product page
        productPersonlization();

    } else if ($('#nq_producthome_personalization').length) {
        // Home page
        homePersonalization();
    }

    globalPersonalization();

    // $('#floc_shirt_choice').trigger('change');
});

var cleanVisualization = function()
{
    block_flocage_name.empty();
    block_flocage_num.empty();
}

var initPersonnalization = function() {
    block_flocage_name = $('#visualization .text_base');
    block_flocage_num = $('#visualization .flocage_num');
};

/**
 * Prototype product page personalization
 */
var productPersonlization = function() {

    var $personalizationBlock = $('#nq_product_personalization');
    var $personalizationBlockContent = $personalizationBlock.children('.content');

    // Init selected product and preview image
    selectedProductId = $('[name="id_product_personalization"]').val();
    if ('undefined' !== typeof shirtsMapping && undefined !== shirtsMapping[selectedProductId]) {
        selectedProduct = shirtsMapping[selectedProductId];
    }

    $('.picture-personalization').attr('src', previewImageUrl);

    // with or not customization
    $(document).on('change', 'input[name="has_personalization"]', function (e) {
        e.stopPropagation();

        $personalizationBlock = $('#nq_product_personalization');
        $personalizationBlockContent = $personalizationBlock.children('.content');

        if ($(this).is(':checked')) {
            $personalizationBlock.addClass('with open_customization');

            $personalizationBlockContent.slideDown();
            $personalizationBlockContent.show();
            $personalizationBlock.children('.checkbox').addClass('open_customization');

        } else {
            $personalizationBlock.removeClass('with open_customization');
            $personalizationBlock.find('select.with').selectBox('value', 0);

            $personalizationBlockContent.slideUp();

            $personalizationBlock.children('.checkbox').removeClass('open_customization');
        }
    });

    // open/close panel vizualisation
    $(document).on('click', '#deploy-visualization', function (e) {
        $personalizationBlock = $('#nq_product_personalization');
        $personalizationBlockContent = $personalizationBlock.children('.content');

        if ($personalizationBlock.hasClass('open_visualization')) {
            $personalizationBlock.removeClass('open_visualization');

        } else {
            $personalizationBlock.addClass('open_visualization');
        }
    });

    // Set price
    if ('undefined' !== typeof productPrice) {
        flockingPrices.productPrice = productPrice;
    }

    setPersonalizationPrice();


    if ($('input[name="has_personalization"]:checked').length > 0) {
        $('input[name="has_personalization"]').trigger('change');
    }
};


/**
 * Prototype home page personalization
 */
var homePersonalization = function() {

    if ('undefined' === typeof personalizableProducts) {
        return false;
    }

    var $typeList = $('#product_page_product_id');
    selectedProductId = $typeList.val();

    // Init
    setSizeCombinations($typeList.val());
    setPersonalizationPrice();

    // On type change
    $typeList.on('change', function() {
        selectedProductId = $typeList.val();
        // Init selected product and preview image
        if ('undefined' !== typeof shirtsMapping && undefined !== shirtsMapping[selectedProductId]) {
            selectedProduct = shirtsMapping[selectedProductId];
        }

        setSponsorExclusion($(this).val());
        setSizeCombinations($(this).val());
    });

    /**
     * Set sizes combinations
     *
     * @returns {boolean}
     */
    function setSizeCombinations(choosenProduct) {
        var $sizeList = $('#idCombination');

        // Check combinations
        if (undefined === personalizableProducts[choosenProduct]
            || undefined === personalizableProducts[choosenProduct].combinations
        ) {
            return false;
        }

        // Clear list
        $sizeList.empty();

        $(personalizableProducts[choosenProduct].combinations).each(function(k, attribute) {
            $sizeList.append('<option value="' + attribute.id_product_attribute + '">' + attribute.attribute_name + '</option>');
        });
        $sizeList.selectBox('refresh');

        // Set price
        flockingPrices.productPrice = personalizableProducts[choosenProduct].price;

        // Set picture
        $('.picture-personalization').attr('src', personalizableProducts[selectedProductId].image);

        // $('#floc_shirt_choice').trigger('change');
    }

    function setSponsorExclusion(choosenProduct)
    {
        if (typeof exclusionSponsorIds == 'undefined') {
            return;
        }

        if (in_array(choosenProduct, exclusionSponsorIds)) {
            $('#sponsor_1').parents('.radio').trigger('click');
            $('#sponsor_0').parents('.radio-inline').hide().attr('disabled', 'disabled');
        } else {
            $('#sponsor_0').parents('.radio-inline').show().removeAttr('disabled');
        }
    }
};


/**
 * Prototype global personalization
 */
var globalPersonalization = function() {

    // Init
    initPersonnalization();

    if ('undefined' !== typeof shirtsMapping && undefined !== shirtsMapping[selectedProductId]) {
        selectedProduct = shirtsMapping[selectedProductId];
    }

    // LFP logo toggle
    $(document).on('change', '#floc_lfp_logo_choice', function () {

        if (1 == $(this).val()) {
            flockingPrices.lfpLogoPrice = parseFloat($(this).data('price'));
        } else {
            flockingPrices.lfpLogoPrice = 0;
        }

        setPersonalizationPrice();
    });

    // Short flocking choice
    $(document).on('change', '#floc_short_choice', function () {

        if (1 == $(this).val()) {
            $('#flocking-short-row').stop(true, true).slideDown();

            flockingPrices.flockingShortPrice = parseFloat($(this).data('price'));
        } else {
            $('#flocking-short-row').stop(true, true).slideUp();

            flockingPrices.flockingShortPrice = 0;
            cleanVisualization();
        }

        setPersonalizationPrice();
    });

    // Shirt flocking choice
    // $(document).on('change', '#floc_shirt_choice', function () {

        // if (1 == $(this).val()) {
            $('#flocking-shirt-row').stop(true, true).slideDown();

            flockingPrices.flockingPrice = parseFloat($(this).data('price'));
            $('.flocking-type-toggle:checked').trigger('change');
        // } else {
        //     $('#flocking-shirt-row').stop(true, true).slideUp();

        //     flockingPrices.flockingPrice = 0;
        //     cleanVisualization();
        // }

        setPersonalizationPrice();
    // });

    // Shirt flocking type choice
    $(document).on('change', '.flocking-type-toggle', function () {

        var flockingId = $(this).val();
        var $flockingRow = $('#flocking-type-' + flockingId);

        if (!$flockingRow.length) {
            return false;
        }

        // Clear choices
        if ($(this).attr('id') == 'flocking_type_toggle_player') {
            $('#floc_shirt_custom_name, #floc_shirt_custom_number, #floc_shirt_custom_value').attr('disabled', true).val('');
            $('#floc_shirt_player_value').removeAttr('disabled');
            $('#floc_shirt_player_value').trigger('change');

        } else if ($(this).attr('id') == 'flocking_type_toggle_custom') {
            $('#floc_shirt_player_value').attr('disabled', true).val('');
            cleanVisualization();
            $('#floc_shirt_custom_name, #floc_shirt_custom_number, #floc_shirt_custom_value').removeAttr('disabled');
            if ($('#floc_shirt_custom_name').val() != '') {
                $('#floc_shirt_custom_name').trigger('keyup');
            }
            if ($('#floc_shirt_custom_number').val() != '') {
                $('#floc_shirt_custom_number').trigger('keyup');
            }
        }

        $('.flocking-field').hide();
        $flockingRow.stop(true, true).slideDown();
    });

    // Shirt flocking custom value
    $(document).on('keyup', '#floc_shirt_custom_name', function (e) {

        var val = $(this).val();
        if (null !== val.match(/[^a-zA-Z.ùÙé É-]/g)) {
            $(this).val(val.substr(0, val.length - 1));
        }
        var content = $(this).val().toUpperCase();
        content = content.replace('\'', '');
        content = content.replace('É', 'E');
        content = content.replace('Ù', 'U');
        $(this).val(content);

        fillCustomPersonalization();
        displayFlocageName(content);
    });
    $(document).on('keyup', '#floc_shirt_custom_number', function () {
        var val = $(this).val();
        if (null !== val.match(/[^0-9]/g)) {
            val = val.substr(0, val.length - 1);
            $(this).val(val);
        }

        fillCustomPersonalization();
        displayFlocageNum(val);
    });

    $(document).on('change', '#floc_shirt_player_value', function() {
        var player = $(this).val();
        if (player == null) {
            player = $(this).find('option').eq(0).val();
        }
        var tab_player = player.split(separatorPersonalizationName);

        if (tab_player.length != 2) {
            return false;
        }
        displayFlocageName(tab_player[1]);
        displayFlocageNum(tab_player[0]);
    });

    function displayFlocageName(name)
    {
        block_flocage_name.show();

        var lettre = '';
        var nb_lettre = 0;
        var max_width = 260;
        var max_margin = 5;
        block_flocage_name.empty();

        if (name.length > 0) {
            for (var i = 0; i < name.length; i++) {

                var height_var = '';

                if (name[i] == ".") {
                    lettre = 'POINT';
                }
                else if (name[i] == "Ú") {
                    lettre = 'U-AVEC-ACCENT';
                    height_var = '24px';
                }
                else if (name[i] == "É") {
                    lettre = 'E-AVEC-ACCENT';
                    height_var = '23px';
                }
                else if (name[i] == "-") {
                    lettre = 'TIRET';
                }
                else {
                    lettre = name[i].toUpperCase();
                }

                if (lettre != ' ' && null !== selectedProduct) {
                    var img_object = $('<img src="' + flocageUrl + selectedProduct['img_flocking'] + '/' + lettre + '.png">');
                    if (lettre == 'TIRET') {
                        img_object.attr('width', '8px').attr('style', 'margin:2px;');
                    } else if(lettre == 'POINT') {
                        img_object.attr('width', '9px').attr('style', 'margin-right:2px; top: 4px;');
                    }
                } else {
                    img_object = "&nbsp;&nbsp;";
                }
                if (height_var != '') {
                    img_object.attr('height', height_var);
                }
                block_flocage_name.append(img_object);

                nb_lettre++;
            }
        }
    }

    function displayFlocageNum(numero)
    {
        if (null === selectedProduct) {
            return;
        }

        var chiffre = '';
        block_flocage_num.empty();
        if (numero.length > 0) {
            for (var i = 0; i < numero.length; i++) {
                chiffre = numero[i];
                block_flocage_num.append('<img src="'+flocageUrl+selectedProduct['img_flocking']+'/'+chiffre+'.png" >');
            }
        }
    }

    function fillCustomPersonalization()
    {
        $('#floc_shirt_custom_value').val($('#floc_shirt_custom_number').val()+separatorPersonalizationName+$('#floc_shirt_custom_name').val());
    }
};

/**
 * Set personalization price
 */
var setPersonalizationPrice = function() {

    var totalPrice = false;

    if ('undefined' !== typeof flockingPrices) {
        totalPrice = parseFloat(flockingPrices.productPrice + flockingPrices.lfpLogoPrice + flockingPrices.flockingPrice + flockingPrices.flockingShortPrice);

        if (totalPrice < 0) {
            totalPrice = false;
        }
    }

    var $pricePersonalization = $('.price-personalization');
    if (!totalPrice) {
        return $pricePersonalization.hide();
    }

    return $pricePersonalization.html(formatCurrency(totalPrice, currencyFormat, currencySign, currencyBlank)).show();
};

var checkNQPersonalization = function() {
    if ($('#visualization').length > 0 && $('input[name="has_personalization"]:checked').length > 0) {
        if ((($('#flocking_type_toggle_player:checked').length == 0 && $('#floc_shirt_custom_name:visible').length == 0) || ($('#floc_shirt_custom_name:visible').length > 0 && ($('#floc_shirt_custom_name').val() == '' || $('#floc_shirt_custom_number').val() == '')))) {

            if ($('#flocking_type_toggle_player:checked').length == 0 && $('#floc_shirt_custom_name:visible').length == 0) {
                $('#uniform-flocking_type_toggle_player').next('label').attr('style', 'color:red;');
                $('#uniform-flocking_type_toggle_custom').next('label').attr('style', 'color:red;');
            } else {
                $('#uniform-flocking_type_toggle_player').next('label').attr('style', '');
                $('#uniform-flocking_type_toggle_custom').next('label').attr('style', '');

                if ($('#floc_shirt_custom_name').val() == '') {
                    $('#floc_shirt_custom_name').attr('style', 'border:1px solid red;');
                } else {
                    $('#floc_shirt_custom_name').attr('style', '');
                }

                if ($('#floc_shirt_custom_number').val() == '') {
                    $('#floc_shirt_custom_number').attr('style', 'border:1px solid red;');
                } else {
                    $('#floc_shirt_custom_number').attr('style', '');
                }
            }

            return false;
        } else {
            $('#uniform-flocking_type_toggle_player').next('label').attr('style', '');
            $('#uniform-flocking_type_toggle_custom').next('label').attr('style', '');
            $('#floc_shirt_custom_name').attr('style', '');
            $('#floc_shirt_custom_number').attr('style', '');
        }
    }

    return true;
};