// Toggle dropdown (optional)
function profiledropdown() {
  const profileList = document.getElementById('profileList');
  profileList.style.display = profileList.style.display === 'block' ? 'none' : 'block';
}

// Global dragging flag
let isDragging = false;

// Function to open the popup for adding a new task
function openAddTaskPopup(projectID) {
  fetch("addTaskpop.html")
    .then(response => response.text())
    .then(data => {
      // Insert the popup HTML into the container
      document.getElementById("popup-container").innerHTML = data;
      const popup = document.getElementById("taskPopup");
      popup.style.display = "block";

      // Set popup events
      document.querySelector(".addTask-close-btn").onclick = () => {
        popup.style.display = "none";
      };
      document.getElementById("cancel-task").onclick = () => {
        popup.style.display = "none";
      };

      // Set the popup header for adding a new task
      document.querySelector(".popup-header").textContent = "Add New Task";
      document.getElementById("projectID").value = projectID;
      document.getElementById("delete-task").style.display = "none"; // Hide delete button for new tasks
      document.getElementById("save-task").dataset.edit = "false"; // Mark as new task

      // Load team members using loadDataTaskCreate.php
      loadAssignToList(projectID); // Reset dropdown

      // Add event listeners for saving and cancelling the task
      attachPopupEventListeners();
      makePopupDraggable(popup); // Make popup draggable
    });
}



// Function to open the popup for editing an existing task
function openEditTaskPopup(task, projectID) {
  fetch("addTaskpop.html")
    .then(response => response.text())
    .then(data => {
      // Insert the popup HTML into the container
      document.getElementById("popup-container").innerHTML = data;
      const popup = document.getElementById("taskPopup");
      popup.style.display = "block";

      // Set popup events
      document.querySelector(".addTask-close-btn").onclick = () => {
        popup.style.display = "none";
      };
      document.getElementById("cancel-task").onclick = () => {
        popup.style.display = "none";
      };

      // Set the popup header for editing a task
      document.querySelector(".popup-header").textContent = "Edit Task";
      document.getElementById("projectID").value = task.projectID;
      document.getElementById("taskName").value = task.taskName; // Task Name
      document.getElementById("taskType").value = task.taskType; // Task Type
      document.getElementById("priority").value = task.priority; // Priority
      document.getElementById("taskDescription").value = task.description; // Task Description
      document.getElementById("status").value = task.status; // Task Status

      // Load Assign To List and preselect the current assignee
      loadAssignToList(projectID, task.assignedTo); // Pass the current assignee to preselect

      
      document.getElementById("save-task").dataset.edit = "true"; // Mark as editing existing task
      document.getElementById("delete-task").style.display = "inline-block"; // Show delete button
      document.getElementById("delete-task").dataset.taskID = task.taskID; // Set taskID for deletion

      // Add event listeners for saving, deleting, and cancelling the task
      attachPopupEventListeners();
      makePopupDraggable(popup); // Make popup draggable
    });
}



// Function to load the "Assign To" dropdown with team members
function loadAssignToList(projectID, selectedUserName = null) {
  const assignToSelect = document.getElementById("assignTo");
  assignToSelect.innerHTML = ''; // Clear previous options

  // Fetch the team members
  fetch(`loadDataTaskCreate.php?members&projectID=${projectID}`)
    .then(response => response.json())
    .then(data => {
      data.members.forEach(member => {
        const option = document.createElement("option");
        option.value = member.userID;
        option.textContent = member.name;

        // Preselect the assignee if the task is being edited (match by name)
        if (selectedUserName && member.name === selectedUserName) {
          option.selected = true; // Preselect the assignee by name
        }

        assignToSelect.appendChild(option);
      });
    })
    .catch(error => console.error("Error loading team members:", error));
}


// Add interactions to popup buttons (Save, Cancel, Delete)
function attachPopupEventListeners() {
  // Delete task
  document.getElementById("delete-task").onclick = function () {
    const id = this.dataset.taskID;
    if (confirm("Are you sure you want to delete this task?")) {
      fetch(`deleteTask.php?id=${id}`)
        .then(res => res.text())
        .then(data => {
          alert('Task deleted successfully.');
          document.getElementById("taskPopup").style.display = "none";
          if (typeof window.refreshTasks === 'function') window.refreshTasks(); // Refresh task list
        });
    }
  };

  // Save task (either create or update)
  document.getElementById("save-task").onclick = function () {
    const formData = new FormData();
    formData.append("projectID", document.getElementById("projectID").value);
    formData.append("taskName", document.getElementById("taskName").value);
    formData.append("taskType", document.getElementById("taskType").value);
    formData.append("priority", document.getElementById("priority").value);
    formData.append("assignedTo", document.getElementById("assignTo").value);
    formData.append("status", document.getElementById("status").value);
    formData.append("taskDescription", document.getElementById("taskDescription").value);
    formData.append("startDate", "2025-04-18" ); // Add start date (you can change this dynamically if needed)
    formData.append("endDate", "2025-04-20"); // Add end date (you can change this dynamically if needed)

    // Check if a file is uploaded
    const fileInput = document.getElementById("taskFile");
    if (fileInput && fileInput.files.length > 0) {
      formData.append("taskFile", fileInput.files[0]);
    }

    const isEdit = this.dataset.edit === "true";
    const taskID = this.dataset.taskID; 
    const url = isEdit ? "updateTask.php" : "createTask.php";
    if (isEdit) formData.append("taskID", task.taskID);

    // Submit the form data
    fetch(url, { method: "POST", body: formData })
      .then(res => res.text())
      .then(data => {
        alert(data.message);
        document.getElementById("taskPopup").style.display = "none"; // Close popup
        if (typeof window.refreshTasks === 'function') window.refreshTasks(); // Refresh task list
      });
  };

  // Cancel task
  document.getElementById("cancel-task").onclick = function () {
    document.getElementById("taskPopup").style.display = "none"; // Close the popup
  };
}

// Make the popup draggable
function makePopupDraggable(popup) {
  const header = popup.querySelector("h2");
  const content = popup.querySelector(".popup-content");
  let offsetX, offsetY;

  header.addEventListener("mousedown", function (e) {
    const rect = content.getBoundingClientRect();
    content.style.left = `${rect.left}px`;
    content.style.top = `${rect.top}px`;
    content.style.transform = "none";
    content.style.position = "fixed";

    offsetX = e.clientX - rect.left;
    offsetY = e.clientY - rect.top;

    function onMouseMove(e) {
      content.style.left = `${e.clientX - offsetX}px`;
      content.style.top = `${e.clientY - offsetY}px`;
    }

    function onMouseUp() {
      window.removeEventListener("mousemove", onMouseMove);
      window.removeEventListener("mouseup", onMouseUp);
    }

    window.addEventListener("mousemove", onMouseMove);
    window.addEventListener("mouseup", onMouseUp);
  });
}

// Hide popup on outside click
window.addEventListener("click", function (event) {
  const content = document.querySelector(".popup-content");
  if (!content?.contains(event.target)) {
    const popup = document.getElementById("taskPopup");
    if (popup) popup.style.display = "none";
  }
});


