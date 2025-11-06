// ============================================
// MODAL HANDLING FOR ADDING PETS
// ============================================

// Open modal when "Add pet" button is clicked
const addPetBtn = document.getElementById('add-pet-btn');
if (addPetBtn) {
    addPetBtn.addEventListener('click', function() {
        const modal = document.getElementById('add-pet-modal-overlay');
        if (modal) {
            modal.style.display = 'flex';
        }
    });
}

// Open modal when "Add another pet" button is clicked
const addPetBtnList = document.getElementById('add-pet-btn-list');
if (addPetBtnList) {
    addPetBtnList.addEventListener('click', function() {
        const modal = document.getElementById('add-pet-modal-overlay');
        if (modal) {
            modal.style.display = 'flex';
        }
    });
}

// Close modal when close button is clicked
const petModalCloseBtn = document.getElementById('pet-modal-close-btn');
if (petModalCloseBtn) {
    petModalCloseBtn.addEventListener('click', function() {
        const modal = document.getElementById('add-pet-modal-overlay');
        if (modal) {
            modal.style.display = 'none';
        }
    });
}

// Close modal when clicking outside the modal content
const addPetModalOverlay = document.getElementById('add-pet-modal-overlay');
if (addPetModalOverlay) {
    addPetModalOverlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
}

// ============================================
// ROLE SWITCH MODAL
// ============================================

function openRoleSwitchModal() {
    // Get current role from PHP (this will be injected by PHP)
    const currentRole = '<?php echo $host_data["sitter_role"] ?? "boarder"; ?>';
    const newRole = currentRole === 'house_sitter' ? 'boarder' : 'house_sitter';
    const roleName = newRole === 'house_sitter' ? 'House Sitter' : 'Boarder';
    
    const confirmMessage = `Switch to ${roleName}?\n\n` +
        (newRole === 'house_sitter' ? 
            'Note: House Sitters require DBS check verification.' : 
            'You will switch to Boarder role.');
    
    if (confirm(confirmMessage)) {
        fetch('process_switch_sitter_role.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'new_role=' + encodeURIComponent(newRole)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Network error:', err);
            alert('Network error. Please try again.');
        });
    }
}

// ============================================
// DELETE ACCOUNT MODAL
// ============================================

function openDeleteModal() {
    const confirmation = prompt(
        'WARNING: This will permanently delete your account and all data.\n\n' +
        'This includes:\n' +
        '• Your profile and personal information\n' +
        '• All pet information\n' +
        '• Booking history\n' +
        '• Messages and reviews\n\n' +
        'Type DELETE (in capitals) to confirm:'
    );
    
    if (confirmation === 'DELETE') {
        if (confirm('Are you absolutely sure? This cannot be undone.')) {
            fetch('process_delete_account.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'confirmation=' + encodeURIComponent(confirmation)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = data.redirect || 'index.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Network error:', err);
                alert('Network error. Please try again.');
            });
        }
    } else if (confirmation !== null) {
        alert('Confirmation failed. Account not deleted.');
    }
}

// ============================================
// HANDLE BOOKING ACTIONS (FOR SITTERS)
// ============================================

function handleBookingAction(bookingId, action) {
    const actionText = action === 'accept' ? 'accept' : 'decline';
    
    if (confirm(`Are you sure you want to ${actionText} this booking?`)) {
        fetch('process_booking_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `booking_id=${bookingId}&action=${action}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Network error:', err);
            alert('Network error. Please try again.');
        });
    }
}

// ============================================
// AUTO-HIDE SUCCESS/ERROR MESSAGES
// ============================================

window.addEventListener('DOMContentLoaded', function() {
    const alertMessages = document.querySelectorAll('.alert-message');
    alertMessages.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// ============================================
// PREVENT FORM RESUBMISSION ON REFRESH
// ============================================

if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}