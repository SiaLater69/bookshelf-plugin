<?php
// ajax/add-to-wishlist.php

add_action('wp_ajax_your_action', 'bookshelf_ajax_add_to_wishlist');
add_action('wp_ajax_nopriv_your_action', 'bookshelf_ajax_add_to_wishlist');

function bookshelf_ajax_add_to_wishlist()
{
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to add a book to the wishlist.');
    }

    // Verify the nonce for security (optional, but recommended)
    if (!check_ajax_referer('your_ajax_nonce', 'security', false)) {
        wp_send_json_error('Invalid nonce.');
    }

    // Get the book ID from the AJAX request
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;

    // Add the book ID to the user's wishlist
    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'wishlist', true);

    if (!in_array($book_id, $wishlist)) {
        $wishlist[] = $book_id;
        update_user_meta($user_id, 'wishlist', $wishlist);

        // Get book details to include in the response
        $book_details = bookshelf_get_books($user_id);

        // Prepare the response data
        $response_data = array(
            'success' => true,
            'message' => 'Book added to the wishlist.',
            'book_details' => $book_details // Include the book details in the response
        );

        wp_send_json_success($response_data);
    } else {
        wp_send_json_error('Book is already in the wishlist.');
    }
}