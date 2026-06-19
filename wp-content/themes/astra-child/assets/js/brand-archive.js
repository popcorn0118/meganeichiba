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

    // URL hash (#cat=slug) 自動觸發對應分類頁籤（須在 click handler 註冊後才能 trigger）
    var hashMatch = window.location.hash.match( /^#cat=(.+)$/ );
    if ( hashMatch ) {
        var $hashTab = $lineup.find( '.brand-lineup-tab[data-cat="' + decodeURIComponent( hashMatch[1] ) + '"]' );
        if ( $hashTab.length && ! $hashTab.hasClass( 'active' ) ) {
            $hashTab.trigger( 'click' );
        }
    }

    // 商品圖片輪播：切換到指定 index
    function goToSlide( $images, index ) {

        var $slides = $images.find( '.lineup-card-image' );
        var $dots   = $images.find( '.lineup-card-dot' );
        var total   = $slides.length;

        if ( total < 2 ) {
            return;
        }

        index = ( ( index % total ) + total ) % total;

        $slides.removeClass( 'active' ).eq( index ).addClass( 'active' );
        $dots.removeClass( 'active' ).eq( index ).addClass( 'active' );

        // 同步商品連結的 ?color= 參數
        var $card      = $images.closest( '.lineup-card' );
        var permalink  = $card.data( 'permalink' );
        var color      = $dots.eq( index ).data( 'color' );

        if ( permalink ) {
            $card.find( '.lineup-card-link' ).attr(
                'href',
                color ? permalink + '?color=' + color : permalink
            );
        }
    }

    // dot 點擊切換（事件委派，支援 AJAX 換入的商品卡）
    $( document ).on( 'click', '.lineup-card-dot', function () {
        goToSlide( $( this ).closest( '.lineup-card-images' ), $( this ).data( 'index' ) );
    } );

    // 拖曳／滑動圖片切換（事件委派，支援 AJAX 換入的商品卡）
    var drag = null;

    $( document ).on( 'pointerdown', '.lineup-card-images', function ( e ) {

        var $images = $( this );

        if ( $images.find( '.lineup-card-image' ).length < 2 ) {
            return;
        }

        drag = {
            $images: $images,
            startX: e.originalEvent.clientX,
            moved: false
        };
    } );

    $( document ).on( 'pointermove', function ( e ) {

        if ( ! drag ) {
            return;
        }

        if ( Math.abs( e.originalEvent.clientX - drag.startX ) > 5 ) {
            drag.moved = true;
        }
    } );

    $( document ).on( 'pointerup pointercancel', function ( e ) {

        if ( ! drag ) {
            return;
        }

        var deltaX = e.originalEvent.clientX - drag.startX;

        if ( drag.moved && Math.abs( deltaX ) > 40 ) {

            var $images = drag.$images;
            var current = $images.find( '.lineup-card-image.active' ).index();

            goToSlide( $images, deltaX < 0 ? current + 1 : current - 1 );
        }

        drag = null;
    } );

} );
