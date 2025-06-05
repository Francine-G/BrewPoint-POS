document.querySelectorAll('.eye button').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        // Get data attributes
        const title = btn.getAttribute('data-title') || '';
        const table = btn.getAttribute('data-table') || '';
        // Set modal content
        document.querySelector('#inventoryModal h2').textContent = title;
        document.getElementById('modal-table').innerHTML = table;
        // Show modal
        document.getElementById('inventoryModal').style.display = 'block';
    });
});

// Close modal on close button click
document.getElementById('closeModalBtn').onclick = function() {
    document.getElementById('inventoryModal').style.display = 'none';
};