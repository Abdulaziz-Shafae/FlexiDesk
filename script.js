function dropdownToggle() {
    let dropdown = document.getElementById("profileList");
    dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
}



// Load the modal from addTaskpop.html
function loadModal() {
    fetch("addTaskpop.html")
        .then(response => response.text())
        .then(data => {
            document.getElementById("modal-container").innerHTML = data;
        })
        .catch(error => console.error("Error loading modal:", error));
}

// Open Modal
function openModal() {
    document.getElementById("modal-bg").style.display = "flex";
}

// Close Modal
function closeModal() {
    document.getElementById("modal-bg").style.display = "none";
}

// Load modal when the page loads
window.onload = loadModal;
