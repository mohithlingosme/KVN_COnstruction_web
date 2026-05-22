/* =========================================================
   KVN CONSTRUCTION — ADMIN PANEL JS
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

    if(toggleBtn){

        toggleBtn.addEventListener('click', function(){

            sidebar.classList.toggle('active');
        });
    }
}

/* =========================================================
   DROPDOWN HANDLING
========================================================= */

function initializeDropdowns()
{
    const dropdowns =
    document.querySelectorAll('.dropdown-toggle');

    dropdowns.forEach(function(dropdown){

        dropdown.addEventListener('click', function(){

            const parent =
            this.parentElement;

            parent.classList.toggle('show');
        });
    });
}

/* =========================================================
   ALERT CLOSE
========================================================= */

function initializeAlerts()
{
    const alerts =
    document.querySelectorAll('.alert-close');

    alerts.forEach(function(button){

        button.addEventListener('click', function(){

            const alert =
            this.closest('.alert');

            alert.style.opacity = '0';

            setTimeout(function(){

                alert.remove();

            },300);
        });
    });
}

/* =========================================================
   DELETE CONFIRMATION
========================================================= */

function initializeDeleteConfirm()
{
    const deleteButtons =
    document.querySelectorAll('.btn-delete');

    deleteButtons.forEach(function(button){

        button.addEventListener('click', function(e){

            const confirmed =
            confirm(
                'Are you sure you want to delete this item?'
            );

            if(!confirmed){

                e.preventDefault();
            }
        });
    });
}

/* =========================================================
   BOOTSTRAP TOOLTIPS
========================================================= */

function initializeTooltips()
{
    const tooltipTriggerList =
    [].slice.call(
        document.querySelectorAll(
            '[data-bs-toggle="tooltip"]'
        )
    );

    tooltipTriggerList.map(function (tooltipTriggerEl) {

        return new bootstrap.Tooltip(
            tooltipTriggerEl
        );
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

        input.addEventListener('keyup', function(){

            const searchValue =
            this.value.toLowerCase();

            const table =
            document.querySelector(
                this.dataset.table
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
                text.includes(searchValue)
                ? ''
                : 'none';
            });
        });
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

            alert.style.opacity = '0';

            setTimeout(function(){

                alert.remove();

            },300);

        },4000);
    });
}

/* =========================================================
   AJAX HELPER
========================================================= */

async function ajaxRequest(url, method = 'GET', data = null)
{
    const options = {

        method: method,

        headers: {

            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    if(data){

        options.headers['Content-Type'] =
        'application/json';

        options.body =
        JSON.stringify(data);
    }

    try {

        const response =
        await fetch(url, options);

        return await response.json();

    } catch(error){

        console.error(
            'AJAX ERROR:',
            error
        );

        return {

            success:false,

            message:'Something went wrong.'
        };
    }
}

/* =========================================================
   SHOW LOADER
========================================================= */

function showLoader()
{
    let loader =
    document.getElementById('globalLoader');

    if(loader){

        loader.style.display = 'flex';
    }
}

/* =========================================================
   HIDE LOADER
========================================================= */

function hideLoader()
{
    let loader =
    document.getElementById('globalLoader');

    if(loader){

        loader.style.display = 'none';
    }
}

/* =========================================================
   TOAST NOTIFICATION
========================================================= */

function showToast(message, type = 'success')
{
    const toast =
    document.createElement('div');

    toast.className =
    `admin-toast ${type}`;

    toast.innerHTML =
    message;

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

function togglePassword(inputId, icon)
{
    const input =
    document.getElementById(inputId);

    if(!input){

        return;
    }

    if(input.type === 'password'){

        input.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');

    }else{

        input.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');
    }
}

/* =========================================================
   CHARACTER COUNTER
========================================================= */

function initializeCharacterCounter()
{
    const textareas =
    document.querySelectorAll('[data-counter]');

    textareas.forEach(function(textarea){

        const counter =
        document.querySelector(
            textarea.dataset.counter
        );

        textarea.addEventListener('input', function(){

            if(counter){

                counter.innerText =
                textarea.value.length;
            }
        });
    });
}

/* =========================================================
   SLUG GENERATOR
========================================================= */

function generateSlug(input, target)
{
    const slug =
    input.value

    .toLowerCase()

    .replace(/[^\w ]+/g,'')

    .replace(/ +/g,'-');

    document.getElementById(target).value =
    slug;
}

/* =========================================================
   SELECT ALL CHECKBOX
========================================================= */

function initializeSelectAll()
{
    const selectAll =
    document.querySelector('.select-all');

    if(!selectAll){

        return;
    }

    selectAll.addEventListener('change', function(){

        const checkboxes =
        document.querySelectorAll('.row-checkbox');

        checkboxes.forEach(function(checkbox){

            checkbox.checked =
            selectAll.checked;
        });
    });
}