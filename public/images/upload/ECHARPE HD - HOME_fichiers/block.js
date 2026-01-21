/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

$(document).ready(function(){ 
    if($('.cart-grid-body .wishlist_products_list_section').length)
    {
        $('#main').append($('.cart-grid-body .wishlist_products_list_section').clone());
        $('.cart-grid-body .wishlist_products_list_section').remove();
    }
    ets_wlp_block.run_slick();
    $(document).on('change','select[name="select-wislist-products"]',function(){
        var ajax_url = $(this).data('link-action');
        var idWishlist = $(this).val();
        var position = $(this).data('position');
        var page_name =$(this).data('page-name');
        $('.wishlist_products_list_wrapper.'+position).addClass('loading');
        $.ajax({
            url: ajax_url,
            data: 'id_wishlist='+idWishlist+'&position='+position+'&ajax=1&page_name='+page_name,
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json.product_list)
                {
                    if($('.wishlist_products_list_wrapper.'+position).next('.ets_wlp_all_products').length)
                        $('.wishlist_products_list_wrapper.'+position).next('.ets_wlp_all_products').remove();
                    $('.wishlist_products_list_wrapper.'+position).replaceWith(json.product_list);
                    if($('.product-description > .ets-wishlist-button-add').length)
                    {
                        $('.product-description >.ets-wishlist-button-add').each(function(){
                            $(this).parent().before($(this).clone());
                            $(this).remove();
                        });
                    }
                    ets_wlp_block.run_slick();
                }   
            }
        });
    });
});
ets_wlp_block = {
    run_slick: function(){
        if($('.wishlist_products_list_wrapper.layout-slide:not(.slick-slider):not(.product_list_16)').length >0)
        {
            $('.wishlist_products_list_wrapper.layout-slide:not(.slick-slider):not(.product_list_16)').each(function(){
                var this_slick = $(this);
                var count_products = this_slick.find('.product-miniature').length;
                $(this).slick({
                      slidesToShow: count_products && count_products <  ets_wlp_nbItemsPerLine ? count_products :ets_wlp_nbItemsPerLine,
                      slidesToScroll: 1,
                      arrows: true,
                      autoplay:this_slick.hasClass('auto'),
                      responsive: [
                          {
                              breakpoint: 1199,
                              settings: {
                                  slidesToShow: count_products && count_products <  ets_wlp_nbItemsPerLine ? count_products :ets_wlp_nbItemsPerLine
                              }
                          },
                          {
                              breakpoint: 992,
                              settings: {
                                  slidesToShow: count_products && count_products <  ets_wlp_nbItemsPerLineTablet ? count_products :ets_wlp_nbItemsPerLineTablet
                              }
                          },
                          {
                              breakpoint: 768,
                              settings: {
                                  slidesToShow: count_products && count_products <  ets_wlp_nbItemsPerLineMobile ? count_products :ets_wlp_nbItemsPerLineMobile
                              }
                          },
                          {
                              breakpoint: 480,
                              settings: {
                                slidesToShow: 1
                              }
                          }
                       ]
                });
            });
       }
    }
}