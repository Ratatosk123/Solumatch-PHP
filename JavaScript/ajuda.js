 // FAQ Accordion functionality
 document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', () => {
        const faqItem = question.parentElement;
        const answer = question.nextElementSibling;
        
        // Close all other FAQ items
        document.querySelectorAll('.faq-item').forEach(item => {
            if (item !== faqItem) {
                item.classList.remove('active');
                item.querySelector('.faq-answer').classList.remove('active');
            }
        });
        
        // Toggle current FAQ item
        faqItem.classList.toggle('active');
        answer.classList.toggle('active');
    });
});

// Search functionality
const searchInput = document.getElementById('searchInput');
const faqItems = document.querySelectorAll('.faq-item');

searchInput.addEventListener('input', (e) => {
    const searchTerm = e.target.value.toLowerCase();
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question').textContent.toLowerCase();
        const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
        const keywords = item.dataset.keywords || '';
        
        const isVisible = question.includes(searchTerm) || 
                        answer.includes(searchTerm) || 
                        keywords.includes(searchTerm);
        
        if (isVisible) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
});

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Back to top button functionality
const backToTopButton = document.getElementById('backToTop');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        backToTopButton.classList.add('visible');
    } else {
        backToTopButton.classList.remove('visible');
    }
});

backToTopButton.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Add fade-in animation on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
        }
    });
}, observerOptions);

// Observe elements for animation
document.querySelectorAll('.category-card').forEach(card => {
    observer.observe(card);
});

// Auto-expand first FAQ item
document.addEventListener('DOMContentLoaded', () => {
    const firstFaqItem = document.querySelector('.faq-item');
    if (firstFaqItem) {
        firstFaqItem.classList.add('active');
        firstFaqItem.querySelector('.faq-answer').classList.add('active');
    }
});

// Add typing effect to search placeholder
const searchPlaceholders = [
    'Digite sua dúvida aqui...',
    'Como criar uma conta?',
    'Esqueci minha senha',
    'Formas de pagamento',
    'Prazo de entrega'
];

let currentPlaceholder = 0;

setInterval(() => {
    currentPlaceholder = (currentPlaceholder + 1) % searchPlaceholders.length;
    searchInput.placeholder = searchPlaceholders[currentPlaceholder];
}, 3000);

