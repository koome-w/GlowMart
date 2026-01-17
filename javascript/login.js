// Get DOM elements
const authWrapper = document.getElementById('authWrapper');
const signInBtn = document.getElementById('signInBtn');
const signUpBtn = document.getElementById('signUpBtn');
const signInForm = document.getElementById('signInForm');
const signUpForm = document.getElementById('signUpForm');
const overlayLeft = document.querySelector('.overlay-left');
const overlayRight = document.querySelector('.overlay-right');

// Toggle between sign in and sign up with smooth transition
function togglePanel() {
  // Prevent rapid clicking during transition
  if (authWrapper.classList.contains('transitioning')) {
    return;
  }
  
  // Add transitioning class to prevent multiple clicks
  authWrapper.classList.add('transitioning');
  authWrapper.classList.toggle('right-panel-active');
  
  // Remove transitioning class after animation completes
  setTimeout(() => {
    authWrapper.classList.remove('transitioning');
  }, 800);
}

// Event listeners for overlay buttons
if (signInBtn) {
  signInBtn.addEventListener('click', (e) => {
    e.preventDefault();
    togglePanel();
  });
}

if (signUpBtn) {
  signUpBtn.addEventListener('click', (e) => {
    e.preventDefault();
    togglePanel();
  });
}

// Enhanced hover effects with smooth transitions
if (overlayLeft) {
  overlayLeft.addEventListener('mouseenter', () => {
    if (!authWrapper.classList.contains('right-panel-active') && !authWrapper.classList.contains('transitioning')) {
      overlayLeft.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
      overlayLeft.style.transform = 'translateX(-10%) scale(1.02)';
    }
  });

  overlayLeft.addEventListener('mouseleave', () => {
    if (!authWrapper.classList.contains('right-panel-active') && !authWrapper.classList.contains('transitioning')) {
      overlayLeft.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
      overlayLeft.style.transform = 'translateX(-20%) scale(1)';
    }
  });

  // Add click effect
  overlayLeft.addEventListener('click', (e) => {
    e.stopPropagation();
    if (!authWrapper.classList.contains('right-panel-active') && !authWrapper.classList.contains('transitioning')) {
      togglePanel();
    }
  });
}

if (overlayRight) {
  overlayRight.addEventListener('mouseenter', () => {
    if (authWrapper.classList.contains('right-panel-active') && !authWrapper.classList.contains('transitioning')) {
      overlayRight.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
      overlayRight.style.transform = 'translateX(10%) scale(1.02)';
    }
  });

  overlayRight.addEventListener('mouseleave', () => {
    if (authWrapper.classList.contains('right-panel-active') && !authWrapper.classList.contains('transitioning')) {
      overlayRight.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
      overlayRight.style.transform = 'translateX(20%) scale(1)';
    }
  });

  // Add click effect
  overlayRight.addEventListener('click', (e) => {
    e.stopPropagation();
    if (authWrapper.classList.contains('right-panel-active') && !authWrapper.classList.contains('transitioning')) {
      togglePanel();
    }
  });
}

// Form submission handlers
if (signInForm) {
  signInForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = signInForm.querySelector('input[type="email"]').value;
    const password = signInForm.querySelector('input[type="password"]').value;
    
    console.log('Sign In:', { email, password });
    // Add your sign-in logic here
    alert('Sign In functionality - Email: ' + email);
  });
}

if (signUpForm) {
  signUpForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = signUpForm.querySelector('input[type="text"]').value;
    const email = signUpForm.querySelector('input[type="email"]').value;
    const password = signUpForm.querySelector('input[type="password"]').value;
    
    console.log('Sign Up:', { name, email, password });
    // Add your sign-up logic here
    alert('Sign Up functionality - Name: ' + name + ', Email: ' + email);
  });
}

// Social button handlers
const socialButtons = document.querySelectorAll('.social-btn');
socialButtons.forEach(button => {
  button.addEventListener('click', (e) => {
    e.preventDefault();
    const platform = button.querySelector('span').textContent;
    console.log(`${platform} login clicked`);
    // Add your social login logic here
    alert(`${platform} login functionality`);
  });
});

// Smooth animation on page load
window.addEventListener('load', () => {
  authWrapper.style.opacity = '0';
  authWrapper.style.transform = 'translateY(20px)';
  setTimeout(() => {
    authWrapper.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    authWrapper.style.opacity = '1';
    authWrapper.style.transform = 'translateY(0)';
  }, 100);
});

// Keyboard navigation support
document.addEventListener('keydown', (e) => {
  // Press 'S' to switch panels (when not typing in input)
  if (e.key === 's' || e.key === 'S') {
    if (document.activeElement.tagName !== 'INPUT') {
      togglePanel();
    }
  }
});

// Add ripple effect to buttons
function createRipple(event) {
  const button = event.currentTarget;
  const circle = document.createElement('span');
  const diameter = Math.max(button.clientWidth, button.clientHeight);
  const radius = diameter / 2;

  circle.style.width = circle.style.height = `${diameter}px`;
  circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
  circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
  circle.classList.add('ripple');

  const ripple = button.getElementsByClassName('ripple')[0];
  if (ripple) {
    ripple.remove();
  }

  button.appendChild(circle);
}

// Add ripple effect to submit and ghost buttons
const submitButtons = document.querySelectorAll('.submit-btn, .ghost-btn');
submitButtons.forEach(button => {
  button.addEventListener('click', createRipple);
});

// Add CSS for ripple effect dynamically
const style = document.createElement('style');
style.textContent = `
  .ripple {
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    background-color: rgba(255, 255, 255, 0.6);
    pointer-events: none;
  }

  @keyframes ripple-animation {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  .submit-btn,
  .ghost-btn {
    position: relative;
    overflow: hidden;
  }
`;
document.head.appendChild(style);
