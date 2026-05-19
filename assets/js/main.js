// Main JS File for GymPro

document.addEventListener('DOMContentLoaded', () => {
    console.log("GymPro Loaded Successfully!");
    
    // Smooth scrolling for landing page
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
