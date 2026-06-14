jQuery( function ( $ ) {

    var $lineup = $( '.brand-lineup' );

    if ( ! $lineup.length ) {
        return;
    }

    var brandId = $lineup.data( 'brand-id' );
    var $grid   = $lineup.find( '.brand-lineup-grid' );

    // 分類頁籤切換
    $lineup.on( 'click', '.brand-lineup-tab', function () {

        var $tab = $( this );

        if ( $tab.hasClass( 'active' ) || $grid.hasClass( 'is-loading' ) ) {
            return;
        }

        $lineup.find( '.brand-lineup-tab' ).removeClass( 'active' );
        $tab.addClass( 'active' );

        $grid.addClass( 'is-loading' );

        $.ajax( {
            url: BrandArchive.ajax_url,
            type: 'POST',
            data: {
                action: 'brand_lineup_filter',
                nonce: BrandArchive.nonce,
                brand_id: brandId,
                cat: $tab.data( 'cat' )
            }
        } ).done( function ( res ) {

            if ( res.success ) {
                $grid.html( res.data.html );
            }

        } ).always( function () {
            $grid.removeClass( 'is-loading' );
        } );

    } );

    // 商品圖片輪播（事件委派，支援 AJAX 換入的商品卡）
    $( document ).on( 'click', '.lineup-card-dot', function () {

        var $dot   = $( this );
        var index  = $dot.data( 'index' );
        var $images = $dot.closest( '.lineup-card-images' );

        $images.find( '.lineup-card-dot' ).removeClass( 'active' );
        $dot.addClass( 'active' );

        $images.find( '.lineup-card-image' ).removeClass( 'active' ).eq( index ).addClass( 'active' );

    } );

} );
