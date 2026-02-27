/**
 * main.js
 * Global frontend JavaScript for Glo-CED India.
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- Contact Form (AJAX submit) ---
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(contactForm);
            const response = await fetch(contactForm.action, {
                method: 'POST',
                body:   formData,
            });

            const result = await response.json();
            alert(result.message || (result.success ? 'Sent!' : 'Error. Please try again.'));

            if (result.success) contactForm.reset();
        });
    }
});
