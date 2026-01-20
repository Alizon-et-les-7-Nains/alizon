let lastScroll = 0;
const header = document.querySelector(".headerFront");

window.addEventListener("scroll", () => {
  const currentScroll = window.scrollY;

  if (currentScroll > lastScroll && currentScroll > 80) {
    // scroll vers le bas
    header?.classList.add("hidden");
  } else {
    // scroll vers le haut
    header?.classList.remove("hidden");
  }

  lastScroll = currentScroll;
});
