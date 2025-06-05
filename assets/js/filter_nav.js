document.addEventListener('DOMContentLoaded', function() {
    // Get all filter buttons and product cards
    const filterButtons = document.querySelectorAll('.filter-button');
    const productCards = document.querySelectorAll('.product-card');
    
    // Add click event listeners to each filter button
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const filterValue = this.textContent.trim();
            
            // Filter products based on the selected category
            productCards.forEach(card => {
                const productCategory = card.querySelector('p').textContent.replace('|', '').trim();
                
                if (filterValue === 'All' || productCategory === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});