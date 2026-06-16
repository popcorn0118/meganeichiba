( function () {

    var mainEl    = document.querySelector( '.product-gallery-main' );
    var thumbs    = document.querySelectorAll( '.product-gallery-thumb' );
    var mainItems = document.querySelectorAll( '.product-gallery-main-item' );
    var total     = mainItems.length;

    if ( ! total ) {
        return;
    }

    function getActiveIndex() {
        for ( var i = 0; i < total; i++ ) {
            if ( mainItems[ i ].classList.contains( 'active' ) ) {
                return i;
            }
        }
        return 0;
    }

    function goToSlide( index ) {
        index = ( ( index % total ) + total ) % total;

        mainItems.forEach( function ( item, i ) {
            item.classList.toggle( 'active', i === index );
        } );

        thumbs.forEach( function ( t, i ) {
            t.classList.toggle( 'active', i === index );
        } );
    }

    // 縮圖點擊
    thumbs.forEach( function ( thumb ) {
        thumb.addEventListener( 'click', function () {
            goToSlide( parseInt( this.dataset.index, 10 ) );
        } );
    } );

    // 拖曳 / 滑動切換（與列表輪播同邏輯）
    if ( ! mainEl || total < 2 ) {
        return;
    }

    var drag = null;

    mainEl.addEventListener( 'pointerdown', function ( e ) {
        drag = { startX: e.clientX, moved: false };
    } );

    document.addEventListener( 'pointermove', function ( e ) {
        if ( ! drag ) return;
        if ( Math.abs( e.clientX - drag.startX ) > 5 ) {
            drag.moved = true;
        }
    } );

    document.addEventListener( 'pointerup', function ( e ) {
        if ( ! drag ) return;
        var deltaX = e.clientX - drag.startX;
        if ( drag.moved && Math.abs( deltaX ) > 40 ) {
            goToSlide( getActiveIndex() + ( deltaX < 0 ? 1 : -1 ) );
        }
        drag = null;
    } );

    document.addEventListener( 'pointercancel', function () {
        drag = null;
    } );

} )();
