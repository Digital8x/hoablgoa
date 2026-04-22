// ===== PROJECT PAGE EXCLUSIVE LOGIC (V2) =====

document.addEventListener('DOMContentLoaded', () => {
    
  // 1. Sticky Project Nav Intersection
  const projectNav = document.getElementById('projectNav');
  if (projectNav) {
    window.addEventListener('scroll', () => {
      projectNav.classList.toggle('scrolled', window.scrollY > 400);
    });
  }

  // 2. Global Modal Functions for Project Pages
  const leadModal = document.getElementById('leadModal');
  const closeModal = document.getElementById('closeModal');
  const leadForm = document.getElementById('leadForm');
  const modalTitle = document.getElementById('modalTitle');
  const sourceItem = document.getElementById('sourceItem');

  // Function to trigger modal from elements
  window.openLeadModal = function(title = 'Register to View', item = 'General') {
    if (leadModal) {
      if (modalTitle) modalTitle.textContent = title;
      if (sourceItem) sourceItem.value = item;
      leadModal.classList.add('open');
    }
  };

  // Close modal logic
  if (closeModal) {
    closeModal.addEventListener('click', () => {
      leadModal.classList.remove('open');
    });
  }
  window.addEventListener('click', (e) => {
    if (e.target === leadModal) leadModal.classList.remove('open');
  });

  // Handle all .btn-open-modal elements on the page
  document.querySelectorAll('.btn-open-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const title = btn.getAttribute('data-title') || btn.innerText || 'Register Your Interest';
      const item = btn.getAttribute('data-item') || 'CTA Button';
      openLeadModal(title, item);
    });
  });

  // 3. Form Submission with Redirect to Thank You
  if (leadForm) {
    leadForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const submitBtn = leadForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerText;
      submitBtn.innerText = 'Verifying...';
      submitBtn.disabled = true;

      const formData = new FormData(leadForm);
      
      try {
        const response = await fetch('../backend/submit_lead.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        if (result.success) {
          // Success Redirect to Thank You Page
          window.location.href = '../thank-you.html';
        } else {
          alert('Submission Error: ' + (result.message || 'Please try again.'));
          submitBtn.innerText = originalText;
          submitBtn.disabled = false;
        }
      } catch (err) {
        console.error(err);
        // Fallback or demo redirect
        window.location.href = '../thank-you.html';
      }
    });
  }

  // 4. Smooth Scroll for Project Menu
  document.querySelectorAll('.project-menu a').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('href').substring(1);
      const targetEl = document.getElementById(targetId);
      if (targetEl) {
        window.scrollTo({
          top: targetEl.offsetTop - 150, // Offset for sticky navs
          behavior: 'smooth'
        });
      }
    });
  });
});
