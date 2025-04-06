// Profile dropdown toggle function
function profiledropdown() {
  let dropdown = document.getElementById('profileList');
  dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}

// Function to open the popup
function openPopup() {
  fetch("addTaskpop.html")
    .then(response => response.text())
    .then(data => {
      const popupContainer = document.getElementById("popup-container");
      popupContainer.innerHTML = data;

      const popup = document.getElementById("taskPopup");
      popup.style.display = "block";

      const closeButton = document.querySelector(".addTask-close-btn");
      const cancelButton = document.getElementById("cancel-task");

      closeButton.addEventListener("click", function () {
        popup.style.display = "none";
      });
      cancelButton.addEventListener("click", function () {
        popup.style.display = "none";
      });

      attachPopupEventListeners();

      makePopupDraggable(popup);
    })
    .catch(error => {
      console.error("Error loading popup:", error);
    });
}

// Event listener for the popup button
const openPopupBtn = document.getElementById("openPopupBtn");
if (openPopupBtn) {
  openPopupBtn.addEventListener("click", openPopup);
}

// Optional: Gantt task creation
document.getElementById('add-task-btn')?.addEventListener('click', function () {
  const newTaskId = gantt.addTask({
    id: gantt.uid(),
    text: 'New Task',
    start_date: gantt.date.date_to_str('%d-%m-%Y')(new Date()),
    duration: 5,
    progress: 0,
    type: 'task',
    priority: 3,
  });
  gantt.updateTask(newTaskId);
});

// ---- Popup-specific logic ----

let isDragging = false;

function makePopupDraggable(popup) {
  const popupContent = popup.querySelector(".popup-content");
  const header = popupContent.querySelector("h2");
  let offsetX, offsetY;

  header.addEventListener('mousedown', function (e) {
    isDragging = false;

    // ✅ FIX: Get current position BEFORE removing transform
    const rect = popupContent.getBoundingClientRect();
    popupContent.style.left = `${rect.left}px`;
    popupContent.style.top = `${rect.top}px`;
    popupContent.style.transform = 'none'; // Remove centering to prevent jump
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



window.addEventListener("click", function (event) {
  const popupContent = document.querySelector(".popup-content");
  if (!popupContent?.contains(event.target) && !isDragging) {
    const popup = document.getElementById("taskPopup");
    if (popup) popup.style.display = "none";
  }
});

// End Date logic
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

function attachPopupEventListeners() {
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
}
