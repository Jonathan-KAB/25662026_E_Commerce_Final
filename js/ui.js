/* UI helpers shared across pages */
// Toggle password visibility
document.addEventListener('click', function (e) {
  const el = e.target.closest('.password-toggle');
  if (!el) return;
  const inputSelector = el.getAttribute('data-target');
  const input = document.querySelector(inputSelector);
  if (!input) return;
  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  // toggle icon
  const icon = el.querySelector('i');
  if (icon) {
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  }
  // accessibility
  el.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
  el.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
});

// Also allow toggling by pressing Enter or Space when focused on the button
document.addEventListener('keydown', function (e) {
  if (!(e.key === 'Enter' || e.key === ' ')) return;
  const el = e.target.closest('.password-toggle');
  if (!el) return;
  e.preventDefault();
  el.click();
});

// Provide a helper to reset icon state when form resets or inputs change
document.addEventListener('input', function (e) {
  const input = e.target;
  if (!input.matches('input[type="password"], input[type="text"]')) return;
  // If user types when visible, keep show state; no extra actions.
});
