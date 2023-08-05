<?php
/*
 * Plugin Name: Bookshelf
 * Description: A plugin for managing books.
 * Version: 1.0.0
 * Author: Siya
 */
//bookshelf.php

function bookshelf_register_post_type()
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
        'parent_item_colon' => 'Parent Book:',
        'menu_name' => 'Bookshelf'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'publicly_queryable' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'book'),
        'capability_type' => 'post',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
        'menu_icon' => 'dashicons-book-alt'
    );

    register_post_type('book', $args);
}
add_action('init', 'bookshelf_register_post_type');


// Add a book to the bookshelf
function bookshelf_add_book($title, $author, $genre, $publication_year, $cover_image, $description, $price)
{
    // Validate required fields
    if (empty($title) || empty($author) || empty($genre) || empty($publication_year)) {
        return new WP_Error('missing_data', 'Required fields are missing.');
    }

    $book_data = array(
        'post_title' => $title,
        'post_type' => 'book',
        'post_status' => 'publish'
    );

    $book_id = wp_insert_post($book_data);

    if ($book_id) {
        // Save book details as metadata
        update_post_meta($book_id, 'author', $author);
        update_post_meta($book_id, 'genre', $genre);
        update_post_meta($book_id, 'publication_year', $publication_year);
        update_post_meta($book_id, 'cover_image', $cover_image);
        update_post_meta($book_id, 'description', $description);

        // Validate and sanitize the price before saving
        $sanitized_price = floatval($price);
        if ($sanitized_price >= 0) {
            update_post_meta($book_id, 'price', $sanitized_price);
        } else {
            return new WP_Error('invalid_price', 'Price must be a non-negative number.');
        }

        return $book_id;
    }

    return new WP_Error('insert_failed', 'Failed to insert book.');
}


// Get the user's book collection
function bookshelf_get_books($user_id)
{
    $args = array(
        'post_type' => 'book',
        'posts_per_page' => -1,
        'author' => $user_id
    );

    $book_query = new WP_Query($args);

    if ($book_query->have_posts()) {
        $books = array();

        while ($book_query->have_posts()) {
            $book_query->the_post();

            $book = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'author' => get_post_meta(get_the_ID(), 'author', true),
                'genre' => get_post_meta(get_the_ID(), 'genre', true),
                'publication_year' => get_post_meta(get_the_ID(), 'publication_year', true),
                'cover_image' => get_post_meta(get_the_ID(), 'cover_image', true),
                'description' => get_post_meta(get_the_ID(), 'description', true)
            );

            $books[] = $book;
        }

        wp_reset_postdata();

        return $books;
    }

    return false;
}

// Add a book to the wishlist
function bookshelf_add_to_wishlist($user_id, $book_id)
{
    // Check if the book is already in the wishlist
    $wishlist = get_user_meta($user_id, 'wishlist', true);

    if (!in_array($book_id, $wishlist)) {
        // Add the book to the wishlist post type
        $wishlist_post_id = wp_insert_post(
            array(
                'post_type' => 'wishlist_book',
                'post_title' => $book_id,
                // Use book ID as title
                'post_status' => 'publish',
                'post_author' => $user_id
            )
        );

        if ($wishlist_post_id) {
            // Update the user's wishlist
            $wishlist[] = $book_id;
            update_user_meta($user_id, 'wishlist', $wishlist);

            return true;
        }
    }

    return false;
}


// Remove a book from the wishlist
function bookshelf_remove_from_wishlist($user_id, $book_id)
{
    $wishlist = get_user_meta($user_id, 'wishlist', true);

    $updated_wishlist = array_diff($wishlist, array($book_id));

    update_user_meta($user_id, 'wishlist', $updated_wishlist);

    return $updated_wishlist;
}

//display the wishlist
function bookshelf_display_wishlist($user_id)
{
    $wishlist = get_user_meta($user_id, 'wishlist', true);

    if (!empty($wishlist)) {
        $books = array();

        foreach ($wishlist as $book_id) {
            $book = array(
                'id' => $book_id,
                'title' => get_the_title($book_id),
                'author' => get_post_meta($book_id, 'author', true),
                'genre' => get_post_meta($book_id, 'genre', true),
                'publication_year' => get_post_meta($book_id, 'publication_year', true),
                'cover_image' => get_post_meta($book_id, 'cover_image', true),
                'description' => get_post_meta($book_id, 'description', true),
                'price' => get_post_meta($book_id, 'price', true)
            );

            $books[] = $book;
        }

        return $books;
    }

    return false;
}

//frontend display
function bookshelf_display_wishlist_shortcode($atts)
{
    ob_start();

    $user_id = get_current_user_id();
    $wishlist = bookshelf_display_wishlist($user_id);

    if ($wishlist) {
        echo '<div class="row">';

        foreach ($wishlist as $book) {
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card">';
            echo '<img src="' . esc_url($book['cover_image']) . '" class="card-img-top" alt="' . esc_attr($book['title']) . '">'; // Display cover image
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . esc_html($book['title']) . '</h5>'; // Display title
            echo '<p class="card-text"><strong>Author:</strong> ' . esc_html($book['author']) . '</p>'; // Display author
            echo '<p class="card-text"><strong>Genre:</strong> ' . esc_html($book['genre']) . '</p>'; // Display genre
            echo '<p class="card-text"><strong>Publication Year:</strong> ' . esc_html($book['publication_year']) . '</p>'; // Display publication year
            echo '<p class="card-text">' . esc_html($book['description']) . '</p>'; // Display description
            echo '<p class="card-text"><strong>Price:</strong> $' . esc_html($book['price']) . '</p>'; // Display price

            // Add a button to remove book from wishlist
            echo '<button class="btn btn-danger remove-from-wishlist" data-book-id="' . esc_attr($book['id']) . '">Remove from Wishlist</button>';

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';

        // JavaScript to handle AJAX removal from wishlist
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo '$(".remove-from-wishlist").on("click", function() {';
        echo 'var bookId = $(this).data("book-id");';
        echo '$.ajax({';
        echo 'url: "' . admin_url('admin-ajax.php') . '",';
        echo 'method: "POST",';
        echo 'data: {';
        echo 'action: "remove_book_from_wishlist",';
        echo 'user_id: ' . $user_id . ',';
        echo 'book_id: bookId,';
        echo '_ajax_nonce: bookshelf_ajax_object.ajax_nonce';
        echo '},';
        echo 'success: function(response) {';
        echo 'if (response.success) {';
        echo '$(this).closest(".card").fadeOut();';
        echo '} else {';
        echo 'console.error(response.data.message);';
        echo '}';
        echo '}';
        echo '});';
        echo '});';
        echo '});';
        echo '</script>';
    } else {
        echo 'Your wishlist is empty.';
    }

    return ob_get_clean();
}
add_shortcode('bookshelf_wishlist', 'bookshelf_display_wishlist_shortcode');



function bookshelf_enqueue_scripts()
{
    wp_enqueue_style('bookshelf-frontend-css', plugins_url('css/bookshelf-frontend.css', __FILE__));
    wp_enqueue_script('bookshelf-frontend-js', plugins_url('js/bookshelf-frontend.js', __FILE__), array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'bookshelf_enqueue_scripts');


require_once(plugin_dir_path(__FILE__) . 'add-to-wishlist.php');
require_once(plugin_dir_path(__FILE__) . 'bookshelf-template.php');

function bookshelf_localize_ajax_object()
{
    wp_localize_script(
        'bookshelf-frontend-js',
        'bookshelf_ajax_object',
        array('ajax_nonce' => wp_create_nonce('your_ajax_nonce'))
    );
}
add_action('wp_enqueue_scripts', 'bookshelf_localize_ajax_object');

function bookshelf_register_wishlist_post_type()
{
    $labels = array(
        'name' => 'Wishlist Books',
        'singular_name' => 'Wishlist Book',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Wishlist Book',
        // ... other labels ...
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        // Not publicly accessible
        'show_ui' => true,
        'capability_type' => 'post',
        'supports' => array('title'),
        'menu_icon' => 'dashicons-heart'
    );

    register_post_type('wishlist_book', $args);
}
add_action('init', 'bookshelf_register_wishlist_post_type');

function bookshelf_handle_wishlist_form()
{
    if (isset($_POST['add_to_wishlist'])) {
        $book_id = intval($_POST['book_id']);
        $user_id = get_current_user_id();

        if ($user_id && $book_id) {
            $added = bookshelf_add_to_wishlist($user_id, $book_id);

            if ($added) {
                // Book added to wishlist
                $message = 'Book added to wishlist successfully.';
            } else {
                // Failed to add book to wishlist
                $error_message = 'Failed to add book to wishlist.';
            }
        }
    }
}
add_action('init', 'bookshelf_handle_wishlist_form');