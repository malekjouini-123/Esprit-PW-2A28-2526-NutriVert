// Nutrivert App.js — Micro-interactions & UX
document.addEventListener('DOMContentLoaded', () => {

  // Delete confirmations
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm('Are you sure you want to delete this item?')) e.preventDefault();
    });
  });

  // Auto-dismiss flash messages after 3 seconds
  document.querySelectorAll('.flash-message').forEach(flash => {
    setTimeout(() => {
      flash.classList.add('fade-out');
      flash.addEventListener('animationend', () => flash.remove());
    }, 3000);
  });

  // Smooth scroll for anchor links (skip bare "#" placeholders)
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (!href || href === '#') return; // skip dummy placeholder links
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // Staggered card entrance animation
  const cards = document.querySelectorAll('.card-rich, .stat-card-vivid');
  cards.forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    setTimeout(() => {
      card.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, 80 * i);
  });

  // Table row hover sound-like feedback (subtle scale)
  document.querySelectorAll('tr').forEach(row => {
    row.style.transition = 'all 0.2s ease';
  });

});
