document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim().toLowerCase();
            debounceTimer = setTimeout(() => {
                const cards = document.querySelectorAll('.post-card');
                cards.forEach(card => {
                    const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
                    const excerpt = card.querySelector('.post-card-excerpt')?.textContent.toLowerCase() || '';
                    if (query === '' || title.includes(query) || excerpt.includes(query)) {
                        card.style.display = '';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => { card.style.display = 'none'; }, 300);
                    }
                });
            }, 300);
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.post-card').forEach(card => {
        observer.observe(card);
    });

    const header = document.querySelector('.header');
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll > 100) {
                header.style.boxShadow = '0 4px 30px rgba(0,0,0,0.3)';
            } else {
                header.style.boxShadow = 'none';
            }
            lastScroll = currentScroll;
        });
    }

    const postContent = document.querySelector('.post-content');
    const readingTimeEl = document.getElementById('readingTime');
    if (postContent && readingTimeEl) {
        const text = postContent.textContent;
        const words = text.split(/\s+/).length;
        const minutes = Math.ceil(words / 200);
        readingTimeEl.textContent = `${minutes} dk okuma`;
    }
});
