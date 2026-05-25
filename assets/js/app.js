// ======================================
// DOCUMENT READY
// ======================================

document.addEventListener('DOMContentLoaded', function () {

    initNavbarScroll();

    initBackToTop();

    initSwipers();

    initEstimator();

    initFaq();

    initSmoothScroll();

    initLoginPopup();

    initMobileMenu();

    initOtpTimer();

    initResendCountdown();

    initSessionTimeoutWarning();

    initSecureForms();

    initInputSanitization();

    initEscapeModalClose();

});

// ======================================
// NAVBAR SCROLL EFFECT
// ======================================

function initNavbarScroll() {

    const header =
    document.querySelector('.header');

    if (!header) return;

    window.addEventListener('scroll', function () {

        if (window.scrollY > 80) {

            header.style.boxShadow =
            '0 5px 20px rgba(0,0,0,0.08)';

            header.classList.add('scrolled');

        } else {

            header.style.boxShadow =
            '0 3px 15px rgba(0,0,0,0.04)';

            header.classList.remove('scrolled');
        }
    });
}

// ======================================
// BACK TO TOP
// ======================================

function initBackToTop() {

    const button =
    document.getElementById('backToTop');

    if (!button) return;

    window.addEventListener('scroll', function () {

        if (window.scrollY > 300) {

            button.style.display = 'flex';

        } else {

            button.style.display = 'none';
        }
    });

    button.addEventListener('click', function () {

        window.scrollTo({

            top: 0,

            behavior: 'smooth'
        });
    });
}

// ======================================
// MOBILE MENU
// ======================================

function initMobileMenu() {

    const menuToggle =
    document.getElementById('mobileMenuToggle');

    const mobileMenu =
    document.getElementById('mobileMenu');

    if (!menuToggle || !mobileMenu) return;

    menuToggle.addEventListener('click', function () {

        mobileMenu.classList.toggle('active');
    });
}

// ======================================
// SWIPER SLIDERS
// ======================================

function initSwipers() {

    // ==================================
    // PROJECT SWIPER
    // ==================================

    if (document.querySelector('.projectSwiper')) {

        new Swiper('.projectSwiper', {

            loop: true,

            autoplay: {
                delay: 3500
            },

            spaceBetween: 25,

            pagination: {
                el: '.swiper-pagination',
                clickable: true
            },

            breakpoints: {

                320: {
                    slidesPerView: 1
                },

                768: {
                    slidesPerView: 2
                },

                1200: {
                    slidesPerView: 3
                }
            }
        });
    }

    // ==================================
    // TESTIMONIAL SWIPER
    // ==================================

    if (document.querySelector('.testimonialSwiper')) {

        new Swiper('.testimonialSwiper', {

            loop: true,

            autoplay: {
                delay: 4000
            },

            spaceBetween: 25,

            pagination: {
                el: '.testimonial-pagination',
                clickable: true
            },

            breakpoints: {

                320: {
                    slidesPerView: 1
                },

                992: {
                    slidesPerView: 2
                }
            }
        });
    }

    // ==================================
    // BLOG SWIPER
    // ==================================

    if (document.querySelector('.blogSwiper')) {

        new Swiper('.blogSwiper', {

            loop: true,

            autoplay: {
                delay: 4500
            },

            spaceBetween: 25,

            breakpoints: {

                320: {
                    slidesPerView: 1
                },

                768: {
                    slidesPerView: 2
                },

                1200: {
                    slidesPerView: 3
                }
            }
        });
    }
}

// ======================================
// FAQ TOGGLE
// ======================================

function initFaq() {

    const faqQuestions =
    document.querySelectorAll('.faq-question');

    faqQuestions.forEach(function (question) {

        question.addEventListener('click', function () {

            const faqItem =
            this.parentElement;

            faqItem.classList.toggle('active');

        });
    });
}

// ======================================
// SMOOTH SCROLL
// ======================================

function initSmoothScroll() {

    document

    .querySelectorAll('a[href^="#"]')

    .forEach(anchor => {

        anchor.addEventListener('click', function (e) {

            const target =
            document.querySelector(

                this.getAttribute('href')
            );

            if (target) {

                e.preventDefault();

                target.scrollIntoView({

                    behavior: 'smooth'
                });
            }
        });
    });
}

// ======================================
// LOGIN POPUP
// ======================================

function initLoginPopup() {

    const popup =
    document.getElementById('loginPopup');

    if (!popup) return;

    window.openLogin = function () {

        popup.classList.add('active');
    }

    window.closeLogin = function () {

        popup.classList.remove('active');
    }

    popup.addEventListener('click', function (e) {

        if (e.target === popup) {

            closeLogin();
        }
    });
}

// ======================================
// ESTIMATOR INIT
// ======================================

function initEstimator() {

    calculateArea();

    const lengthInput =
    document.getElementById('plotLength');

    const widthInput =
    document.getElementById('plotWidth');

    if (lengthInput) {

        lengthInput.addEventListener(

            'input',

            calculateArea
        );
    }

    if (widthInput) {

        widthInput.addEventListener(

            'input',

            calculateArea
        );
    }

    const estimatorForm =
    document.getElementById('estimatorForm');

    if (estimatorForm) {

        estimatorForm.addEventListener(

            'submit',

            function () {

                const button =
                estimatorForm.querySelector(

                    'button[type="submit"]'
                );

                if (button) {

                    button.disabled = true;

                    button.innerText =
                    'Generating Estimate...';
                }
            }
        );
    }
}

// ======================================
// AREA CALCULATION
// ======================================

function calculateArea() {

    const length =
    parseFloat(
        document.getElementById('plotLength')?.value
    ) || 0;

    const width =
    parseFloat(
        document.getElementById('plotWidth')?.value
    ) || 0;

    const area =
    length * width;

    const sqftInput =
    document.getElementById('sqft');

    const sqftValue =
    document.getElementById('sqftValue');

    if (sqftInput) {

        sqftInput.value = area;
    }

    if (sqftValue) {

        sqftValue.innerText =
        area + ' sqft';
    }
}

// ======================================
// DYNAMIC ESTIMATOR
// ======================================

function calculateCost() {

    const sqft =
    parseFloat(
        document.getElementById('sqft')?.value
    ) || 0;

    const floors =
    parseInt(
        document.getElementById('floors')?.value
    ) || 1;

    const packageSelect =
    document.getElementById('quality');

    if (!packageSelect) return;

    const selectedOption =
    packageSelect.options[
        packageSelect.selectedIndex
    ];

    const price =
    parseFloat(
        selectedOption.dataset.price
    ) || 0;

    const timeline =
    selectedOption.dataset.timeline || '-';

    const material =
    selectedOption.dataset.material || '-';

    const packageName =
    selectedOption.text;

    const builtupArea =
    sqft * floors;

    const total =
    builtupArea * price;

    const totalCost =
    document.getElementById('totalCost');

    const builtupAreaBox =
    document.getElementById('builtupArea');

    const timelineBox =
    document.getElementById('timeline');

    const packageBox =
    document.getElementById('package');

    const materialBox =
    document.getElementById('materialGrade');

    if (totalCost) {

        totalCost.innerText =

            '₹ ' +

            total.toLocaleString('en-IN');
    }

    if (builtupAreaBox) {

        builtupAreaBox.innerText =

            builtupArea +

            ' sqft';
    }

    if (timelineBox) {

        timelineBox.innerText =
        timeline;
    }

    if (packageBox) {

        packageBox.innerText =
        packageName;
    }

    if (materialBox) {

        materialBox.innerText =
        material;
    }
}

// ======================================
// OTP TIMER
// ======================================

function initOtpTimer() {

    const otpTimer =
    document.getElementById('otpTimer');

    if (!otpTimer) return;

    let timeLeft = 300;

    updateOtpTimer();

    const timer =
    setInterval(function () {

        timeLeft--;

        updateOtpTimer();

        if (timeLeft <= 0) {

            clearInterval(timer);

            otpTimer.innerText =
            'OTP Expired';
        }

    }, 1000);

    function updateOtpTimer() {

        const minutes =
        Math.floor(timeLeft / 60);

        const seconds =
        timeLeft % 60;

        otpTimer.innerText =

            minutes +

            ':' +

            String(seconds).padStart(2, '0');
    }
}

// ======================================
// RESEND OTP COUNTDOWN
// ======================================

function initResendCountdown() {

    const resendButton =
    document.getElementById('resendOtpBtn');

    if (!resendButton) return;

    let countdown = 30;

    resendButton.disabled = true;

    resendButton.innerText =
    'Resend OTP in 30s';

    const interval =
    setInterval(function () {

        countdown--;

        resendButton.innerText =

            'Resend OTP in ' +

            countdown +

            's';

        if (countdown <= 0) {

            clearInterval(interval);

            resendButton.disabled = false;

            resendButton.innerText =
            'Resend OTP';
        }

    }, 1000);
}

// ======================================
// SESSION TIMEOUT WARNING
// ======================================

function initSessionTimeoutWarning() {

    const timeoutMinutes = 30;

    const warningBefore = 5;

    const totalMs =
    timeoutMinutes * 60 * 1000;

    const warningMs =
    (timeoutMinutes - warningBefore)
    * 60
    * 1000;

    setTimeout(function () {

        const stayLoggedIn =
        confirm(

            'Your session will expire in 5 minutes. Stay logged in?'
        );

        if (!stayLoggedIn) {

            window.location.href =
            '/logout.php';
        }

    }, warningMs);

    setTimeout(function () {

        window.location.href =
        '/logout.php';

    }, totalMs);
}

// ======================================
// SECURE FORMS
// ======================================

function initSecureForms() {

    const forms =
    document.querySelectorAll('form');

    forms.forEach(function (form) {

        form.addEventListener(

            'submit',

            function () {

                const submitButton =
                form.querySelector(

                    'button[type="submit"]'
                );

                if (submitButton) {

                    submitButton.disabled = true;

                    submitButton.innerText =
                    'Please wait...';
                }
            }
        );
    });
}

// ======================================
// SECURE FETCH WRAPPER
// ======================================

async function secureFetch(

    url,

    options = {}

) {

    const csrfToken =
    document.querySelector(

        'meta[name="csrf-token"]'
    )?.content;

    options.headers = {

        ...(options.headers || {}),

        'X-CSRF-TOKEN':
        csrfToken,

        'X-Requested-With':
        'XMLHttpRequest'
    };

    return fetch(url, options);
}

// ======================================
// SECURE AJAX CONTACT FORM
// ======================================

async function submitContactForm(form) {

    try {

        const formData =
        new FormData(form);

        const response =
        await secureFetch(

            form.action,

            {

                method: 'POST',

                body: formData
            }
        );

        const data =
        await response.json();

        if (data.success) {

            alert(data.message);

            form.reset();

        } else {

            alert(data.message);
        }

    } catch (error) {

        console.error(error);

        alert('Something went wrong.');
    }
}

// ======================================
// OTP ATTEMPT COUNTER
// ======================================

let otpAttempts = 0;

function increaseOtpAttempt() {

    otpAttempts++;

    const attemptBox =
    document.getElementById('otpAttempts');

    if (attemptBox) {

        attemptBox.innerText =

            'Attempts: ' +

            otpAttempts +

            '/5';
    }

    if (otpAttempts >= 5) {

        alert(
            'Too many OTP attempts.'
        );

        window.location.href =
        '/login.php';
    }
}

// ======================================
// ESC KEY MODAL CLOSE
// ======================================

function initEscapeModalClose() {

    document.addEventListener(

        'keydown',

        function (e) {

            if (e.key === 'Escape') {

                document

                .querySelectorAll('.modal.active')

                .forEach(function (modal) {

                    modal.classList.remove('active');
                });
            }
        }
    );
}

// ======================================
// INPUT SANITIZATION
// ======================================

function initInputSanitization() {

    document.addEventListener(

        'input',

        function (e) {

            if (

                e.target.matches(

                    'input[type="text"], textarea'
                )

            ) {

                e.target.value =

                e.target.value

                .replace(/<script/gi, '')

                .replace(/<\/script>/gi, '');
            }
        }
    );
}

// ======================================
// AUTO HIDE ALERTS
// ======================================

setTimeout(function () {

    document

    .querySelectorAll('.alert')

    .forEach(function (alert) {

        alert.style.opacity = '0';

        setTimeout(function () {

            alert.remove();

        }, 400);
    });

}, 5000);