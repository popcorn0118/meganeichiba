( function () {

    var thumbs   = document.querySelectorAll( '.product-gallery-thumb' );
    var mainItems = document.querySelectorAll( '.product-gallery-main-item' );

    if ( ! thumbs.length ) {
        return;
    }

    thumbs.forEach( function ( thumb ) {

        thumb.addEventListener( 'click', function () {

            var index = parseInt( this.dataset.index, 10 );

            mainItems.forEach( function ( item ) {
                item.classList.toggle( 'active', parseInt( item.dataset.index, 10 ) === index );
            } );

            thumbs.forEach( function ( t ) {
                t.classList.toggle( 'active', parseInt( t.dataset.index, 10 ) === index );
            } );
        } );
    } );

} )();
