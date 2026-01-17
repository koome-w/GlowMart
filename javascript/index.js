// Hero Slider Functionality
const slides = [
  {
    id: 1,
    title: "Make Your Skin Pop with GlowMart",
    subtitle: "We care about your skin at GlowMart. Your glow is one of our priorities to us.",
    image: "../assets/slide1.jpeg",
    cta: "SHOP NOW"
  },
  {
    id: 2,
    title: "Discover Your Perfect Glow",
    subtitle: "Premium beauty products crafted for every skin type. Experience the difference.",
    image: "../assets/slide2.jpeg",
    cta: "EXPLORE PRODUCTS"
  },
  {
    id: 3,
    title: "Beauty That Moves With You",
    subtitle: "From morning routines to evening rituals, we've got you covered.",
    image: "../assets/slide3.jpeg",
    cta: "START YOUR JOURNEY"
  }
];

// Fallback images if original images don't load
const fallbackImage = "https://via.placeholder.com/600x400/ff6b6b/ffffff?text=GlowMart+Beauty";

let currentSlide = 0;

// Get DOM elements
const heroTitle = document.getElementById('heroTitle');
const heroSubtitle = document.getElementById('heroSubtitle');
const heroCta = document.getElementById('heroCta');
const heroImage = document.getElementById('heroImage');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const heroDots = document.getElementById('heroDots');

// Function to update slide
function updateSlide(index) {
  currentSlide = index;
  const slide = slides[currentSlide];
  
  heroTitle.textContent = slide.title;
  heroSubtitle.textContent = slide.subtitle;
  heroCta.textContent = slide.cta;
  heroImage.src = slide.image;
  heroImage.alt = slide.title;
  heroImage.onerror = function() {
    this.src = fallbackImage;
  };
  
  // Update dots
  const dots = heroDots.querySelectorAll('.dot');
  dots.forEach((dot, i) => {
    if (i === currentSlide) {
      dot.classList.add('active');
    } else {
      dot.classList.remove('active');
    }
  });
}

// Next slide
function nextSlide() {
  currentSlide = (currentSlide + 1) % slides.length;
  updateSlide(currentSlide);
}

// Previous slide
function prevSlide() {
  currentSlide = (currentSlide - 1 + slides.length) % slides.length;
  updateSlide(currentSlide);
}

// Go to specific slide
function goToSlide(index) {
  updateSlide(index);
}

// Event listeners for navigation buttons
if (prevBtn) {
  prevBtn.addEventListener('click', prevSlide);
}

if (nextBtn) {
  nextBtn.addEventListener('click', nextSlide);
}

// Event listeners for dots
if (heroDots) {
  const dots = heroDots.querySelectorAll('.dot');
  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => goToSlide(index));
  });
}

// Auto-slide functionality
let slideInterval = setInterval(nextSlide, 3000);

// Pause auto-slide on hover
const heroSection = document.querySelector('.hero');
if (heroSection) {
  heroSection.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
  });
  
  heroSection.addEventListener('mouseleave', () => {
    slideInterval = setInterval(nextSlide, 3000);
  });
}

// Mobile Menu Toggle
const menuToggle = document.getElementById('menuToggle');
const nav = document.getElementById('nav');

if (menuToggle && nav) {
  menuToggle.addEventListener('click', () => {
    nav.classList.toggle('nav-open');
  });
  
  // Close menu when clicking on a link
  const navLinks = nav.querySelectorAll('.nav-link');
  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      nav.classList.remove('nav-open');
    });
  });
  
  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (!nav.contains(e.target) && !menuToggle.contains(e.target)) {
      nav.classList.remove('nav-open');
    }
  });
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const href = this.getAttribute('href');
    if (href !== '#' && href !== '#shop' && href !== '#about') {
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    }
  });
});

// Initialize first slide
updateSlide(0);

