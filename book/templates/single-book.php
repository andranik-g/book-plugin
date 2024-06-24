<?php get_header(); ?>

<main class="main-page single-book">
    <div class="container">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="row">
                    <div class="col-md-8">

                        <?php
                        if (have_posts()) :
                            while (have_posts()) :
                                the_post(); ?>
                                <div class="row">
                                    <div class="col-md-5">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="single-img">
                                                <?php the_post_thumbnail(); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php

                                        $publication_year = get_post_meta(get_the_ID(), '_book_publication_year', true);
                                        $genre = get_post_meta(get_the_ID(), '_book_genre', true);
                                        $isbn = get_post_meta(get_the_ID(), '_book_isbn', true);
                                        ?>

                                        <div class="book-meta">

                                            <?php if (!empty($isbn)) : ?>
                                                <p><strong>ISBN:</strong> <?php echo esc_html($isbn); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($publication_year)) : ?>
                                                <p><strong>Publication Year:</strong> <?php echo esc_html($publication_year); ?></p>
                                            <?php endif; ?>


                                            <?php
                                            // Get genres (custom taxonomy terms) assigned to the current post
                                            $genres = get_the_terms(get_the_ID(), 'book_genre');

                                            if ($genres && !is_wp_error($genres)) :
                                            ?>


                                                <p><strong>Genre:</strong>
                                                    <?php foreach ($genres as $genre) : ?>
                                                        <a href="<?php echo esc_url(get_term_link($genre)); ?>"><?php echo esc_html($genre->name); ?></a>
                                                    <?php endforeach; ?>
                                                </p>

                                            <?php endif; ?>

                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <h1 class="fw-semibold h2 mb-3"><?php the_title(); ?></h1>
                                        <?php $author = get_post_meta(get_the_ID(), '_book_author', true); ?>
                                        <span class="d-block mb-5">by <?= $author ?></span>
                                        <?php the_content(); ?>
                                    </div>
                                </div>


                        <?php
                            endwhile;
                        endif; ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        // Query related articles
                        $related_args = array(
                            'post_type' => 'book',
                            'posts_per_page' => 4, // Adjust the number of related posts to display
                            'post__not_in' => array(get_the_ID()), // Exclude the current post
                            'orderby' => 'rand', // Order by random to change the order on each page load
                        );

                        $related_query = new WP_Query($related_args);

                        // Output related articles
                        if ($related_query->have_posts()) :
                        ?>
                            <div class="side-widget related-articles">
                                <div class="related-posts">
                                    <h3 class="h3 fw-semibold mb-50">Releated Books</h3>

                                    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                        <div class="list-related books">
                                            <div class="img-list">
                                                <?php the_post_thumbnail(); ?>
                                            </div>
                                            <div class="title-list-block fw-medium">
                                                <h5><?php the_title(); ?></h5>
                                                <a href="<?php the_permalink(); ?>">more <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                                        <path d="M5.95246 1.43771L6.64613 0.726013C6.93985 0.424662 7.4148 0.424662 7.70539 0.726013L13.7797 6.955C14.0734 7.25635 14.0734 7.74365 13.7797 8.04179L7.70539 14.274C7.41167 14.5753 6.93673 14.5753 6.64613 14.274L5.95246 13.5623C5.65562 13.2577 5.66187 12.7608 5.96496 12.4627L9.73016 8.78235H0.749916C0.334338 8.78235 0 8.43932 0 8.01294V6.98706C0 6.56068 0.334338 6.21766 0.749916 6.21766H9.73016L5.96496 2.53733C5.65874 2.23918 5.65249 1.74227 5.95246 1.43771Z" fill="#4CE0D7" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>

                                </div>
                            </div>
                        <?php
                            // Restore original post data
                            wp_reset_postdata();
                        endif;
                        ?>
                    </div>
                </div>
                <?php
                // Get the current post ID
                $current_post_id = get_the_ID();

                // Get the terms (genres) associated with the current post
                $terms = get_the_terms($current_post_id, 'book_genre');

                if ($terms && !is_wp_error($terms)) {
                    $term_ids = array();
                    foreach ($terms as $term) {
                        $term_ids[] = $term->term_id;
                    }

                    // Query arguments to fetch related books
                    $args = array(
                        'post_type' => 'book', // Adjust post type if necessary
                        'posts_per_page' => 4, // Number of related books to display
                        'post__not_in' => array($current_post_id), // Exclude the current post
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'book_genre',
                                'field' => 'term_id',
                                'terms' => $term_ids,
                                'operator' => 'IN',
                            ),
                        ),
                    );

                    // Query for related books
                    $related_books = new WP_Query($args);

                    // Output related books
                    if ($related_books->have_posts()) :
                ?>
                        <div class="related-books">
                            <h3 class="fw-semibold">You might also enjoy</h3>
                            <div class="row">
                                <?php while ($related_books->have_posts()) : $related_books->the_post(); ?>
                                    <div class="col-md-3">
                                        <?php include(plugin_dir_path(__FILE__) . 'book-card.php'); ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                <?php
                        // Restore original post data
                        wp_reset_postdata();
                    endif;
                }
                ?>

            </div>

        </div>
    </div>
</main>
<?php get_footer(); ?>