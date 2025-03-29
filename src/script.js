/* eslint-disable no-unused-vars */
//aaaaaaprofile dropdown caller
function profiledropdown() {
  let dropdown = document.getElementById('profileList');
  dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}
/* eslint-enable no-unused-vars */




//popup scripts 

// Get the button that opens the popup
const openPopupBtn = document.getElementById("openPopupBtn");

// Get the container where the popup will be inserted
const popupContainer = document.getElementById("popup-container");

// Show the popup when the button is clicked
openPopupBtn.addEventListener("click", function () {
  // Fetch the task popup HTML content
  fetch("addTaskpop.html")
    .then(response => response.text())
    .then(data => {
      // Insert the popup HTML into the container
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
    })
    .catch(error => {
      console.error("Error loading popup:", error);
    });
});


      // add task for gantt , neeed to make it connected with the dhtmlx
      document
        .getElementById('add-task-btn')
        .addEventListener('click', function () {
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