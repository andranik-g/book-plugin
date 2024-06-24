<?php
/*
Plugin Name: Book Plugin
Description: Custom plugin for managing books.
Version: 1.0
Author: Andranik Grigoryan
*/
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue the necessary scripts
function my_book_plugin_enqueue_scripts()
{
    wp_enqueue_script('book-list-ajax', plugins_url('/js/book-list-ajax.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('book-list-ajax', 'bookListAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'my_book_plugin_enqueue_scripts');
// Register custom post type 'Book'
function my_book_plugin_register_book_post_type()
{
    $labels = array(
        'name' => 'Books',
        'singular_name' => 'Book',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Book',
        'edit_item' => 'Edit Book',
        'new_item' => 'New Book',
        'view_item' => 'View Book',
        'search_items' => 'Search Books',
        'not_found' => 'No books found',
        'not_found_in_trash' => 'No books found in trash',
        'parent_item_colon' => '',
        'menu_name' => 'Books'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-book-alt', // Icon for the menu
        'supports' => array('title', 'editor', "thumbnail",),
        'taxonomies' => array('book_genre'),
    );

    register_post_type('book', $args);
}
add_action('init', 'my_book_plugin_register_book_post_type');

// Register custom taxonomy 'Book Genre'
function my_book_plugin_register_book_genre_taxonomy()
{
    $labels = array(
        'name' => 'Book Genres',
        'singular_name' => 'Book Genre',
        'search_items' => 'Search Book Genres',
        'all_items' => 'All Book Genres',
        'parent_item' => 'Parent Book Genre',
        'parent_item_colon' => 'Parent Book Genre:',
        'edit_item' => 'Edit Book Genre',
        'update_item' => 'Update Book Genre',
        'add_new_item' => 'Add New Book Genre',
        'new_item_name' => 'New Book Genre Name',
        'menu_name' => 'Book Genres',
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'book-genre'),
    );

    register_taxonomy('book_genre', 'book', $args);
}
add_action('init', 'my_book_plugin_register_book_genre_taxonomy');

function my_book_plugin_genre_template($template)
{
    if (is_tax('book_genre')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/taxonomy-book_genre.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'my_book_plugin_genre_template');

function my_book_plugin_single_book_template($template)
{
    if (is_singular('book')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-book.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'my_book_plugin_single_book_template');


// Add custom meta box for 'Book' post type
function my_book_plugin_book_meta_box()
{
    add_meta_box(
        'my_book_plugin_book_meta_box',
        'Book Details',
        'my_book_plugin_render_book_meta_box',
        'book',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'my_book_plugin_book_meta_box');

function my_book_plugin_render_book_meta_box($post)
{
    // Retrieve current values for fields
    $author = get_post_meta($post->ID, '_book_author', true);
    $publication_year = get_post_meta($post->ID, '_book_publication_year', true);
    $genre = get_post_meta($post->ID, '_book_genre', true);
    $isbn = get_post_meta($post->ID, '_book_isbn', true);

    // Output fields
?>
    <p>
        <label for="book_author">Author:</label>
        <input type="text" id="book_author" name="book_author" value="<?php echo esc_attr($author); ?>" />
    </p>
    <p>
        <label for="book_isbn">ISBN:</label>
        <input type="text" id="book_isbn" name="book_isbn" value="<?php echo esc_attr($isbn); ?>" />
    </p>

    <p>
        <label for="book_publication_year">Publication Year:</label>
        <input type="text" id="book_publication_year" name="book_publication_year" value="<?php echo esc_attr($publication_year); ?>" />
    </p>


<?php
}

// Save custom meta box data
function my_book_plugin_save_book_meta_data($post_id)
{
    if (isset($_POST['book_author'])) {
        update_post_meta($post_id, '_book_author', sanitize_text_field($_POST['book_author']));
    }
    if (isset($_POST['book_publication_year'])) {
        update_post_meta($post_id, '_book_publication_year', sanitize_text_field($_POST['book_publication_year']));
    }
    if (isset($_POST['book_genre'])) {
        update_post_meta($post_id, '_book_genre', sanitize_text_field($_POST['book_genre']));
    }
    if (isset($_POST['book_isbn'])) {
        update_post_meta($post_id, '_book_isbn', sanitize_text_field($_POST['book_isbn']));
    }
}
add_action('save_post', 'my_book_plugin_save_book_meta_data');

//Shortcodes //


// Shortcode for AJAX loading
function book_list_shortcode_ajax($atts)
{
    $atts = shortcode_atts(
        array(
            'title' => 'Books', // Default title if not provided
        ),
        $atts,
        'book_list_ajax' // Shortcode name
    );
    ob_start();
?>
    <div class="shortcode-book">
        <div class="col-md-10 offset-md-1">
            <h2 class="text-center h3 fw-bold mb-50"><?php echo esc_html($atts['title']); ?></h2>
            <div id="book-list">
                <?php echo my_book_plugin_get_books_html(4); ?>
            </div>
            <button id="load-more-books" class="fw-semibold">More Books <i class="fas fa-spinner fa-spin"></i></button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('book_list_ajax', 'book_list_shortcode_ajax');

// Shortcode for pagination
function book_list_shortcode_pagination()
{

    global $paged;
    if (get_query_var('paged')) {
        $paged = get_query_var('paged');
    } elseif (get_query_var('page')) {
        $paged = get_query_var('page');
    } else {
        $paged = 1;
    }

    $args = array(
        'post_type' => 'book',
        'posts_per_page' => 8,
        'paged' => $paged
    );
    $query = new WP_Query($args);
    ob_start();
    if ($query->have_posts()) { ?>
        <div class="col-md-10 offset-md-1">
            <div id="book-list">
                <div class="row gx-5">
                    <?php while ($query->have_posts()) {
                        $query->the_post();
                    ?>
                        <div class="col-md-3">
                            <?php include(plugin_dir_path(__FILE__) . 'templates/book-card.php'); ?>
                        </div>

                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php custom_pagination($query); ?>
        </div>
    <?php
    }
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('book_list_pagination', 'book_list_shortcode_pagination');

// AJAX handler function
function my_book_plugin_load_more_books()
{
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $books_html = my_book_plugin_get_books_html(4, $offset);
    echo $books_html;
    wp_die();
}
add_action('wp_ajax_load_more_books', 'my_book_plugin_load_more_books');
add_action('wp_ajax_nopriv_load_more_books', 'my_book_plugin_load_more_books');

// Helper function to get books HTML
function my_book_plugin_get_books_html($number_of_books = 4, $offset = 0)
{
    $args = array(
        'post_type' => 'book',
        'posts_per_page' => $number_of_books,
        'offset' => $offset
    );
    $query = new WP_Query($args);
    ob_start();
    if ($query->have_posts()) { ?>
        <div class="row gx-5">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
            ?>

                <div class="col-md-3">
                    <?php include(plugin_dir_path(__FILE__) . 'templates/book-card.php'); ?>
                </div>

            <?php
            } ?>
        </div>
<?php }
    wp_reset_postdata();
    return ob_get_clean();
}
