<?php
// bookshelf-frontend.php

function bookshelf_frontend_display()
{
    $books = bookshelf_get_books(get_current_user_id());
    ?>
    <div class="bookshelf-container">
        <h2>Your Book Collection</h2>
        <?php if ($books): ?>
            <ul class="book-list">
                <?php foreach ($books as $book): ?>
                    <li>
                        <h3>
                            <?php echo $book['title']; ?>
                        </h3>
                        <p>Author:
                            <?php echo $book['author']; ?>
                        </p>
                        <p>Genre:
                            <?php echo $book['genre']; ?>
                        </p>
                        <p>Publication Year:
                            <?php echo $book['publication_year']; ?>
                        </p>
                        <p>Description:
                            <?php echo $book['description']; ?>
                        </p>
                        <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" />
                        <p>Price:
                            <?php echo get_post_meta($book['id'], 'price', true); ?>
                        </p>
                        <button class="add-to-wishlist" data-book-id="<?php echo $book['id']; ?>">Add to Wishlist</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No books found in your collection.</p>
        <?php endif; ?>

        <h2>Add New Book</h2>
        <form id="add-book-form">
            <div>
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" required>
            </div>
            <div>
                <label for="author">Author:</label>
                <input type="text" name="author" id="author">
            </div>
            <div>
                <label for="genre">Genre:</label>
                <input type="text" name="genre" id="genre">
            </div>
            <div>
                <label for="publication_year">Publication Year:</label>
                <input type="text" name="publication_year" id="publication_year">
            </div>
            <div>
                <label for="cover_image">Cover Image URL:</label>
                <input type="text" name="cover_image" id="cover_image">
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea name="description" id="description"></textarea>
            </div>
            <?php do_action('bookshelf_after_description_field'); ?>
            <div>
                <label for="price">Price:</label>
                <input type="text" name="price" id="price">
            </div>
            <button class="add-to-wishlist" data-book-id="1">Add to Wishlist</button>
            <button type="submit">Add Book</button>
        </form>
    </div>

    <script>
        // JavaScript code for handling AJAX request to add book to the wishlist
        document.addEventListener('DOMContentLoaded', function () {
            const addToWishlistButtons = document.querySelectorAll('.add-to-wishlist');

            addToWishlistButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const bookId = this.dataset.bookId;
                    addToWishlist(bookId);
                });
            });

            function addToWishlist(bookId) {

                const ajaxUrl = 'https://siaweb.tech/wp-admin/admin-ajax.php';

                // Replace 'your_action' with the actual action name for handling the AJAX request on the server side.
                const data = {
                    action: 'your_action',
                    book_id: bookId
                };

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: new URLSearchParams(data),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Book added to the wishlist successfully!');
                        } else {
                            alert('Failed to add book to the wishlist.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    </script>
    <?php
}

// Shortcode to display the bookshelf frontend
add_shortcode('bookshelf_frontend', 'bookshelf_frontend_display');

function bookshelf_display_wishlist_form()
{
    // Display a form for adding books to the wishlist
    ob_start();
    ?>
    <form id="wishlist-form" method="post">
        <label for="book_id">Select a book to add to wishlist:</label>
        <select name="book_id" id="book_id">
            <?php
            $books = bookshelf_get_all_books(); // You'll need to implement this function
            foreach ($books as $book) {
                echo '<option value="' . esc_attr($book['id']) . '">' . esc_html($book['title']) . '</option>';
            }
            ?>
        </select>

        <input type="submit" name="add_to_wishlist" value="Add to Wishlist">
    </form>

    <?php

    // Display success or error message
    if (isset($message)) {
        echo '<p class="success-message">' . esc_html($message) . '</p>';
    } elseif (isset($error_message)) {
        echo '<p class="error-message">' . esc_html($error_message) . '</p>';
    }
    return ob_get_clean();
}
add_shortcode('bookshelf_wishlist_form', 'bookshelf_display_wishlist_form');