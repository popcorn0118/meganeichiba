<?php
/* =================================
   首頁 - 最新消息列表
   "<?php require get_theme_file_path( 'inc/custom-news-list.php' ); ?>"
   "[astra_custom_layout id=2284]"
================================== */

if ( ! defined( 'ABSPATH' ) ) exit;

$q = new WP_Query([
    'post_type'           => 'post',
    'posts_per_page'      => 5,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
]);
?>

<div class="products-news-list">

    <?php if ( $q->have_posts() ) : ?>
        <ul class="products-news-list__items">

            <?php while ( $q->have_posts() ) : $q->the_post(); ?>
                <li class="products-news-list__item">

                    <div class="products-news-list__link">

                        <time class="products-news-list__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                            <?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?>
                        </time>

                        <span class="products-news-list__title">
                            <?php the_title(); ?>
                        </span>

                    </div>

                </li>
            <?php endwhile; ?>

        </ul>
    <?php endif; ?>

</div>

<?php
wp_reset_postdata();
?>