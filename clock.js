function updateTime() {
  const date = new Date();
  let hours = date.getHours();  // Get hours (24-hour format)
  let minutes = date.getMinutes();
  let seconds = date.getSeconds();
  let ampm = hours >= 12 ? "PM" : "AM";


  // Convert to 12-hour format if necessary
  hours = hours % 12;
  hours = hours === 0 ? 12 : hours;

  hours = hours < 10 ? "0" + hours : hours;
  minutes = minutes < 10 ? "0" + minutes : minutes;
  seconds = seconds < 10 ? "0" + seconds : seconds;

  const formattedTime = `${hours}:${minutes}:${seconds} ${ampm}`;
  document.getElementById("clock").innerText = formattedTime;
}


setInterval(updateTime, 1000);

// Optionally, call updateTime once to display initial time on page load
// updateTime();