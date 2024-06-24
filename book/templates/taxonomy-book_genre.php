<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<main class="main-page">
    <div class="container">
        <?php $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => 'book',
            'posts_per_page' => 8,
            'paged' => $paged,
            'tax_query' => array(
                array(
                    'taxonomy' => 'book_genre',
                    'field' => 'slug',
                    'terms' => get_queried_object()->slug, // Get the current taxonomy term slug
                ),
            ),
        );

        $custom_query = new WP_Query($args);

        if ($custom_query->have_posts()) : ?>
            <?php the_archive_title('<h1 class="fw-bold text-center h3 mb-50">', '</h1>'); ?>
            <div class="col-md-10 offset-md-1">
                <div class="row gx-5">
                    <?php while ($custom_query->have_posts()) : $custom_query->the_post(); ?>
                        <div class="col-md-3">
                            <?php include(plugin_dir_path(__FILE__) . 'book-card.php'); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php custom_pagination($custom_query); ?>
            </div>
        <?php else : ?>
            <?php get_template_part('template-parts/content', 'none'); ?>
        <?php endif; ?>
    </div>
</main><!-- #main -->
<?php get_footer(); ?>