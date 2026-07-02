const menuBtn = document.getElementById("menuBtn");
const navbar = document.getElementById("navbar");

if (menuBtn && navbar) {
  menuBtn.addEventListener("click", () => {
    navbar.classList.toggle("active");
  });
}

const faqQuestions = document.querySelectorAll(".faq-question");
faqQuestions.forEach((question) => {
  question.addEventListener("click", () => {
    const currentItem = question.parentElement;
    document.querySelectorAll(".faq-item").forEach((item) => {
      if (item !== currentItem) item.classList.remove("active");
    });
    currentItem.classList.toggle("active");
  });
});

const revealElements = document.querySelectorAll(".reveal");
const revealOnScroll = () => {
  const windowHeight = window.innerHeight;
  revealElements.forEach((el) => {
    const elementTop = el.getBoundingClientRect().top;
    if (elementTop < windowHeight - 90) {
      el.classList.add("visible");
    }
  });
};

window.addEventListener("scroll", revealOnScroll);
window.addEventListener("load", revealOnScroll);


// Fraud alert popup - shows once per browser session
const fraudAlert = document.getElementById("fraudAlert");
const closeModalButtons = document.querySelectorAll("[data-close-modal]");

const openFraudAlert = () => {
  if (!fraudAlert) return;
  if (sessionStorage.getItem("srsbFraudAlertClosed") === "true") return;
  fraudAlert.classList.add("show");
  fraudAlert.setAttribute("aria-hidden", "false");
};

const closeFraudAlert = () => {
  if (!fraudAlert) return;
  fraudAlert.classList.remove("show");
  fraudAlert.setAttribute("aria-hidden", "true");
  sessionStorage.setItem("srsbFraudAlertClosed", "true");
};

window.addEventListener("load", () => {
  setTimeout(openFraudAlert, 700);
});

closeModalButtons.forEach((button) => {
  button.addEventListener("click", closeFraudAlert);
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") closeFraudAlert();
});

// Our Clients carousel controls and gentle auto-scroll
const clientCarousel = document.getElementById("clientCarousel");
const clientPrev = document.getElementById("clientPrev");
const clientNext = document.getElementById("clientNext");

if (clientCarousel && clientPrev && clientNext) {
  const scrollClients = (direction = 1) => {
    const distance = Math.min(280, clientCarousel.clientWidth * 0.75);
    clientCarousel.scrollBy({ left: direction * distance, behavior: "smooth" });
  };

  clientNext.addEventListener("click", () => scrollClients(1));
  clientPrev.addEventListener("click", () => scrollClients(-1));

  setInterval(() => {
    const isAtEnd = clientCarousel.scrollLeft + clientCarousel.clientWidth >= clientCarousel.scrollWidth - 8;
    if (isAtEnd) {
      clientCarousel.scrollTo({ left: 0, behavior: "smooth" });
    } else {
      scrollClients(1);
    }
  }, 3200);
}
