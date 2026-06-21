/* =========================================================
   KVN CONSTRUCTION PLATFORM
   COMPLETE ADMIN PANEL SCRIPT
========================================================= */

document.addEventListener('DOMContentLoaded', function(){

    initializeSidebar();

    initializeDropdowns();

    initializeAlerts();

    initializeDeleteConfirm();

    initializeTooltips();

    initializeImagePreview();

    initializeTableSearch();

    initializeAutoDismissAlerts();

    initializeCharacterCounter();

    initializeSelectAll();

    initializeFormValidation();

    initializeModalClose();

    initializeMobileSidebarClose();
});

/* =========================================================
   SIDEBAR TOGGLE
========================================================= */

function initializeSidebar()
{
    const sidebar =
    document.querySelector('.admin-sidebar');

    const toggleBtn =
    document.getElementById('sidebarToggle');

    if(!sidebar || !toggleBtn){

        return;
    }

    toggleBtn.addEventListener('click', function(){

        sidebar.classList.toggle('active');
    });
}

/* =========================================================
   MOBILE SIDEBAR CLOSE
========================================================= */

function initializeMobileSidebarClose()
{
    document.addEventListener('click', function(e){

        const sidebar =
        document.querySelector('.admin-sidebar');

        const toggle =
        document.getElementById('sidebarToggle');

        if(

            window.innerWidth < 992

            &&

            sidebar

            &&

            sidebar.classList.contains('active')

            &&

            !sidebar.contains(e.target)

            &&

            !toggle.contains(e.target)
        ){

            sidebar.classList.remove('active');
        }
    });
}

/* =========================================================
   DROPDOWNS
========================================================= */

function initializeDropdowns()
{
    const dropdowns =
    document.querySelectorAll('.dropdown-toggle');

    dropdowns.forEach(function(dropdown){

        dropdown.addEventListener('click', function(e){

            e.preventDefault();

            e.stopPropagation();

            const parent =
            this.parentElement;

            closeAllDropdowns();

            parent.classList.toggle('show');
        });
    });

    document.addEventListener('click', function(){

        closeAllDropdowns();
    });
}

function closeAllDropdowns()
{
    document.querySelectorAll('.dropdown')

    .forEach(function(dropdown){

        dropdown.classList.remove('show');
    });
}

/* =========================================================
   ALERT CLOSE
========================================================= */

function initializeAlerts()
{
    document.addEventListener('click', function(e){

        if(e.target.classList.contains('alert-close')){

            const alert =
            e.target.closest('.alert');

            dismissElement(alert);
        }
    });
}

/* =========================================================
   AUTO DISMISS ALERTS
========================================================= */

function initializeAutoDismissAlerts()
{
    const alerts =
    document.querySelectorAll('.alert-auto-dismiss');

    alerts.forEach(function(alert){

        setTimeout(function(){

            dismissElement(alert);

        },4000);
    });
}

/* =========================================================
   DISMISS ELEMENT
========================================================= */

function dismissElement(element)
{
    if(!element){

        return;
    }

    element.style.opacity = '0';

    element.style.transition = '0.3s';

    setTimeout(function(){

        element.remove();

    },300);
}

/* =========================================================
   DELETE CONFIRMATION
========================================================= */

function initializeDeleteConfirm()
{
    document.addEventListener('click', function(e){

        const button =
        e.target.closest('.btn-delete');

        if(button){

            const message =
            button.dataset.message
            ||
            'Are you sure you want to delete this item?';

            const confirmed =
            confirm(message);

            if(!confirmed){

                e.preventDefault();
            }
        }
    });
}

/* =========================================================
   TOOLTIPS
========================================================= */

function initializeTooltips()
{
    if(typeof bootstrap === 'undefined'){

        return;
    }

    const tooltipTriggerList =
    [].slice.call(

        document.querySelectorAll(

            '[data-bs-toggle="tooltip"]'
        )
    );

    tooltipTriggerList.map(function(element){

        return new bootstrap.Tooltip(element);
    });
}

/* =========================================================
   IMAGE PREVIEW
========================================================= */

function initializeImagePreview()
{
    const imageInputs =
    document.querySelectorAll('.image-input');

    imageInputs.forEach(function(input){

        input.addEventListener('change', function(e){

            const file =
            e.target.files[0];

            if(!file){

                return;
            }

            if(!file.type.startsWith('image/')){

                showToast(

                    'Invalid image file.',

                    'danger'
                );

                return;
            }

            const reader =
            new FileReader();

            reader.onload = function(event){

                const preview =
                input.parentElement.querySelector(
                    '.image-preview'
                );

                if(preview){

                    preview.src =
                    event.target.result;

                    preview.style.display =
                    'block';
                }
            };

            reader.readAsDataURL(file);
        });
    });
}

/* =========================================================
   TABLE SEARCH
========================================================= */

function initializeTableSearch()
{
    const searchInputs =
    document.querySelectorAll('.table-search');

    searchInputs.forEach(function(input){

        input.addEventListener(

            'keyup',

            debounce(function(){

                const value =
                input.value.toLowerCase();

                const table =
                document.querySelector(
                    input.dataset.table
                );

                if(!table){

                    return;
                }

                const rows =
                table.querySelectorAll('tbody tr');

                rows.forEach(function(row){

                    const text =
                    row.innerText.toLowerCase();

                    row.style.display =

                    text.includes(value)

                    ? ''

                    : 'none';
                });

            },300)
        );
    });
}

/* =========================================================
   DEBOUNCE
========================================================= */

function debounce(callback, delay)
{
    let timeout;

    return function(){

        clearTimeout(timeout);

        timeout = setTimeout(

            callback,

            delay
        );
    };
}

/* =========================================================
   AJAX HELPER
========================================================= */

async function ajaxRequest(

    url,

    method = 'GET',

    data = null
)
{
    showLoader();

    const csrfToken =
    document.querySelector(

        'meta[name="csrf-token"]'
    )?.content;

    const options = {

        method: method,

        headers: {

            'X-Requested-With':
            'XMLHttpRequest',

            'Accept':
            'application/json'
        }
    };

    if(csrfToken){

        options.headers['X-CSRF-TOKEN'] =
        csrfToken;
    }

    if(data){

        options.headers['Content-Type'] =
        'application/json';

        options.body =
        JSON.stringify(data);
    }

    try {

        const response =
        await fetch(url, options);

        const result =
        await response.json();

        hideLoader();

        return result;

    } catch(error){

        hideLoader();

        console.error(error);

        return {

            success:false,

            message:'Request failed.'
        };
    }
}

/* =========================================================
   LOADER
========================================================= */

function showLoader()
{
    const loader =
    document.getElementById('globalLoader');

    if(loader){

        loader.style.display = 'flex';
    }
}

function hideLoader()
{
    const loader =
    document.getElementById('globalLoader');

    if(loader){

        loader.style.display = 'none';
    }
}

/* =========================================================
   TOAST
========================================================= */

function showToast(message, type = 'success')
{
    const toast =
    document.createElement('div');

    toast.className =
    `admin-toast ${type}`;

    toast.innerHTML = `
        <div class="toast-content">
            ${message}
        </div>
    `;

    document.body.appendChild(toast);

    setTimeout(function(){

        toast.classList.add('show');

    },100);

    setTimeout(function(){

        toast.classList.remove('show');

        setTimeout(function(){

            toast.remove();

        },300);

    },4000);
}

/* =========================================================
   PASSWORD TOGGLE
========================================================= */

function togglePassword(inputId, iconId)
{
    const input =
    document.getElementById(inputId);

    const icon =
    document.getElementById(iconId);

    if(!input){

        return;
    }

    if(input.type === 'password'){

        input.type = 'text';

        if(icon){

            icon.classList.remove('bi-eye');

            icon.classList.add('bi-eye-slash');
        }

    }else{

        input.type = 'password';

        if(icon){

            icon.classList.remove('bi-eye-slash');

            icon.classList.add('bi-eye');
        }
    }
}

/* =========================================================
   CHARACTER COUNTER
========================================================= */

function initializeCharacterCounter()
{
    const inputs =
    document.querySelectorAll('[data-counter]');

    inputs.forEach(function(input){

        const counter =
        document.querySelector(
            input.dataset.counter
        );

        if(counter){

            counter.innerText =
            input.value.length;
        }

        input.addEventListener('input', function(){

            if(counter){

                counter.innerText =
                input.value.length;
            }
        });
    });
}

/* =========================================================
   SLUG GENERATOR
========================================================= */

function generateSlug(input, targetId)
{
    const slug =
    input.value

    .toLowerCase()

    .trim()

    .replace(/[^\w ]+/g,'')

    .replace(/\s+/g,'-');

    const target =
    document.getElementById(targetId);

    if(target){

        target.value = slug;
    }
}

/* =========================================================
   SELECT ALL
========================================================= */

function initializeSelectAll()
{
    const selectAll =
    document.querySelector('.select-all');

    if(!selectAll){

        return;
    }

    selectAll.addEventListener('change', function(){

        document.querySelectorAll('.row-checkbox')

        .forEach(function(checkbox){

            checkbox.checked =
            selectAll.checked;
        });
    });
}

/* =========================================================
   FORM VALIDATION
========================================================= */

function initializeFormValidation()
{
    const forms =
    document.querySelectorAll('.needs-validation');

    forms.forEach(function(form){

        form.addEventListener('submit', function(e){

            if(!form.checkValidity()){

                e.preventDefault();

                e.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });
}

/* =========================================================
   MODAL CLOSE
========================================================= */

function initializeModalClose()
{
    document.addEventListener('click', function(e){

        if(e.target.classList.contains('modal-close')){

            const modal =
            e.target.closest('.modal');

            if(modal){

                modal.style.display = 'none';
            }
        }
    });
}