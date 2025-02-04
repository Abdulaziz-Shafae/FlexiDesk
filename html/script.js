// script.js


function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex-1].style.display = "block";
    dots[slideIndex-1].className += " active";
}


document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('settings-icon').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('settings-dropdown').style.display = 
            document.getElementById('settings-dropdown').style.display === 'block' ? 'none' : 'block';
    });

    document.getElementById('profile-icon').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('profile-dropdown').style.display = 
            document.getElementById('profile-dropdown').style.display === 'block' ? 'none' : 'block';
    });
});

function Notification1() {
    alert('This is Notification1.');
}
function Notification2() {
    alert('This is Notification2.');
}
function NotificationAll() {
    alert('This is Notification1.   This is Notification2.');
}
function Arabic() {
    alert('Switched to Arabic.');
}
function Mode() {
    const body = document.body;
    const modeText = document.getElementById('mode-toggle');

    console.log("Toggling mode..."); // Debugging line

    // Toggle dark mode class
    body.classList.toggle('dark-mode');

    // Toggle text content and save mode in localStorage
    if (body.classList.contains('dark-mode')) {
        modeText.textContent = 'Switch to light mode';
        localStorage.setItem('theme', 'dark');
    } else {
        modeText.textContent = 'Switch to dark mode';
        localStorage.setItem('theme', 'light');
    }
}

// Check and apply the saved mode on page load
window.onload = function() {
    const savedTheme = localStorage.getItem('theme');
    const body = document.body;
    const modeText = document.getElementById('mode-toggle');

    console.log("Loading saved theme:", savedTheme); // Debugging line

    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        modeText.textContent = 'Switch to light mode';
    } else {
        modeText.textContent = 'Switch to dark mode';
    }
}



function validateForm() {
    const requiredFields = [
        'firstName', 'lastName', 'phoneNumber', 'phoneNumber2', 'email',
        'password', 'city', 'dob', 'id', 'university', 'college', 'major',
        'gpa', 'gpaScale', 'transcript', 'lor'
    ];
    
    for (const field of requiredFields) {
        const element = document.getElementById(field);
        if (!element.value || (element.type === 'file' && !element.files.length)) {
            alert('Please fill out all fields before submitting.');
            return false;
        }
    }

    return true;
}

function Logout() {
    // Clear session data
    sessionStorage.clear();

    // Clear cookies
    document.cookie.split(";").forEach(function(c) {
        document.cookie = c.trim().split("=")[0] + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    });

    // Redirect to the login page
    window.location.href = 'login.html'; // Replace with your login page URL
}


function renderTable() {
    const table = document.getElementById('statusTable');
    table.innerHTML = '';
    
    const start = currentPage * rowsPerPage;
    const end = Math.min(start + rowsPerPage, dataArray.length);
    
    for (let i = start; i < end; i++) {
        const row = table.insertRow();
        const programCell = row.insertCell(0);
        const statusCell = row.insertCell(1);

        programCell.textContent = dataArray[i].program;
        statusCell.textContent = dataArray[i].status;
        
        if (dataArray[i].status === "Accepted") {
            statusCell.classList.add('accepted');
        } else if (dataArray[i].status === "Denied") {
            statusCell.classList.add('denied');
        }
    }
}

function prevPage() {
    currentPage = (currentPage === 0) ? Math.floor(dataArray.length / rowsPerPage) : currentPage - 1;
    renderTable();
}

function nextPage() {
    currentPage = ((currentPage + 1) * rowsPerPage >= dataArray.length) ? 0 : currentPage + 1;
    renderTable();
}