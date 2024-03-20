document.addEventListener("DOMContentLoaded", () => {
  const checkbox = document.querySelector(".is_recurrent");
  const startDateLabel = document.querySelector(
    'label[for="event_start_date"]'
  );
  const endDateLabel = document.querySelector('label[for="event_end_date"]');
  const frequencyLabel = document.querySelector('label[for="event_frequency"]');
  const endDateField = document.getElementById("event_end_date");
  const frequencyField = document.getElementById("event_frequency");

  const toggleFields = () => {
    const isRecurrentChecked = checkbox.checked;

    endDateField.style.display = isRecurrentChecked ? "block" : "none";
    frequencyField.style.display = isRecurrentChecked ? "block" : "none";

    endDateLabel.style.display = isRecurrentChecked ? "block" : "none";
    frequencyLabel.style.display = isRecurrentChecked ? "block" : "none";
  };

  checkbox.addEventListener("change", toggleFields);

  toggleFields();
});
