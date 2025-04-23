// Function to toggle profile dropdown
function profiledropdown() {
  var profileList = document.getElementById('profileList');
  profileList.style.display = profileList.style.display === 'block' ? 'none' : 'block';
}

// Open the popup - for both Add and Edit modes
function openPopup(task = null, projectID = null) {
  fetch("addTaskpop.html")
    .then(response => response.text())
    .then(data => {
      const popupContainer = document.getElementById("popup-container");
      popupContainer.innerHTML = data;

      const popup = document.getElementById("taskPopup");
      popup.style.display = "block";

      const closeButton = document.querySelector(".addTask-close-btn");
      const cancelButton = document.getElementById("cancel-task");

      closeButton.addEventListener("click", () => popup.style.display = "none");
      cancelButton.addEventListener("click", () => popup.style.display = "none");

      // If editing, fill the fields with task data
      if (task) {
        document.querySelector(".popup-header").textContent = "Edit Task";
        document.getElementById("projectID").value = task.projectID;
        document.getElementById("task-name").value = task.taskName;
        document.getElementById("task-type").value = task.taskType;
        document.getElementById("priority").value = task.priority;
        document.getElementById("assignTo").value = task.assignedTo;
        document.getElementById("taskDescription").value = task.taskDescription;
        // TODO: convert task.startDate and endDate to day/month/year/duration
        document.getElementById("save-task").dataset.edit = "true";
        document.getElementById("save-task").dataset.taskid = task.taskID;
        document.getElementById("delete-task").style.display = "inline-block";
        document.getElementById("delete-task").dataset.taskid = task.taskID;
      } else {
        // If adding new task
        document.querySelector(".popup-header").textContent = "Add New Task";
        document.getElementById("projectID").value = projectID;
        document.getElementById("delete-task").style.display = "none";
        document.getElementById("save-task").dataset.edit = "false";
      }

      attachPopupEventListeners();
      makePopupDraggable(popup);
    })
    .catch(error => {
      console.error("Error loading popup:", error);
    });
}

// Attach all events inside popup
function attachPopupEventListeners() {
  // Update end date based on start and duration
  document.getElementById('day')?.addEventListener('change', updateEndDate);
  document.getElementById('month')?.addEventListener('change', updateEndDate);
  document.getElementById('year')?.addEventListener('change', updateEndDate);

  document.getElementById('increase-day')?.addEventListener('click', function () {
    let days = parseInt(document.getElementById('days-count').innerText);
    document.getElementById('days-count').innerText = days + 1;
    updateEndDate();
  });

  document.getElementById('decrease-day')?.addEventListener('click', function () {
    let days = parseInt(document.getElementById('days-count').innerText);
    if (days > 1) {
      document.getElementById('days-count').innerText = days - 1;
      updateEndDate();
    }
  });

  updateEndDate();

  // Delete task if editing
  document.getElementById('delete-task')?.addEventListener('click', function () {
    const taskID = this.dataset.taskid;
    if (!taskID) return;

    if (confirm("Are you sure you want to delete this task?")) {
      fetch(`deleteTask.php?id=${taskID}`, { method: 'GET' })
        .then(res => res.text())
        .then(response => {
          alert(response);
          document.getElementById('taskPopup').style.display = 'none';
        
          // 🔁 Refresh tasks in the view
          if (typeof window.refreshTasks === 'function') {
            window.refreshTasks();
          }
        })
        .catch(err => console.error("Error deleting task:", err))
      }
  });

  // Save task (create or update)
  document.getElementById('save-task')?.addEventListener('click', function () {
    const formData = new FormData();
    formData.append('projectID', document.getElementById('projectID').value);
    formData.append('taskName', document.getElementById('task-name').value);
    formData.append('taskType', document.getElementById('task-type').value);
    formData.append('priority', document.getElementById('priority').value);
    formData.append('assignedTo', document.getElementById('assignTo').value);
    formData.append('taskDescription', document.getElementById('taskDescription').value);

    const day = document.getElementById('day').value;
    const month = document.getElementById('month').selectedIndex;
    const year = document.getElementById('year').value;
    const duration = parseInt(document.getElementById('days-count').innerText);
    const startDate = new Date(year, month, day);
    const endDate = new Date(startDate);
    endDate.setDate(startDate.getDate() + duration);
    formData.append('startDate', startDate.toISOString().split('T')[0]);
    formData.append('endDate', endDate.toISOString().split('T')[0]);

    const fileInput = document.getElementById('taskFile');
    if (fileInput?.files.length > 0) {
      formData.append('taskFile', fileInput.files[0]);
    }

    const isEditMode = document.getElementById('save-task').dataset.edit === 'true';
    const url = isEditMode ? 'updateTask.php' : 'createTask.php';

    if (isEditMode) {
      formData.append('taskID', document.getElementById('save-task').dataset.taskid);
    }

    fetch(url, {
      method: 'POST',
      body: formData
    }).then(res => res.text())
    .then(response => {
      alert(response);
      document.getElementById('taskPopup').style.display = 'none';
    
      // 🔁 Refresh tasks in the view
      if (typeof window.refreshTasks === 'function') {
        window.refreshTasks();
      }
    })
    .catch(err => console.error('Error saving task:', err));    
  });
}

// Calculate and update the end date
function updateEndDate() {
  const day = parseInt(document.getElementById('day')?.value);
  const month = document.getElementById('month')?.selectedIndex + 1;
  const year = parseInt(document.getElementById('year')?.value);
  const duration = parseInt(document.getElementById('days-count')?.innerText) || 1;

  if (isNaN(day) || isNaN(month) || isNaN(year)) return;

  const startDate = new Date(year, month - 1, day);
  startDate.setDate(startDate.getDate() + duration);

  const endDateStr = `${startDate.getDate()} ${startDate.toLocaleString('default', { month: 'long' })} ${startDate.getFullYear()}`;
  document.getElementById('end-date').textContent = `End Date ${endDateStr}`;
}

// Make popup draggable
let isDragging = false;
function makePopupDraggable(popup) {
  const popupContent = popup.querySelector(".popup-content");
  const header = popupContent.querySelector("h2");
  let offsetX, offsetY;

  header.addEventListener('mousedown', function (e) {
    isDragging = false;
    const rect = popupContent.getBoundingClientRect();
    popupContent.style.left = `${rect.left}px`;
    popupContent.style.top = `${rect.top}px`;
    popupContent.style.transform = 'none';
    popupContent.style.position = 'fixed';
    offsetX = e.clientX - rect.left;
    offsetY = e.clientY - rect.top;

    function onMouseMove(e) {
      isDragging = true;
      popupContent.style.left = `${e.clientX - offsetX}px`;
      popupContent.style.top = `${e.clientY - offsetY}px`;
    }

    function onMouseUp() {
      setTimeout(() => { isDragging = false }, 100);
      window.removeEventListener('mousemove', onMouseMove);
      window.removeEventListener('mouseup', onMouseUp);
    }

    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
  });
}

// Hide popup when clicking outside
window.addEventListener("click", function (event) {
  const popupContent = document.querySelector(".popup-content");
  if (!popupContent?.contains(event.target) && !isDragging) {
    const popup = document.getElementById("taskPopup");
    if (popup) popup.style.display = "none";
  }
});
