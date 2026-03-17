document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting)
            {
                if (!entry.target.classList.contains('visible'))
                {
                    entry.target.classList.add('visible');
                }

                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.05,
        rootMargin: '-75px 0px',
    });

    document.querySelectorAll('.scroll-show-area').forEach(item => {
        observer.observe(item);
    });
});