// ============================================
// MULTI-LANGUAGE SUPPORT
// ============================================

// Get current language from localStorage or default to English
let currentLanguage = localStorage.getItem('language') || 'en';

// Function to change language
function changeLanguage(lang) {
    currentLanguage = lang;
    localStorage.setItem('language', lang);
    
    // Update all elements with data-translate attribute
    document.querySelectorAll('[data-translate]').forEach(element => {
        const key = element.getAttribute('data-translate');
        const translation = getTranslation(key, lang);
        
        if (translation) {
            // Check if it's an input or textarea placeholder
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.placeholder = translation;
            } 
            // Check if element has a placeholder attribute set
            else if (element.hasAttribute('placeholder')) {
                element.placeholder = translation;
            }
            // Otherwise update innerHTML
            else {
                element.innerHTML = translation;
            }
        }
    });
    
    // Update active language button
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-lang') === lang) {
            btn.classList.add('active');
        }
    });
    
    // Update page title
    updatePageTitle(lang);
    
    // Update HTML lang attribute
    document.documentElement.lang = lang;
}

// Get translation from nested object
function getTranslation(key, lang) {
    if (typeof translations === 'undefined') return null;
    
    const keys = key.split('.');
    let translation = translations[lang];
    
    for (const k of keys) {
        if (translation && translation[k]) {
            translation = translation[k];
        } else {
            return null;
        }
    }
    
    return translation;
}

// Update page title based on current page and language
function updatePageTitle(lang) {
    const pageTitles = {
        en: {
            index: "Assistant Commissioner of Local Government Office - Hambantota",
            home: "Home - ACLG Hambantota",
            account: "Account Department - ACLG Hambantota",
            establishment: "Establishment Department - ACLG Hambantota",
            development: "Development Department - ACLG Hambantota",
            investigation: "Investigation Department - ACLG Hambantota",
            engineering: "Engineering Department - ACLG Hambantota",
            "404": "404 - Page Not Found | ACLG Hambantota"
        },
        si: {
            index: "පළාත් පාලන සහකාර කොමසාරිස් කාර්යාලය - හම්බන්තොට",
            home: "මුල් පිටුව - ACLG හම්බන්තොට",
            account: "ගිණුම් දෙපාර්තමේන්තුව - ACLG හම්බන්තොට",
            establishment: "ආයතන දෙපාර්තමේන්තුව - ACLG හම්බන්තොට",
            development: "සංවර්ධන දෙපාර්තමේන්තුව - ACLG හම්බන්තොට",
            investigation: "විමර්ශන දෙපාර්තමේන්තුව - ACLG හම්බන්තොට",
            engineering: "ඉංජිනේරු දෙපාර්තමේන්තුව - ACLG හම්බන්තොට",
            "404": "404 - පිටුව හමු නොවීය | ACLG හම්බන්තොට"
        },
        ta: {
            index: "உள்ளூராட்சி உதவி ஆணையர் அலுவலகம் - ஹம்பாந்தோட்டை",
            home: "முகப்பு - ACLG ஹம்பாந்தோட்டை",
            account: "கணக்கு துறை - ACLG ஹம்பாந்தோட்டை",
            establishment: "ஸ்தாபன துறை - ACLG ஹம்பாந்தோட்டை",
            development: "அபிவிருத்தி துறை - ACLG ஹம்பாந்தோட்டை",
            investigation: "விசாரணை துறை - ACLG ஹம்பாந்தோட்டை",
            engineering: "பொறியியல் துறை - ACLG ஹம்பாந்தோட்டை",
            "404": "404 - பக்கம் கிடைக்கவில்லை | ACLG ஹம்பாந்தோட்டை"
        }
    };
    
    // Detect current page
    const path = window.location.pathname;
    let page = 'index';
    
    if (path.includes('home.html')) page = 'home';
    else if (path.includes('account.html')) page = 'account';
    else if (path.includes('establishment.html')) page = 'establishment';
    else if (path.includes('development.html')) page = 'development';
    else if (path.includes('investigation.html')) page = 'investigation';
    else if (path.includes('engineering.html')) page = 'engineering';
    else if (path.includes('404.html')) page = '404';
    
    if (pageTitles[lang] && pageTitles[lang][page]) {
        document.title = pageTitles[lang][page];
    }
}

// Initialize language on page load
document.addEventListener('DOMContentLoaded', () => {
    // Load saved language or default to English
    changeLanguage(currentLanguage);
    
    // Add click event to all language buttons
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const lang = btn.getAttribute('data-lang');
            changeLanguage(lang);
        });
    });
});

// ============================================
// NAVIGATION & MOBILE MENU
// ============================================

// Mobile Navigation Toggle
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

if (hamburger) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
}

// Close mobile menu when clicking on a link
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Hero Slideshow
const heroSlideshow = document.querySelector('.hero-slideshow');
if (heroSlideshow) {
    const slides = document.querySelectorAll('.hero-slideshow .slide');
    const indicators = document.querySelectorAll('.slideshow-indicators .indicator');
    const prevBtn = document.querySelector('.slide-prev');
    const nextBtn = document.querySelector('.slide-next');
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        // Remove active class from all slides and indicators
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));

        // Add active class to current slide and indicator
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    function startSlideshow() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }

    function stopSlideshow() {
        clearInterval(slideInterval);
    }

    // Navigation buttons
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopSlideshow();
            startSlideshow(); // Restart auto-play
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopSlideshow();
            startSlideshow(); // Restart auto-play
        });
    }

    // Indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
            stopSlideshow();
            startSlideshow(); // Restart auto-play
        });
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (heroSlideshow && window.pageYOffset < 100) {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                stopSlideshow();
                startSlideshow();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                stopSlideshow();
                startSlideshow();
            }
        }
    });

    // Touch support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    heroSlideshow.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });

    heroSlideshow.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            // Swipe left
            nextSlide();
            stopSlideshow();
            startSlideshow();
        }
        if (touchEndX > touchStartX + 50) {
            // Swipe right
            prevSlide();
            stopSlideshow();
            startSlideshow();
        }
    }

    // Pause slideshow on hover
    heroSlideshow.addEventListener('mouseenter', stopSlideshow);
    heroSlideshow.addEventListener('mouseleave', startSlideshow);

    // Start the slideshow
    startSlideshow();
}

// Smooth Scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const offset = 70; // Height of fixed navbar
            const targetPosition = target.offsetTop - offset;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Active Navigation Link on Scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        const sectionHeight = section.offsetHeight;
        if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
});

// Navbar scroll effect
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.15)';
    } else {
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
    }
    
    lastScroll = currentScroll;
});

// Back to Top Button
const backToTopButton = document.getElementById('backToTop');

if (backToTopButton) {
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });

    backToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Form validation and submission handler
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    const submitBtn = contactForm.querySelector('.submit-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    const formSuccess = document.getElementById('formSuccess');

    // Validation functions
    function validateName() {
        const nameError = document.getElementById('nameError');
        if (nameInput.value.trim().length < 2) {
            nameError.textContent = 'Name must be at least 2 characters long';
            nameInput.style.borderColor = '#dc2626';
            return false;
        }
        nameError.textContent = '';
        nameInput.style.borderColor = '';
        return true;
    }

    function validateEmail() {
        const emailError = document.getElementById('emailError');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value.trim())) {
            emailError.textContent = 'Please enter a valid email address';
            emailInput.style.borderColor = '#dc2626';
            return false;
        }
        emailError.textContent = '';
        emailInput.style.borderColor = '';
        return true;
    }

    function validateSubject() {
        const subjectError = document.getElementById('subjectError');
        if (subjectInput.value.trim().length < 3) {
            subjectError.textContent = 'Subject must be at least 3 characters long';
            subjectInput.style.borderColor = '#dc2626';
            return false;
        }
        subjectError.textContent = '';
        subjectInput.style.borderColor = '';
        return true;
    }

    function validateMessage() {
        const messageError = document.getElementById('messageError');
        if (messageInput.value.trim().length < 10) {
            messageError.textContent = 'Message must be at least 10 characters long';
            messageInput.style.borderColor = '#dc2626';
            return false;
        }
        messageError.textContent = '';
        messageInput.style.borderColor = '';
        return true;
    }

    // Real-time validation
    nameInput.addEventListener('blur', validateName);
    emailInput.addEventListener('blur', validateEmail);
    subjectInput.addEventListener('blur', validateSubject);
    messageInput.addEventListener('blur', validateMessage);

    // Form submission
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate all fields
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isSubjectValid = validateSubject();
        const isMessageValid = validateMessage();
        
        if (!isNameValid || !isEmailValid || !isSubjectValid || !isMessageValid) {
            return;
        }

        // Show loading state
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-block';
        submitBtn.disabled = true;
        formSuccess.style.display = 'none';
        
        // Submit to Formspree
        try {
            const response = await fetch(contactForm.action, {
                method: 'POST',
                body: new FormData(contactForm),
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                // Show success message
                formSuccess.style.display = 'block';
                contactForm.reset();
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    formSuccess.style.display = 'none';
                }, 5000);
            } else {
                // Show error message
                alert('An error occurred. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        } finally {
            // Reset button state
            btnText.style.display = 'inline-block';
            btnLoading.style.display = 'none';
            submitBtn.disabled = false;
        }
    });
}

// Add animation to elements when they come into view
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all department cards
document.querySelectorAll('.dept-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});

// Observe info items
document.querySelectorAll('.info-item').forEach(item => {
    item.style.opacity = '0';
    item.style.transform = 'translateX(-30px)';
    item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(item);
});

// Add loading animation for landing page
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
});

// Set initial body opacity
document.body.style.opacity = '0';
document.body.style.transition = 'opacity 0.5s ease';

// ============================================
// CHATBOT FUNCTIONALITY WITH AI INTEGRATION
// ============================================

const chatbotToggle = document.getElementById('chatbotToggle');
const chatbotContainer = document.getElementById('chatbotContainer');
const chatbotClose = document.getElementById('chatbotClose');
const chatbotMinimize = document.getElementById('chatbotMinimize');
const chatbotMessages = document.getElementById('chatbotMessages');
const chatbotInput = document.getElementById('chatbotInput');
const chatbotSend = document.getElementById('chatbotSend');
const quickButtons = document.querySelectorAll('.quick-btn');

// AI Configuration
const AI_CONFIG = {
    enabled: false, // Disabled: Browser CORS prevents direct API calls. Use rule-based instead.
    provider: 'gemini', // Options: 'openai', 'gemini', 'anthropic'
    apiKey: 'AIzaSyBiNr6v43AfxjEg1HhaPvz693w7_chNcH4', // Your Gemini API key
    model: 'gemini-pro', // Model to use
    maxTokens: 150,
    temperature: 0.7
};

// NOTE: To enable AI, you need a backend server to proxy API calls
// Browser direct calls to AI APIs are blocked by CORS security

// Conversation history for AI context
let conversationHistory = [];

// Debug: Check if chatbot elements loaded
console.log('Chatbot Debug:', {
    toggleButton: !!chatbotToggle,
    container: !!chatbotContainer,
    input: !!chatbotInput,
    send: !!chatbotSend,
    aiEnabled: AI_CONFIG.enabled,
    hasApiKey: !!AI_CONFIG.apiKey
});

// Initialize button visibility
if (chatbotToggle) {
    chatbotToggle.style.display = 'flex';
}
if (chatbotContainer) {
    chatbotContainer.classList.remove('active');
}

// Open chatbot
if (chatbotToggle) {
    chatbotToggle.addEventListener('click', () => {
        console.log('Chatbot toggle clicked');
        chatbotContainer.classList.add('active');
        chatbotToggle.style.display = 'none';
        chatbotInput.focus();
        
        // Remove "New" badge after first open
        const badge = chatbotToggle.querySelector('.chatbot-badge');
        if (badge) {
            setTimeout(() => badge.remove(), 300);
        }
    });
}

// Close chatbot
if (chatbotClose) {
    chatbotClose.addEventListener('click', () => {
        chatbotContainer.classList.remove('active');
        chatbotToggle.style.display = 'flex';
    });
}

// Minimize chatbot
if (chatbotMinimize) {
    chatbotMinimize.addEventListener('click', () => {
        chatbotContainer.classList.remove('active');
        chatbotToggle.style.display = 'flex';
    });
}

// Get current time
function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// Add message to chat
function addMessage(text, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'}`;
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
    
    const content = document.createElement('div');
    content.className = 'message-content';
    
    const messagePara = document.createElement('p');
    messagePara.textContent = text;
    
    const time = document.createElement('span');
    time.className = 'message-time';
    time.textContent = getCurrentTime();
    
    content.appendChild(messagePara);
    content.appendChild(time);
    
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(content);
    
    // Remove quick buttons if they exist
    const existingQuickButtons = chatbotMessages.querySelector('.quick-buttons');
    if (existingQuickButtons && isUser) {
        existingQuickButtons.remove();
    }
    
    chatbotMessages.appendChild(messageDiv);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

// Show typing indicator
function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.className = 'chat-message bot-message typing-message';
    typingDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    `;
    chatbotMessages.appendChild(typingDiv);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    return typingDiv;
}

// Remove typing indicator
function removeTypingIndicator(indicator) {
    if (indicator && indicator.parentNode) {
        indicator.remove();
    }
}

// Get bot response based on user input
function getBotResponse(userInput) {
    const input = userInput.toLowerCase();
    const responses = getTranslation('chatbot.responses', currentLanguage);
    
    // Greeting patterns
    const greetings = ['hello', 'hi', 'hey', 'greetings', 'good morning', 'good afternoon', 
                       'ආයුබෝවන්', 'හායි', 'வணக்கம்', 'ஹாய்'];
    if (greetings.some(greeting => input.includes(greeting))) {
        const greetingResponses = responses.greeting || ['Hello!'];
        return greetingResponses[Math.floor(Math.random() * greetingResponses.length)] + ' ' + responses.default;
    }
    
    // Thank you patterns
    const thanks = ['thank', 'thanks', 'appreciate', 'ස්තූතියි', 'நன்றி'];
    if (thanks.some(thank => input.includes(thank))) {
        return responses.thanks || "You're welcome!";
    }
    
    // Help patterns
    const help = ['help', 'උදව්', 'உதவி'];
    if (help.some(h => input.includes(h))) {
        return responses.help || "I can help you with information about our departments, contact details, and more!";
    }
    
    // Permit/License queries
    const permit = ['permit', 'license', 'approval', 'authorization', 'building', 'construct', 
                    'අවසර', 'බලපත්‍ර', 'ඉදිකිරීම්', 'அனுமதி', 'உரிமம்', 'கட்டிடம்'];
    if (permit.some(p => input.includes(p))) {
        return "For construction permits and building approvals:\n\n1. Download the permit application form from our Downloads section\n2. Submit to Engineering Department with required documents\n3. Contact: +94 47 222 0456\n\nThe Engineering Department will process your application and conduct necessary inspections.";
    }
    
    // Department-specific queries
    if (input.includes('account') || input.includes('ගිණුම්') || input.includes('கணக்கு')) {
        return responses.account || responses.departments;
    }
    if (input.includes('establishment') || input.includes('ආයතන') || input.includes('ஸ்தாபனம்')) {
        return responses.establishment || responses.departments;
    }
    if (input.includes('development') || input.includes('සංවර්ධන') || input.includes('அபிவிருத்தி')) {
        return responses.development || responses.departments;
    }
    if (input.includes('investigation') || input.includes('විමර්ශන') || input.includes('விசாரணை')) {
        return responses.investigation || responses.departments;
    }
    if (input.includes('engineering') || input.includes('ඉංජිනේරු') || input.includes('பொறியியல்')) {
        return responses.engineering || responses.departments;
    }
    
    // Keyword matching
    if (input.includes('department') || input.includes('දෙපාර්තමේන්තු') || input.includes('துறை')) {
        return responses.departments;
    }
    if (input.includes('contact') || input.includes('phone') || input.includes('email') || 
        input.includes('සම්බන්ධ') || input.includes('தொடர்பு')) {
        return responses.contact;
    }
    if (input.includes('hour') || input.includes('time') || input.includes('open') || 
        input.includes('වේලාව') || input.includes('நேரம்')) {
        return responses.hours;
    }
    if (input.includes('download') || input.includes('form') || input.includes('document') || 
        input.includes('බාගත') || input.includes('பதிவிறக்க')) {
        return responses.downloads;
    }
    
    // Default response
    return responses.default || "I'm here to help! Please ask me about our departments, contact information, or office hours.";
}

// Handle sending messages with AI integration
async function sendMessage() {
    const message = chatbotInput.value.trim();
    if (!message) return;
    
    // Add user message
    addMessage(message, true);
    chatbotInput.value = '';
    chatbotSend.disabled = true;
    
    // Show typing indicator
    const typingIndicator = showTypingIndicator();
    
    let response;
    
    try {
        // Try AI first if enabled
        if (AI_CONFIG.enabled && AI_CONFIG.apiKey) {
            try {
                response = await getAIResponse(message);
                console.log('AI Response:', response);
            } catch (aiError) {
                console.warn('AI failed, using rule-based:', aiError);
                response = null;
            }
        }
        
        // Fall back to rule-based if AI fails or disabled
        if (!response) {
            console.log('Using rule-based response');
            await new Promise(resolve => setTimeout(resolve, 800 + Math.random() * 700));
            response = getBotResponse(message);
        }
        
        removeTypingIndicator(typingIndicator);
        addMessage(response, false);
        
    } catch (error) {
        console.error('Message error:', error);
        removeTypingIndicator(typingIndicator);
        // Still try rule-based as last resort
        const fallbackResponse = getBotResponse(message);
        addMessage(fallbackResponse, false);
    } finally {
        chatbotSend.disabled = false;
    }
}

// Send button click
if (chatbotSend) {
    chatbotSend.addEventListener('click', sendMessage);
}

// Enter key to send
if (chatbotInput) {
    chatbotInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
}

// Quick button clicks
quickButtons.forEach(button => {
    button.addEventListener('click', () => {
        const question = button.getAttribute('data-question');
        const responses = getTranslation('chatbot.responses', currentLanguage);
        
        // Add user message (button text)
        addMessage(button.textContent, true);
        
        // Show typing indicator
        const typingIndicator = showTypingIndicator();
        
        // Get response based on question type
        setTimeout(() => {
            removeTypingIndicator(typingIndicator);
            let response = responses.default;
            
            switch(question) {
                case 'departments':
                    response = responses.departments;
                    break;
                case 'contact':
                    response = responses.contact;
                    break;
                case 'hours':
                    response = responses.hours;
                    break;
                case 'downloads':
                    response = responses.downloads;
                    break;
            }
            
            addMessage(response, false);
        }, 600);
    });
});

// Update chatbot when language changes
const originalChangeLanguage = changeLanguage;
changeLanguage = function(lang) {
    originalChangeLanguage(lang);
    
    // Update chatbot placeholder
    if (chatbotInput) {
        const placeholder = getTranslation('chatbot.inputPlaceholder', lang);
        if (placeholder) {
            chatbotInput.placeholder = placeholder;
        }
    }
};
