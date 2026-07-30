document.addEventListener('DOMContentLoaded', function () {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        document.querySelectorAll('.reveal').forEach(function (el) {
            el.classList.add('active');
        });
        return;
    }

    const reveals = document.querySelectorAll('.reveal');

    const observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        },
        { threshold: 0.1 }
    );

    reveals.forEach(function (el) {
        observer.observe(el);
    });
});
