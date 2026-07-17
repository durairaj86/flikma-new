// Flikma marketing site — small progressive enhancements.
// Bootstrap's own bundle already handles the navbar toggle/dropdowns;
// this only adds the extras that plain Bootstrap doesn't.
document.addEventListener('DOMContentLoaded', function () {
    // Animated count-up for the stats section numbers (12K+, 40+, 98%, 24/7).
    var statNodes = document.querySelectorAll('.stat-box h2');
    if (statNodes.length && 'IntersectionObserver' in window) {
        var animate = function (el) {
            var text = el.textContent.trim();
            var match = text.match(/^([\d.]+)(.*)$/);
            if (!match) return;
            var target = parseFloat(match[1]);
            var suffix = match[2];
            var duration = 900;
            var start = null;

            function step(ts) {
                if (!start) start = ts;
                var progress = Math.min((ts - start) / duration, 1);
                var value = target * progress;
                el.textContent = (Number.isInteger(target) ? Math.floor(value) : value.toFixed(1)) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        };

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        statNodes.forEach(function (el) { observer.observe(el); });
    }
});
