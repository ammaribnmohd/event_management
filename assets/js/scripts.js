// Add date validation
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.min = new Date().toISOString().split('T')[0];
    });
});

// Confirm delete
function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this event?')) {
        event.preventDefault();
    }
}