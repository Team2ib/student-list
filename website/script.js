function close() {
  document.getElementById("api").remove();
  document.getElementById("phpbutton").addEventListener("click", open);
  document.getElementById("phpbutton").removeEventListener("click", close);
  document.getElementById("phpbutton").setAttribute("name", "open");
}

function open() {
  document.getElementById("api").remove();
  document.getElementById("phpbutton").addEventListener("click", close);
  document.getElementById("phpbutton").removeEventListener("click", open);
  document.getElementById("phpbutton").setAttribute("name", "close");
}

document.getElementById("phpbutton").addEventListener("click", open);
