// Toggle dropdown (optional)
function profiledropdown() {
  const profileList = document.getElementById('profileList');
  profileList.style.display = profileList.style.display === 'block' ? 'none' : 'block';
}

// Global dragging flag
let isDragging = false;

// Function to open the popup for adding a new task
function openAddTaskPopup(projectID) {
  // Hide the tooltip if it's visible
  const tooltip = document.getElementById("custom-tooltip");
  if (tooltip) tooltip.style.display = "none";

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

      // Initialize date handling
      initializeDateSelectors(projectID);

      // Add event listeners for saving and cancelling the task
      attachPopupEventListeners();
      makePopupDraggable(popup); // Make popup draggable
    });
}

// Function to open the popup for editing an existing task
function openEditTaskPopup(task, projectID) {
  // Hide the tooltip first
  const tooltip = document.getElementById("custom-tooltip");
  if (tooltip) tooltip.style.display = "none";
  
  // Hide any task-popup that might be open (for table view)
  const taskPopup = document.getElementById("task-popup");
  if (taskPopup) taskPopup.style.display = "none";

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
      document.getElementById("taskName").value = task.taskName || ''; // Task Name
      document.getElementById("taskType").value = task.taskType || 'Task'; // Task Type
      document.getElementById("priority").value = task.priority || 'Medium'; // Priority
      document.getElementById("taskDescription").value = task.description || ''; // Task Description
      document.getElementById("status").value = task.status || 'Pending'; // Task Status
      document.getElementById("taskStartDate").value = task.startDate || ''; // Task start date
      document.getElementById("taskEndDate").value = task.endDate || ''; // Task end date

      // Load Assign To List and preselect the current assignee
      loadAssignToList(projectID, task.assignedTo); // Pass the current assignee to preselect

      // Initialize date handling
      initializeDateSelectors(projectID, task.startDate, task.endDate);

      document.getElementById("save-task").dataset.edit = "true"; // Mark as editing existing task
      document.getElementById("delete-task").style.display = "inline-block"; // Show delete button
      document.getElementById("delete-task").dataset.taskID = task.taskID; // Set taskID for deletion
      document.getElementById("save-task").dataset.taskID = task.taskID; // Set taskID for saving 

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

// Function to update the task popup event listeners
function attachPopupEventListeners() {
  // Delete task
  document.getElementById("delete-task").onclick = function () {
    const id = this.dataset.taskID;
    if (confirm("Are you sure you want to delete this task?")) {
      // Show a loading notification
      showNotification('info', 'Processing', 'Deleting task...');
      
      fetch(`deleteTask.php?id=${id}`)
        .then(res => {
          if (!res.ok) {
            throw new Error('Server responded with an error');
          }
          return res.text();
        })
        .then(data => {
          // Close the popup first
          document.getElementById("taskPopup").style.display = "none";
          
          // Show success notification
          showNotification('success', 'Success', 'Task deleted successfully');
          
          // Refresh the appropriate view based on the current page
          refreshCurrentView();
        })
        .catch(error => {
          console.error("Delete task error:", error);
          showNotification('error', 'Error', 'Failed to delete task. Please try again.');
        });
    }
  };

  // Save task (either create or update)
  document.getElementById("save-task").onclick = function () {
    // Show a loading notification
    showNotification('info', 'Processing', 'Saving task...');
    
    const formData = new FormData();
    formData.append("projectID", document.getElementById("projectID").value);
    formData.append("taskName", document.getElementById("taskName").value);
    formData.append("taskType", document.getElementById("taskType").value);
    formData.append("priority", document.getElementById("priority").value);
    formData.append("assignedTo", document.getElementById("assignTo").value);
    formData.append("status", document.getElementById("status").value);
    formData.append("taskDescription", document.getElementById("taskDescription").value);
    formData.append("startDate", document.getElementById('taskStartDate').value); 
    formData.append("endDate", document.getElementById('taskEndDate').value); 

    // Check if a file is uploaded
    const fileInput = document.getElementById("taskFile");
    if (fileInput && fileInput.files.length > 0) {
      formData.append("taskFile", fileInput.files[0]);
    }

    const isEdit = this.dataset.edit === "true";
    const taskID = this.dataset.taskID; 
    const url = isEdit ? "updateTask.php" : "createTask.php";

    if (isEdit) formData.append("taskID", taskID);

    // Submit the form data
    fetch(url, { 
      method: "POST", 
      body: formData 
    })
    .then(res => {
      if (!res.ok) {
        throw new Error('Server responded with an error');
      }
      return res.text();
    })
    .then(data => {
      // Close the popup first
      document.getElementById("taskPopup").style.display = "none";
      
      // Show success notification
      if(isEdit){
        showNotification('success', 'Success', 'Task updated successfully');
      } else {
        showNotification('success', 'Success', 'Task added successfully');
      }
      
      // Refresh the appropriate view based on the current page
      refreshCurrentView();
    })
    .catch(error => {
      console.error("Save task error:", error);
      showNotification('error', 'Error', 'Failed to save task. Please try again.');
    });
  };

  // Cancel task with improved handling
  document.getElementById("cancel-task").onclick = function () {
    // Hide the task popup
    document.getElementById("taskPopup").style.display = "none";
  };
}

// Function to detect the current view and refresh it
function refreshCurrentView() {
  // Check if we're on the Gantt view page
  if (document.getElementById('gantt_here')) {
    // Use the Gantt refresh function
    if (typeof window.refreshGanttData === 'function') {
      window.refreshGanttData();
    } else if (typeof refreshGanttData === 'function') {
      refreshGanttData();
    } else {
      console.warn("Gantt refresh function not found, using fallback");
      setTimeout(() => {
        if (typeof loadGanttTasks === 'function') {
          loadGanttTasks();
        } else {
          location.reload();
        }
      }, 500);
    }
  } 
  // Check if we're on the Table view page
  else if (document.querySelector('#task-table')) {
    // Use the Table refresh function
    if (typeof window.refreshTableData === 'function') {
      window.refreshTableData();
    } else if (typeof refreshTableData === 'function') {
      refreshTableData();
    } else {
      console.warn("Table refresh function not found, using fallback");
      if (typeof getTasks === 'function') {
        getTasks();
      } else {
        location.reload();
      }
    }
  }
  // If we can't identify the view, try a generic refresh approach
  else {
    console.warn("Unknown view type, attempting page reload");
    setTimeout(() => {
      location.reload();
    }, 500);
  }
}

// Make the popup draggable
function makePopupDraggable(popup) {
  const header = popup.querySelector("h2");
  const content = popup.querySelector(".popup-content");
  let offsetX, offsetY;
  let isDragging = false;

  header.addEventListener("mousedown", function (e) {
    // Only start dragging on primary mouse button (prevent issues with right-click)
    if (e.button !== 0) return;
    
    isDragging = true;
    const rect = content.getBoundingClientRect();
    content.style.left = `${rect.left}px`;
    content.style.top = `${rect.top}px`;
    content.style.transform = "none";
    content.style.position = "fixed";

    offsetX = e.clientX - rect.left;
    offsetY = e.clientY - rect.top;

    function onMouseMove(e) {
      if (!isDragging) return;
      content.style.left = `${e.clientX - offsetX}px`;
      content.style.top = `${e.clientY - offsetY}px`;
    }

    function onMouseUp() {
      isDragging = false;
      window.removeEventListener("mousemove", onMouseMove);
      window.removeEventListener("mouseup", onMouseUp);
    }

    window.addEventListener("mousemove", onMouseMove);
    window.addEventListener("mouseup", onMouseUp);
  });
}

// Hide popup on outside click with improved handling
window.addEventListener("click", function (event) {
  const taskPopup = document.getElementById("taskPopup");
  if (!taskPopup) return;
  
  const content = taskPopup.querySelector(".popup-content");
  
  // Check if the click is outside the popup content
  if (content && !content.contains(event.target) && event.target.tagName !== 'TR' && !event.target.closest('tr')) {
    taskPopup.style.display = "none";
  }
});

// Hide popup on outside click
window.addEventListener("click", function (event) {
  const content = document.querySelector(".popup-content");
  if (content && !content.contains(event.target)) {
    const popup = document.getElementById("taskPopup");
    if (popup) popup.style.display = "none";
  }
});

function initializeDateSelectors(projectID, taskStartDate = null, taskEndDate = null) {
  // DOM elements
  const daySelect = document.getElementById('day');
  const monthSelect = document.getElementById('month');
  const yearSelect = document.getElementById('year');
  const increaseBtn = document.getElementById('increase-day');
  const decreaseBtn = document.getElementById('decrease-day');
  const daysCount = document.getElementById('days-count');
  const endDateDiv = document.getElementById('end-date');

  let selectedStartDate;
  let selectedEndDate;
  let projectStartDate;
  let projectEndDate;
  let isInitialLoad = true; // Flag to track initial load vs user changes

  // Populate selects
  function populateDateOptions() {
    // Clear existing options
    daySelect.innerHTML = '';
    monthSelect.innerHTML = '';
    yearSelect.innerHTML = '';
  
    const projectStartYear = projectStartDate.getFullYear();
    const projectEndYear = projectEndDate.getFullYear();
  
    // Populate Years
    for (let i = projectStartYear; i <= projectEndYear; i++) {
      const option = document.createElement('option');
      option.value = i;
      option.textContent = i;
      yearSelect.appendChild(option);
    }
  
    // Populate Months
    const selectedYear = parseInt(yearSelect.value);
  
    for (let i = 1; i <= 12; i++) {
      // Convert to 0-based for comparison with getMonth()
      const monthIndex = i - 1;
      
      // Skip months outside the valid range for the selected year
      if (
        (selectedYear === projectStartYear && monthIndex < projectStartDate.getMonth()) || 
        (selectedYear === projectEndYear && monthIndex > projectEndDate.getMonth())
      ) {
        continue;
      }
  
      const option = document.createElement('option');
      option.value = i; // Keep as 1-indexed for display
      option.textContent = i;
      monthSelect.appendChild(option);
    }
  
    // Update days based on selected month and year
    updateDays();
  }
  
  // Recalculate and populate days based on selected month and year
  function updateDays() {
    const selectedYear = parseInt(yearSelect.value);
    const selectedMonth = parseInt(monthSelect.value) - 1; // Convert to 0-indexed
    const lastDayOfMonth = new Date(selectedYear, selectedMonth + 1, 0).getDate();  // Get last day of selected month

    // Clear previous days
    daySelect.innerHTML = '';

    // Define start and end days for the month
    let startDay = 1;
    let endDay = lastDayOfMonth;

    // If the selected year and month match the project's start month
    if (selectedYear === projectStartDate.getFullYear() && selectedMonth === projectStartDate.getMonth()) {
        startDay = projectStartDate.getDate();  // Start from project start day
    }

    // If the selected year and month match the project's end month
    if (selectedYear === projectEndDate.getFullYear() && selectedMonth === projectEndDate.getMonth()) {
        endDay = projectEndDate.getDate();  // End at project end day
    }

    // Populate the days dropdown
    for (let i = startDay; i <= endDay; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        daySelect.appendChild(option);
    }

    // Automatically select the first day if it's available
    if (isInitialLoad) {
        daySelect.value = projectStartDate.getDate();
        isInitialLoad = false;
    }
  }

  
  // Initialize date options when page loads or after month/year change
  monthSelect.addEventListener('change', updateDays);
  yearSelect.addEventListener('change', updateDays);
  
  // Update end date and hidden fields
  function updateEndDate(resetDays = false) {
    // Get current selected date (month is 1-indexed in dropdown, convert to 0-indexed)
    const day = parseInt(daySelect.value);
    const month = parseInt(monthSelect.value) - 1; // Convert to 0-based
    const year = parseInt(yearSelect.value);

    selectedStartDate = new Date(year, month, day);

    if (resetDays) {
      daysCount.textContent = 0;
    }

    selectedEndDate = new Date(selectedStartDate);
    selectedEndDate.setDate(selectedStartDate.getDate() + parseInt(daysCount.textContent));

    // Limit endDate to projectEndDate
    if (selectedEndDate > projectEndDate) {
      selectedEndDate = new Date(projectEndDate);
      const diffTime = Math.abs(selectedEndDate - selectedStartDate);
      const correctedDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
      daysCount.textContent = correctedDays;
    }

    // Update DOM with 1-indexed month for display
    endDateDiv.textContent = `${selectedEndDate.getDate()} / ${selectedEndDate.getMonth() + 1} / ${selectedEndDate.getFullYear()}`;

    // Format dates for form submission (YYYY-MM-DD)
    document.getElementById('taskStartDate').value = formatDateForInput(selectedStartDate);
    document.getElementById('taskEndDate').value = formatDateForInput(selectedEndDate);
  }
  
  // Helper function to format dates as YYYY-MM-DD
  function formatDateForInput(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  }

  function setupHoldButton(button, action) {
    let intervalId;
  
    button.addEventListener('mousedown', () => {
      action(); // Immediate action
      intervalId = setInterval(action, 200); // Repeat every 200ms
    });
  
    button.addEventListener('mouseup', () => {
      clearInterval(intervalId);
    });
  
    button.addEventListener('mouseleave', () => {
      clearInterval(intervalId);
    });
  
    button.addEventListener('touchstart', (e) => {
      e.preventDefault();
      action();
      intervalId = setInterval(action, 200);
    }, { passive: false });    
  
    button.addEventListener('touchend', () => {
      clearInterval(intervalId);
    });
  }

  // Button actions
  setupHoldButton(increaseBtn, () => {
    const maxDays = Math.floor((projectEndDate - selectedStartDate) / (1000 * 60 * 60 * 24));
    if (parseInt(daysCount.textContent) < maxDays) {
      daysCount.textContent = parseInt(daysCount.textContent) + 1;
      updateEndDate();
    }
  });
  
  setupHoldButton(decreaseBtn, () => {
    if (parseInt(daysCount.textContent) > 0) {
      daysCount.textContent = parseInt(daysCount.textContent) - 1;
      updateEndDate();
    }
  });
  
  daySelect.addEventListener('change', () => updateEndDate(true));
  monthSelect.addEventListener('change', () => updateEndDate(true));
  yearSelect.addEventListener('change', () => updateEndDate(true));

  // Fetch the project dates
  fetch('getProjectDate.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ projectID: projectID })
  })
  .then(response => response.json())
  .then(data => {
    if (data.error) {
      console.error('Error:', data.error);
      return;
    }

    // Set project start and end dates
    projectStartDate = new Date(data.startDate);
    projectEndDate = new Date(data.endDate);

    // Use task-specific dates if available, else default to project start date
    selectedStartDate = taskStartDate ? new Date(taskStartDate) : new Date(projectStartDate);
    selectedEndDate = taskEndDate ? new Date(taskEndDate) : new Date(projectStartDate);

    // Populate dropdowns
    populateDateOptions();

    // Set default dropdown selections to selectedStartDate
    daySelect.value = selectedStartDate.getDate();
    monthSelect.value = selectedStartDate.getMonth() + 1; // Months are 0-11, add 1 for display
    yearSelect.value = selectedStartDate.getFullYear();

    // Calculate days difference between start and end date
    const diffTime = selectedEndDate - selectedStartDate;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    daysCount.textContent = diffDays;

    // Update the end date display
    updateEndDate();
  })
  .catch(error => {
    console.error('Fetch error:', error);
  });
}

// Show notification function (global)
function showNotification(type, title, message, duration = 3000) {
  // Check if notification banner exists
  let banner = document.getElementById('notification-banner');
  
  // If not, create one (for pages that don't include the notification HTML)
  if (!banner) {
    banner = document.createElement('div');
    banner.id = 'notification-banner';
    banner.className = 'notification-banner';
    banner.innerHTML = `
      <i id="notification-icon" class="fas fa-check-circle"></i>
      <div class="notification-content">
        <h4 id="notification-title">Success</h4>
        <p id="notification-message">Action completed successfully.</p>
      </div>
      <button class="notification-close" onclick="hideNotification()">
        <i class="fas fa-times"></i>
      </button>
    `;
    document.body.appendChild(banner);
    
    // Add the style if not already present
    if (!document.getElementById('notification-style')) {
      const style = document.createElement('style');
      style.id = 'notification-style';
      style.textContent = `
        .notification-banner {
          position: fixed;
          top: 20px;
          left: 50%;
          transform: translateX(-50%);
          padding: 12px 20px;
          border-radius: 8px;
          display: flex;
          align-items: center;
          gap: 12px;
          box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
          z-index: 2000;
          max-width: 400px;
          width: calc(100% - 40px);
          opacity: 0;
          transition: opacity 0.3s ease, transform 0.3s ease;
          pointer-events: none;
        }
        .notification-banner.visible {
          opacity: 1;
          pointer-events: auto;
        }
        .notification-banner.success {
          background-color: #d4edda;
          border-left: 4px solid #28a745;
          color: #155724;
        }
        .notification-banner.error {
          background-color: #f8d7da;
          border-left: 4px solid #dc3545;
          color: #721c24;
        }
        .notification-banner.info {
          background-color: #e6f7ff;
          border-left: 4px solid #1890ff;
          color: #0c53a3;
        }
        .notification-banner i {
          font-size: 24px;
        }
        .notification-content {
          flex: 1;
        }
        .notification-content h4 {
          margin: 0 0 4px 0;
          font-size: 16px;
        }
        .notification-content p {
          margin: 0;
          font-size: 14px;
        }
        .notification-close {
          background: none;
          border: none;
          color: inherit;
          cursor: pointer;
          opacity: 0.7;
          transition: opacity 0.2s;
        }
        .notification-close:hover {
          opacity: 1;
        }
      `;
      document.head.appendChild(style);
    }
  }

  const icon = document.getElementById('notification-icon');
  const titleEl = document.getElementById('notification-title');
  const messageEl = document.getElementById('notification-message');
  
  // Set content
  titleEl.textContent = title;
  messageEl.textContent = message;
  
  // Set type-specific styles
  banner.className = 'notification-banner visible ' + type;
  
  if (type === 'success') {
    icon.className = 'fas fa-check-circle';
  } else if (type === 'error') {
    icon.className = 'fas fa-exclamation-circle';
  } else if (type === 'info') {
    icon.className = 'fas fa-info-circle';
  }
  
  // Auto-hide after duration
  if (duration > 0) {
    setTimeout(hideNotification, duration);
  }
}

// Function to hide notification
function hideNotification() {
  const banner = document.getElementById('notification-banner');
  if (banner) {
    banner.classList.remove('visible');
  }
}

// Function to refresh Gantt chart after task operations
function refreshGanttData() {
  // If we're on the Gantt view page
  if (document.getElementById('gantt_here')) {
    // Check if the gantt global variable is available
    if (typeof gantt !== 'undefined') {
      // Check if the Gantt-specific refresh function exists
      if (typeof window.loadGanttTasks === 'function') {
        window.loadGanttTasks();
        return;
      }
      
      // If the specific refresh function isn't available, fetch and update directly
      const projectID = sessionStorage.getItem("selectedProjectID");
      const status = document.getElementById("status-filter")?.value || "";
      const priority = document.getElementById("priority-filter")?.value || "";
      const onlyMine = document.getElementById("assigned-to-me")?.checked || false;

      fetch("getProjectTask.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ projectID, status, priority, onlyMine })
      })
      .then(res => {
        if (!res.ok) {
          throw new Error('Server responded with an error');
        }
        return res.json();
      })
      .then(tasks => {
        if (gantt && typeof gantt.clearAll === 'function') {
          const data = tasks.map((task) => {
            let startDate = new Date(task.startDate);
            let endDate = new Date(task.endDate);
            
            if (isNaN(startDate.getTime())) startDate = new Date();
            if (isNaN(endDate.getTime()) || endDate < startDate) {
              endDate = new Date(startDate);
              endDate.setDate(endDate.getDate() + 1);
            }

            // Calculate duration in days
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            return {
              id: task.taskID,
              text: task.taskName,
              start_date: startDate,
              end_date: endDate,
              duration: diffDays,
              type: task.taskType === 'Milestone' ? 'milestone' : 'task',
              priority: convertPriorityToNumber(task.priority),
              description: task.description,
              assignedTo: task.assignedTo,
              status: task.status,
              taskType: task.taskType,
              projectID: projectID
            };
          });
          
          gantt.clearAll();
          gantt.parse({ data });
          gantt.render();
        }
      })
      .catch(err => {
        console.error("Failed to refresh tasks:", err);
        showNotification('error', 'Error', 'Failed to refresh tasks. Please try again.');
      });
    } else {
      console.warn("Gantt object not found, attempting page reload as fallback");
      setTimeout(() => {
        // As a fallback, if everything else fails, reload the page
        window.location.reload();
      }, 1000);
    }
  }
}

// Helper function to convert priority string to number
function convertPriorityToNumber(priority) {
  if (typeof priority === 'number') return priority;
  
  switch (priority?.toLowerCase()) {
    case "low": return 1;
    case "medium-low": return 2;
    case "medium": return 3;
    case "high": return 4;
    case "critical": return 5;
    default: return 3;
  }
}

// Make functions globally available
window.showNotification = showNotification;
window.hideNotification = hideNotification;
window.refreshGanttData = refreshGanttData;