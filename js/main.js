// ===== NAVBAR SCROLL =====
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 60);
});

// ===== BURGER MENU =====
const burger = document.getElementById('burger');
const navLinks = document.getElementById('navLinks');
if (burger) {
  burger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    burger.classList.toggle('active');
  });
}

// ===== MODAL SYSTEM =====
const leadModal = document.getElementById('leadModal');
const privacyModal = document.getElementById('privacyModal');
const openModalBtns = document.querySelectorAll('.btn-open-modal');
const closeModalBtn = document.getElementById('closeModal');
const closePrivacyBtn = document.getElementById('closePrivacy');
const privacyBtn = document.getElementById('privacyBtn');

// Open Lead Modal
openModalBtns.forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const project = btn.getAttribute('data-project');
    if (project) {
      document.getElementById('project').value = project;
    }
    leadModal.classList.add('open');
  });
});

// Close Lead Modal
if (closeModalBtn) {
  closeModalBtn.addEventListener('click', () => {
    leadModal.classList.remove('open');
  });
}

// Privacy Modal
if (privacyBtn) {
  privacyBtn.addEventListener('click', () => {
    privacyModal.classList.add('open');
  });
}
if (closePrivacyBtn) {
  closePrivacyBtn.addEventListener('click', () => {
    privacyModal.classList.remove('open');
  });
}

// Close on outside click
window.addEventListener('click', (e) => {
  if (e.target === leadModal) leadModal.classList.remove('open');
  if (e.target === privacyModal) privacyModal.classList.remove('open');
});

// ===== SCROLL REVEAL =====
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach((el, i) => {
  el.style.transitionDelay = `${(i % 5) * 0.1}s`;
  revealObserver.observe(el);
});

// ===== LEAD FORM SUBMISSION =====
const form = document.getElementById('leadForm');
const submitBtn = document.getElementById('submitBtn');
const formSuccess = document.getElementById('formSuccess');

if (form) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = form.name.value.trim();
    const phone = form.phone.value.trim();
    const project = form.project.value;

    if (!name || name.length < 2) { showToast('Please enter your full name.', 'error'); return; }
    if (!phone || !/^[\+]?[\d\s\-()]{7,15}$/.test(phone)) { showToast('Please enter a valid phone number.', 'error'); return; }

    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline';
    submitBtn.disabled = true;

    const data = new FormData(form);

    try {
      const response = await fetch('backend/submit_lead.php', {
        method: 'POST',
        body: data
      });
      const result = await response.json();

      if (result.success) {
        form.style.display = 'none';
        formSuccess.style.display = 'block';
        showToast('🎉 Enquiry submitted successfully!');
      } else {
        showToast(result.message || 'Error occurred.', 'error');
      }
    } catch (err) {
      showToast('Network error. Please try again.', 'error');
    } finally {
      btnText.style.display = 'inline';
      btnLoader.style.display = 'none';
      submitBtn.disabled = false;
    }
  });
}

// ===== TOAST =====
function showToast(msg, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = msg;
  toast.style.background = type === 'error' ? '#e53e3e' : '#00d4b1';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 4000);
}
