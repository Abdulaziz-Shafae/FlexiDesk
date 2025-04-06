/* eslint-disable no-unused-vars */
// Profile dropdown toggle function
function profiledropdown() {
  let dropdown = document.getElementById('profileList');
  dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}
/* eslint-enable no-unused-vars */

// Popup scripts

// Function to open the popup
function openPopup() {
  // Fetch the task popup HTML content
  fetch("addTaskpop.html")
    .then(response => response.text())
    .then(data => {
      // Insert the popup HTML into the container
      const popupContainer = document.getElementById("popup-container");
      popupContainer.innerHTML = data;

      // Now that the popup is in the container, show it
      const popup = document.getElementById("taskPopup");
      popup.style.display = "block"; // Show the popup

      // Add event listeners to close the popup
      const closeButton = document.querySelector(".addTask-close-btn");
      const cancelButton = document.getElementById("cancel-task");

      // Close the popup when clicking the close or cancel button
      closeButton.addEventListener("click", function () {
        popup.style.display = "none";
      });
      cancelButton.addEventListener("click", function () {
        popup.style.display = "none";
      });

      // Close popup if clicked outside of it
      window.addEventListener("click", function (event) {
        if (event.target === popup) {
          popup.style.display = "none";
        }
      });

      // Initialize draggable functionality
      makePopupDraggable(popup);
    })
    .catch(error => {
      console.error("Error loading popup:", error);
    });
}

// Function to make the popup draggable
function makePopupDraggable(popup) {
  const header = popup.querySelector(".popup-header"); // Assuming the header has the class 'popup-header'
  let offsetX, offsetY;

  header.addEventListener('mousedown', function(e) {
    offsetX = e.clientX - popup.getBoundingClientRect().left;
    offsetY = e.clientY - popup.getBoundingClientRect().top;

    function onMouseMove(e) {
      popup.style.position = 'absolute';
      popup.style.left = `${e.clientX - offsetX}px`;
      popup.style.top = `${e.clientY - offsetY}px`;
    }

    function onMouseUp() {
      window.removeEventListener('mousemove', onMouseMove);
      window.removeEventListener('mouseup', onMouseUp);
    }

    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
  });
}

// Event listener for the button that opens the popup
const openPopupBtn = document.getElementById("openPopupBtn");
if (openPopupBtn) {
  openPopupBtn.addEventListener("click", openPopup);
}

// Add task for Gantt chart (ensure 'gantt' is defined elsewhere in your code)
document.getElementById('add-task-btn')?.addEventListener('click', function () {
  const newTaskId = gantt.addTask({
    id: gantt.uid(),
    text: 'New Task',
    start_date: gantt.date.date_to_str('%d-%m-%Y')(new Date()), // Today’s date
    duration: 5,
    progress: 0,
    type: 'task',
    priority: 3, // Medium priority
  });

  gantt.updateTask(newTaskId);
});
