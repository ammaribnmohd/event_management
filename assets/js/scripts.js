document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.min = new Date().toISOString().split('T')[0];
    });

     // Add event listener for form submissions to prevent submission on validation error
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                 event.preventDefault();
                 event.stopPropagation();
            }
            this.classList.add('was-validated');
        }, false);
    });
});


// Confirm delete
function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this event?')) {
        event.preventDefault();
    }
}