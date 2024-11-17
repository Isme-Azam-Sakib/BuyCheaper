document.addEventListener('DOMContentLoaded', () => {
    const showMoreBtn = document.getElementById('show-more-btn');
    const hiddenBrands = document.querySelectorAll('.hidden-brand');

    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', () => {
            hiddenBrands.forEach(brand => brand.classList.toggle('hidden-brand'));
            showMoreBtn.innerHTML = showMoreBtn.innerHTML.includes('Show More') 
                ? 'Show Less <span>&#9650;</span>' 
                : 'Show More <span>&#9660;</span>';
        });
    }
});
