// Add Pet Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const addPetModal = document.getElementById('add-pet-modal-overlay');
    const addPetBtn = document.getElementById('add-pet-btn');
    const closeModalBtn = document.getElementById('pet-modal-close-btn');

    function openModal() {
        addPetModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Add the fade-in class after a small delay to trigger the animation
        setTimeout(() => {
            addPetModal.classList.add('modal-open');
        }, 10);
    }

    function closeModal() {
        addPetModal.classList.remove('modal-open');
        document.body.style.overflow = '';

        // Wait for the fade-out animation to complete before hiding
        setTimeout(() => {
            addPetModal.style.display = 'none';
        }, 300);
    }

    // Event Listeners
    if (addPetBtn) {
        addPetBtn.addEventListener('click', openModal);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking outside
    addPetModal.addEventListener('click', function(event) {
        if (event.target === addPetModal) {
            closeModal();
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && addPetModal.style.display === 'flex') {
            closeModal();
        }
    });

    // Form Handling
    const addPetForm = addPetModal.querySelector('form');
    if (addPetForm) {
        addPetForm.addEventListener('submit', function(event) {
            event.preventDefault();

            // Get form data
            const formData = new FormData(addPetForm);
            
            // Submit form using fetch
            fetch(addPetForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and refresh page to show new pet
                    closeModal();
                    location.reload();
                } else {
                    // Show error message
                    alert(data.message || 'An error occurred while adding your pet.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding your pet.');
            });
        });
    }
});

// Add animation classes
document.addEventListener('DOMContentLoaded', function() {
    // Find all verification items
    const verificationItems = document.querySelectorAll('.verification-item');
    verificationItems.forEach((item, index) => {
        // Add animation with delay
        setTimeout(() => {
            item.classList.add('fade-in-up');
        }, index * 100);
    });

    // Find all pet cards
    const petCards = document.querySelectorAll('.pet-card');
    petCards.forEach((card, index) => {
        // Add animation with delay
        setTimeout(() => {
            card.classList.add('fade-in-up');
        }, index * 100);
    });
});