// js/bookshelf-frontend.js

document.addEventListener('DOMContentLoaded', function() {
    const addToWishlistButtons = document.querySelectorAll('.add-to-wishlist');

    addToWishlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.dataset.bookId;
            addToWishlist(bookId);
        });
    });

    function addToWishlist(bookId) {
        console.log('addToWishlist function called');
        const ajaxUrl = 'https://siaweb.tech/wp-admin/admin-ajax.php';

        // Replace 'your_action' with the actual action name for handling the AJAX request on the server side.
        const data = {
            action: 'your_action',
            book_id: bookId,
            security: bookshelf_ajax_object.ajax_nonce // Use a variable passed from PHP for nonce security
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
