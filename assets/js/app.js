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

    faqQuestions.forEach(function(question){

        question.addEventListener('click', function(){

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

    document.querySelectorAll('a[href^="#"]')

    .forEach(anchor => {

        anchor.addEventListener('click', function (e) {

            const target =
            document.querySelector(

                this.getAttribute('href')
            );

            if(target){

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

    if(!popup) return;

    window.openLogin = function(){

        popup.classList.add('active');
    }

    window.closeLogin = function(){

        popup.classList.remove('active');
    }

    popup.addEventListener('click', function(e){

        if(e.target === popup){

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

    if(lengthInput){

        lengthInput.addEventListener(

            'input',

            calculateArea
        );
    }

    if(widthInput){

        widthInput.addEventListener(

            'input',

            calculateArea
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

    if(sqftInput){

        sqftInput.value = area;
    }

    if(sqftValue){

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

        document.getElementById('sqft').value
    ) || 0;

    const floors =
    parseInt(

        document.getElementById('floors').value
    ) || 1;

    const packageSelect =
    document.getElementById('quality');

    const selectedOption =
    packageSelect.options[
        packageSelect.selectedIndex
    ];

    const price =
    parseFloat(

        selectedOption.dataset.price
    ) || 0;

    const timeline =
    selectedOption.dataset.timeline;

    const material =
    selectedOption.dataset.material;

    const packageName =
    selectedOption.text;

    // ==================================
    // BUILTUP AREA
    // ==================================

    const builtupArea =
    sqft * floors;

    // ==================================
    // TOTAL COST
    // ==================================

    const total =
    builtupArea * price;

    // ==================================
    // UPDATE UI
    // ==================================

    document.getElementById(
        'totalCost'
    ).innerText =

        '₹ ' +

        total.toLocaleString('en-IN');

    document.getElementById(
        'builtupArea'
    ).innerText =

        builtupArea +

        ' sqft';

    document.getElementById(
        'timeline'
    ).innerText =

        timeline;

    document.getElementById(
        'package'
    ).innerText =

        packageName;

    document.getElementById(
        'materialGrade'
    ).innerText =

        material;
}

// ======================================
// AJAX CONTACT FORM
// ======================================

async function submitContactForm(form) {

    try {

        const formData =
        new FormData(form);

        const response =
        await fetch(

            form.action,

            {

                method: 'POST',

                body: formData
            }
        );

        const data =
        await response.json();

        if(data.success){

            alert(data.message);

            form.reset();

        } else {

            alert(data.message);
        }

    } catch(error){

        console.error(error);

        alert('Something went wrong');
    }
}

// ======================================
// AUTO HIDE ALERTS
// ======================================

setTimeout(function(){

    document
    .querySelectorAll('.alert')

    .forEach(function(alert){

        alert.style.opacity = '0';

        setTimeout(function(){

            alert.remove();

        },400);
    });

},5000);