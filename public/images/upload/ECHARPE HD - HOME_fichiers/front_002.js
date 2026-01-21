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
    if($('.product-description > .ets-wishlist-button-add').length)
    {
        $('.product-description >.ets-wishlist-button-add').each(function(){
            $(this).parent().before($(this).clone());
            $(this).remove();
        });
    }
    $(document).on('click','.ets-wishlist-button-add.add_wishlist',function(e){
        e.preventDefault();
        $('.ets-wishlist-add-to .ets-wishlist-modal').removeClass('fade').addClass('show');
        $('#ets_wl_id_product_current').val($(this).data('id-product'));
        $('#ets_wl_id_product_attribute_current').val($(this).data('id-product-attribute'));
    });
    $(document).on('click','button[data-dismiss="modal"]',function(){
       $(this).parents('.ets-wishlist-modal').addClass('fade').removeClass('show'); 
       $('.quickview').removeClass('ets-hidden');
    });
    $(document).on('click','.ets-wishlist-add-to-new',function(){
        $('.ets-wishlist-create .ets-wishlist-modal').removeClass('fade').addClass('show');
        $('.quickview').addClass('ets-hidden');
        $('.ets-wishlist-add-to .ets-wishlist-modal').addClass('fade').removeClass('show');
        $('#wishlist_name').val('')
        
    });
    $(document).on('click','#wishlist_name',function(){
        setTimeout(function(){$('#wishlist_name').focus()},300);
    })
    $(document).on('click','.ets-wishlist-button-add.login',function(){
        $('.ets-wishlist-login .ets-wishlist-modal').removeClass('fade').addClass('show');
    });
    $(document).on('click','.ets-btn-submit-new-wishlist',function(){
        ets_wishlist.addNewWishlist();
    });
    $(document).on('click','.ets-wishlist-chooselist .item-wishlist',function(){
       ets_wishlist.addProductToWishlist($(this).data('id'),$('#ets_wl_id_product_current').val(),$('#ets_wl_id_product_attribute_current').val()); 
    });
    $(document).on('click','.ets-wishlist-button-add.delete_wishlist,.btn_delete_wishlist',function(e){
        e.preventDefault();
        ets_wishlist.deleteProductFromWishlist($(this));
    });
    $(document).on("click",'.ets-wishlist-list-item-actions',function(){
        $(this).next().next('.dropdown-menu').toggleClass('show');
    });
    $(document).mouseup(function (e)
    {
        if($('.buttons-share-wishlist.show').length)
        {
            var container_dropdown = $('.buttons-share-wishlist.show');
            if (!container_dropdown.is(e.target)&& container_dropdown.has(e.target).length === 0)
            {
                container_dropdown.removeClass('show');
            }
        }
    }); 
    $(document).on('click','.tbn-shareurl-wishlist',function(){
        $(this).parent().find('.buttons-share-wishlist').toggleClass('show');
    });
    $(document).on('click','.btn-copy-url-share',function(e){
        e.preventDefault();
        $(this).next('input').select();
        document.execCommand("copy");
        $('.ets-wishlist-toast .ets-wishlist-toast-text').html($('.buttons-share-wishlist').attr('data-text-copi'));
        $('.ets-wishlist-toast').removeClass('error').addClass('success').addClass('isActive');
        setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);
        $('.buttons-share-wishlist').removeClass('show')
    });
    $(document).on('click','.tbn-rename-wishlist',function(){
        $('.ets-wishlist-rename .ets-wishlist-modal').removeClass('fade').addClass('show');
        $('#id_wishlist_edit').val($(this).parents('.ets-wishlist-list-item').attr('data-id'));
        $('#wishlist_name_edit').val($(this).parents('.ets-wishlist-list-item').attr('data-name'));
    });
    $(document).on('click','.btn-submit-rename-wishlist',function(){
        ets_wishlist.renameWishlist();
    });
    $(document).on('click','.ets-wishlist-btn-delete',function(){
        ets_wishlist.deleteWishlist($(this).data('url-delete'));
    });
    $(document).on('click','.ets-wishlist-product-addtocart',function(){
        ets_wishlist.addToCart($(this)); 
    });
    $(document ).ajaxComplete(function( event, xhr, settings ) {
        if($('.product-add-to-cart .ets-wishlist-button-add').length &&  xhr.responseText && xhr.responseText.indexOf("product_add_to_cart")>=0)
        {
            var data = JSON.parse(xhr.responseText);
            if(data.product_add_to_cart)
            {
                $('.product-actions .product-add-to-cart').replaceWith(data.product_add_to_cart);
            }
        }
    });
});
ets_wishlist = {
    addNewWishlist : function (){
        var wishlist_name = $('#wishlist_name').val();
        if(!wishlist_name)
        {
            $('.ets-wishlist-toast .ets-wishlist-toast-text').html($('.ets-wishlist-toast').data('required'));
            $('.ets-wishlist-toast').removeClass('success').addClass('error').addClass('isActive');
            setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);
        }
        else
        {
            var ajax_url = $('.ets-wishlist-create').data('url');
            $.ajax({
                url: ajax_url,
                data: 'wishlist_name='+wishlist_name,
                type: 'post',
                dataType: 'json',
                success: function(json){
                    $('.ets-wishlist-toast .ets-wishlist-toast-text').html(json.message);
                    if(json.success)
                    {
                        $('.ets-wishlist-toast').removeClass('error').addClass('success').addClass('isActive');
                        $('.ets-wishlist-create .ets-wishlist-modal').removeClass('show').addClass('fade');
                        $('.quickview').removeClass('ets-hidden');
                        if($('#module-ets_wishlist_pres17-lists').length)
                        {
                            var li_item ='<tr class="ets-wishlist-list-item" data-id="'+json.datas.id_ets_wishlist+'" data-name="'+json.datas.name+'">';
                            li_item +='<td><a class="ets-wishlist-list-item-link" href="'+json.datas.listUrl+'">';
                            li_item +='<p class="ets-wishlist-list-item-title"><span class="wishlist_name">'+json.datas.name+'</span></p>';
                            li_item += '</a></td>';
                            li_item +='<td>--</td>';
                            li_item +='<td class="ets-wishlist-list-item-right">';
                                li_item +='<a title="'+View_text+'" class="link-view-wishlist" href="'+json.datas.shareUrl+'">';
                                li_item +='<i><svg style="width:14px; height:14px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M279.6 160.4C282.4 160.1 285.2 160 288 160C341 160 384 202.1 384 256C384 309 341 352 288 352C234.1 352 192 309 192 256C192 253.2 192.1 250.4 192.4 247.6C201.7 252.1 212.5 256 224 256C259.3 256 288 227.3 288 192C288 180.5 284.1 169.7 279.6 160.4zM480.6 112.6C527.4 156 558.7 207.1 573.5 243.7C576.8 251.6 576.8 260.4 573.5 268.3C558.7 304 527.4 355.1 480.6 399.4C433.5 443.2 368.8 480 288 480C207.2 480 142.5 443.2 95.42 399.4C48.62 355.1 17.34 304 2.461 268.3C-.8205 260.4-.8205 251.6 2.461 243.7C17.34 207.1 48.62 156 95.42 112.6C142.5 68.84 207.2 32 288 32C368.8 32 433.5 68.84 480.6 112.6V112.6zM288 112C208.5 112 144 176.5 144 256C144 335.5 208.5 400 288 400C367.5 400 432 335.5 432 256C432 176.5 367.5 112 288 112z"/></svg></i>';
                                li_item +='</a>';
                                li_item +='<div class="ets_wl_share_social">';
                                    li_item +='<button title="'+Share_text+'" class="tbn-shareurl-wishlist" data-shareurl="'+json.datas.shareUrl+'">';
                                        li_item +='<i><svg style="width:14px; height:14px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M448 127.1C448 181 405 223.1 352 223.1C326.1 223.1 302.6 213.8 285.4 197.1L191.3 244.1C191.8 248 191.1 251.1 191.1 256C191.1 260 191.8 263.1 191.3 267.9L285.4 314.9C302.6 298.2 326.1 288 352 288C405 288 448 330.1 448 384C448 437 405 480 352 480C298.1 480 256 437 256 384C256 379.1 256.2 376 256.7 372.1L162.6 325.1C145.4 341.8 121.9 352 96 352C42.98 352 0 309 0 256C0 202.1 42.98 160 96 160C121.9 160 145.4 170.2 162.6 186.9L256.7 139.9C256.2 135.1 256 132 256 128C256 74.98 298.1 32 352 32C405 32 448 74.98 448 128L448 127.1zM95.1 287.1C113.7 287.1 127.1 273.7 127.1 255.1C127.1 238.3 113.7 223.1 95.1 223.1C78.33 223.1 63.1 238.3 63.1 255.1C63.1 273.7 78.33 287.1 95.1 287.1zM352 95.1C334.3 95.1 320 110.3 320 127.1C320 145.7 334.3 159.1 352 159.1C369.7 159.1 384 145.7 384 127.1C384 110.3 369.7 95.1 352 95.1zM352 416C369.7 416 384 401.7 384 384C384 366.3 369.7 352 352 352C334.3 352 320 366.3 320 384C320 401.7 334.3 416 352 416z"/></svg></i>';
                                    li_item +='</button>';
                                    li_item +='<ul class="buttons-share-wishlist" style="">';
                                        li_item +='<li class="facebook">'
                                            li_item +='<a class="" href="https://www.facebook.com/sharer.php?u='+json.datas.shareUrl+'" title="Share" target="_blank" rel="noopener noreferrer">';
                                                li_item += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg>';
                                            li_item +='</a>';
                                        li_item +='</li>';
                                        li_item +='<li class="twitter">'
                                            li_item +='<a class="" href="https://twitter.com/intent/tweet?text='+json.datas.name+' '+json.datas.shareUrl+'" title="X" target="_blank" rel="noopener noreferrer">';
                                                li_item += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 462.799"><path d="M403.229 0h78.506L310.219 196.04 512 462.799H354.002L230.261 301.007 88.669 462.799h-78.56l183.455-209.683L0 0h161.999l111.856 147.88L403.229 0zm-27.556 415.805h43.505L138.363 44.527h-46.68l283.99 371.278z"/></svg>';
                                            li_item +='</a>';
                                        li_item +='</li>';
                                        li_item +='<li class="pinterest">'
                                            li_item +='<a class="" href="https://www.pinterest.com/pin/create/button/?url='+json.datas.shareUrl+'" title="Pinterest" target="_blank" rel="noopener noreferrer">';
                                                li_item += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"><path d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59 8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9 150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3.8-3.4 5-20.3 6.9-28.1.6-2.5.3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6 0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8 7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29 123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z"/></svg>';
                                            li_item +='</a>';
                                        li_item +='</li>';
                                        li_item +='<li class="copylink">'
                                            li_item +='<a title="'+Copy_link_text+'" href="#" class="btn-copy-url-share" type="button">';
                                                li_item += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M172.5 131.1C228.1 75.51 320.5 75.51 376.1 131.1C426.1 181.1 433.5 260.8 392.4 318.3L391.3 319.9C381 334.2 361 337.6 346.7 327.3C332.3 317 328.9 297 339.2 282.7L340.3 281.1C363.2 249 359.6 205.1 331.7 177.2C300.3 145.8 249.2 145.8 217.7 177.2L105.5 289.5C73.99 320.1 73.99 372 105.5 403.5C133.3 431.4 177.3 435 209.3 412.1L210.9 410.1C225.3 400.7 245.3 404 255.5 418.4C265.8 432.8 262.5 452.8 248.1 463.1L246.5 464.2C188.1 505.3 110.2 498.7 60.21 448.8C3.741 392.3 3.741 300.7 60.21 244.3L172.5 131.1zM467.5 380C411 436.5 319.5 436.5 263 380C213 330 206.5 251.2 247.6 193.7L248.7 192.1C258.1 177.8 278.1 174.4 293.3 184.7C307.7 194.1 311.1 214.1 300.8 229.3L299.7 230.9C276.8 262.1 280.4 306.9 308.3 334.8C339.7 366.2 390.8 366.2 422.3 334.8L534.5 222.5C566 191 566 139.1 534.5 108.5C506.7 80.63 462.7 76.99 430.7 99.9L429.1 101C414.7 111.3 394.7 107.1 384.5 93.58C374.2 79.2 377.5 59.21 391.9 48.94L393.5 47.82C451 6.731 529.8 13.25 579.8 63.24C636.3 119.7 636.3 211.3 579.8 267.7L467.5 380z"/></svg>';
                                            li_item +='</a>';
                                            li_item +='<input name="share_link_wishlist" type="text" value="'+json.datas.shareUrl+'" style="opacity:0;width:1px"  />';
                                        li_item +='</li>';
                                    li_item +='</ul>';
                                li_item +='</div>';
                                li_item +='<button class="tbn-rename-wishlist" title="'+Rename_text+'">'
                                        li_item +='<i><svg style="width:14px; height:14px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M490.3 40.4C512.2 62.27 512.2 97.73 490.3 119.6L460.3 149.7L362.3 51.72L392.4 21.66C414.3-.2135 449.7-.2135 471.6 21.66L490.3 40.4zM172.4 241.7L339.7 74.34L437.7 172.3L270.3 339.6C264.2 345.8 256.7 350.4 248.4 353.2L159.6 382.8C150.1 385.6 141.5 383.4 135 376.1C128.6 370.5 126.4 361 129.2 352.4L158.8 263.6C161.6 255.3 166.2 247.8 172.4 241.7V241.7zM192 63.1C209.7 63.1 224 78.33 224 95.1C224 113.7 209.7 127.1 192 127.1H96C78.33 127.1 64 142.3 64 159.1V416C64 433.7 78.33 448 96 448H352C369.7 448 384 433.7 384 416V319.1C384 302.3 398.3 287.1 416 287.1C433.7 287.1 448 302.3 448 319.1V416C448 469 405 512 352 512H96C42.98 512 0 469 0 416V159.1C0 106.1 42.98 63.1 96 63.1H192z"/></svg></i>';
                                li_item +='</button>';
                                li_item +='<button title="'+Delete_text+'" class="ets-wishlist-btn-delete" data-url-delete="'+json.datas.deleteUrl+'">';
                                    li_item +='<i><svg style="width:14px; height:14px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM394.8 466.1C393.2 492.3 372.3 512 346.9 512H101.1C75.75 512 54.77 492.3 53.19 466.1L31.1 128H416L394.8 466.1z"/></svg></i>';
                                li_item +='</button>';
                            li_item += '</td>';   
                            li_item +='</tr>';
                            $('.ets-wishlist-container .ets-wishlist-list').append(li_item);
                        }
                        else
                        {
                            $('.ets-wishlist-add-to .ets-wishlist-modal').addClass('show').removeClass('fade');
                            $('.ets-wishlist-chooselist .ets-wishlist-list').append('<li class="item-wishlist" data-id="'+json.datas.id_ets_wishlist+'"><p>'+json.datas.name+'</p></li>');
                        }
                        
                    }
                    else
                    {
                        $('.ets-wishlist-toast').removeClass('success').addClass('error').addClass('isActive');
                    }
                    setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);    
                },
                error: function(xhr, status, error)
                {     
                    
                }
            });
        } 
    },
    addProductToWishlist: function(idWishlist,idProduct,idProductAttribute){
        var ajax_url = $('.ets-wishlist-add-to').data('url');
        $.ajax({
            url: ajax_url,
            data: {
                id_wishlist: idWishlist,
                id_product : idProduct,
                id_product_attribute : idProductAttribute,
            },
            type: 'post',
            dataType: 'json',
            success: function(json){
                $('.ets-wishlist-toast .ets-wishlist-toast-text').html(json.message);
                if(json.success)
                {
                    $('.ets-wishlist-toast').removeClass('error').addClass('success').addClass('isActive');
                    $('.ets-wishlist-button-add_'+idProduct+'_'+idProductAttribute+'').addClass('delete_wishlist').removeClass('add_wishlist');
                    $('.ets-wishlist-button-add_'+idProduct+'_'+idProductAttribute+'').attr('data-id_wishlist',idWishlist);
                    $('.ets-wishlist-add-to .ets-wishlist-modal').removeClass('show').addClass('fade');
                }
                else
                {
                    $('.ets-wishlist-toast').removeClass('success').addClass('error').addClass('isActive');
                }
                setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);    
            },
            error: function(xhr, status, error)
            {     
                
            }
        });
    },
    deleteProductFromWishlist:function($button)
    {
        if(!$button.hasClass('view_page') || confirm(confirm_delete_product_text))
        {
            var ajax_url = $button.data('url');
            var idProduct = parseInt($button.data('id-product'));
            var idProductAttribute = parseInt($button.data('id-product-attribute'));
            var idWishlist = parseInt($button.attr('data-id_wishlist'));
            if(!$button.hasClass('loading'))
            {
                $button.addClass('loading');
                $.ajax({
                    url: ajax_url,
                    data: {
                        action:'deleteProductFromWishlist',
                        id_product : idProduct,
                        id_product_attribute : idProductAttribute,
                        id_wishlist:idWishlist,
                        viewpage: $button.hasClass('view_page') ? true :false
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function(json){
                        $button.removeClass('loading');
                        $('.ets-wishlist-toast .ets-wishlist-toast-text').html(json.message);
                        if(json.success)
                        {
                            $('.ets-wishlist-toast').removeClass('error').addClass('success').addClass('isActive');
                            if($button.hasClass('view_page'))
                            {
                                $('.ets-wishlist-products-container').html(json.products_list);
                            }
                            else
                                $('.ets-wishlist-button-add_'+idProduct+'_'+idProductAttribute+'').removeClass('delete_wishlist').addClass('add_wishlist');
                        }
                        else
                        {
                            $('.ets-wishlist-toast').removeClass('success').addClass('error').addClass('isActive');
                        }
                        setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);    
                    },
                    error: function(xhr, status, error)
                    {     
                        
                    }
                });
            }
        }
    },
    renameWishlist :function(){
        var wishlist_name = $('#wishlist_name_edit').val();
        var idWishlist = $('#id_wishlist_edit').val();
        if(!wishlist_name)
        {
            $('.ets-wishlist-toast .ets-wishlist-toast-text').html($('.ets-wishlist-toast').data('required'));
            $('.ets-wishlist-toast').removeClass('success').addClass('error').addClass('isActive');
            setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);
        }
        else
        {
            var ajax_url = $('.ets-wishlist-rename').data('url');
            $.ajax({
                url: ajax_url,
                data: 'wishlist_name='+wishlist_name+'&id_wishlist='+idWishlist,
                type: 'post',
                dataType: 'json',
                success: function(json){
                    $('.ets-wishlist-toast .ets-wishlist-toast-text').html(json.message);
                    if(json.success)
                    {
                        $('.ets-wishlist-toast').removeClass('error').addClass('success').addClass('isActive');
                        $('.ets-wishlist-rename .ets-wishlist-modal').removeClass('show').addClass('fade');
                        $('.ets-wishlist-list-item[data-id="'+json.datas.id_ets_wishlist+'"] .wishlist_name').html(json.datas.name);
                        $('.ets-wishlist-list-item[data-id="'+json.datas.id_ets_wishlist+'"]').attr('data-name',json.datas.name);
                    }
                    else
                    {
                        $('.ets-wishlist-toast').removeClass('success').addClass('error').addClass('isActive');
                    }
                    setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);    
                },
                error: function(xhr, status, error)
                {     
                    
                }
            });
        }
    },
    deleteWishlist : function(url_delete)
    {
        if(confirm(confirm_delete_wishlist_text))
        {
            $.ajax({
                url: url_delete,
                data: '',
                type: 'post',
                dataType: 'json',
                success: function(json){
                    $('.ets-wishlist-toast .ets-wishlist-toast-text').html(json.message);
                    if(json.success)
                    {
                        $('.ets-wishlist-toast').removeClass('error').addClass('success').addClass('isActive');
                        $('.ets-wishlist-list-item[data-id="'+json.datas.id_ets_wishlist+'"]').remove();
                    }
                    else
                    {
                        $('.ets-wishlist-toast').removeClass('success').addClass('error').addClass('isActive');
                    }
                    setTimeout(function(){$('.ets-wishlist-toast').removeClass('isActive');},3000);    
                },
                error: function(xhr, status, error)
                {     
                    
                }
            });
        }
    },
    addToCart: function($button)
    {
        if(!$button.hasClass('loading'))
        {
            $button.addClass('loading');
            var url_link = $button.data('link');
            $.ajax({
                url: url_link,
                data: 'action=update&ajax=1',
                type: 'post',
                dataType: 'json',                
                success: function(json){ 
                    $button.removeClass('loading');
                    if(json.hasError && json.errors)
                    {
                        alert(json.errors[0]);
                    }
                    else
                    {
                        ets_wishlist.addProductToCart(json.id_product,json.id_product_attribute,$button.data('id_wishlist'));
                        prestashop.emit("updateCart", {
                            reason: {
                                idProduct: json.id_product,
                                idProductAttribute: json.id_product_attribute,
                                idCustomization: 0,
                                linkAction: "add-to-cart",
                                cart: json.cart
                            },
                            resp: json
                        });
                    }
                    
                }
            });
        }
    },
    addProductToCart: function(idProduct,idProductAttribute,idWishlist){
        $.ajax({
            url: addProductToCartUrl,
            data: {
                id_product : idProduct,
                id_product_attribute : idProductAttribute,
                idWishlist : idWishlist
            },
            type: 'post',
            dataType: 'json',
            success: function(json){
                   
            },
            error: function(xhr, status, error)
            {     
                
            }
        });
    },
}