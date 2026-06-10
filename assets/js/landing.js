/* ============================================================
   LANDING PAGE JS — landing.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  // ── Navbar scroll effect ──
  const nav = document.getElementById('mainNav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 60);
  }, { passive: true });

  // ── Animated Counter ──
  function animateCounter(el) {
    const target  = parseInt(el.getAttribute('data-target'), 10);
    const suffix  = el.getAttribute('data-suffix') || '';
    const duration = 2000;
    const step     = target / (duration / 16);
    let current    = 0;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = Math.floor(current).toLocaleString() + suffix;
    }, 16);
  }

  const counterEls = document.querySelectorAll('[data-target]');
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting && !e.target.dataset.animated) {
        e.target.dataset.animated = 'true';
        animateCounter(e.target);
      }
    });
  }, { threshold: 0.5 });
  counterEls.forEach(el => counterObserver.observe(el));

  // ── Testimonial Auto-Carousel ──
  const cards      = document.querySelectorAll('.testimonial-card');
  const dots       = document.querySelectorAll('.t-dot');
  let   tIndex     = 0;
  let   tInterval;

  function showTestimonial(idx) {
    cards.forEach((c, i) => {
      c.style.display = i === idx ? 'block' : 'none';
    });
    dots.forEach((d, i) => {
      d.classList.toggle('active', i === idx);
    });
    tIndex = idx;
  }

  function startAutoPlay() {
    tInterval = setInterval(() => {
      showTestimonial((tIndex + 1) % cards.length);
    }, 5000);
  }

  if (cards.length > 1) {
    // Initialize – handled by Bootstrap carousel if used, else manual
  }

  // ── Contact Form ──
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', e => {
      e.preventDefault();
      const btn  = contactForm.querySelector('[type="submit"]');
      const orig = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending…';
      btn.disabled  = true;
      setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Message Sent!';
        btn.style.background = 'linear-gradient(135deg,#43A047,#1B5E20)';
        contactForm.reset();
        setTimeout(() => {
          btn.innerHTML = orig;
          btn.style.background = '';
          btn.disabled = false;
        }, 3500);
      }, 1800);
    });
  }

  // ── Hero CTA ripple ──
  document.querySelectorAll('.hero-btn-ripple').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const circle = document.createElement('span');
      const rect   = this.getBoundingClientRect();
      circle.style.cssText = `
        position:absolute;width:1px;height:1px;border-radius:50%;
        background:rgba(255,255,255,0.4);
        transform:scale(0);animation:ripple 0.6s linear;
        left:${e.clientX - rect.left}px;top:${e.clientY - rect.top}px;
      `;
      this.style.position = 'relative';
      this.style.overflow = 'hidden';
      this.appendChild(circle);
      setTimeout(() => circle.remove(), 700);
    });
  });

  // Add ripple keyframe dynamically
  const style = document.createElement('style');
  style.textContent = '@keyframes ripple{to{transform:scale(200);opacity:0}}';
  document.head.appendChild(style);

  // ── Parallax hero blobs ──
  window.addEventListener('scroll', () => {
    const scrollY = window.scrollY;
    document.querySelectorAll('.particle').forEach((p, i) => {
      const speed = 0.05 + i * 0.02;
      p.style.transform = `translateY(${scrollY * speed}px)`;
    });
  }, { passive: true });

});
