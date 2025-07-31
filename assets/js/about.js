document.addEventListener("DOMContentLoaded", function () {
  // Video Modal
  const videoBtn = document.getElementById("playVideoBtn");
  const videoModal = new bootstrap.Modal(document.getElementById("videoModal"));
  const videoFrame = document.getElementById("videoFrame");

  if (videoBtn) {
    videoBtn.addEventListener("click", function () {
      const videoId = this.getAttribute("data-video-id");
      videoFrame.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
      videoModal.show();
    });
  }

  videoModal._element.addEventListener("hidden.bs.modal", function () {
    videoFrame.src = "";
  });

  // Animate stats counters
  const statItems = document.querySelectorAll(".stat-item");
  if (statItems.length > 0) {
    statItems.forEach((item) => {
      const target = parseInt(item.getAttribute("data-count"));
      const statNumber = item.querySelector(".stat-number");
      let current = 0;
      const increment = target / 50;

      const updateStat = () => {
        current += increment;
        if (current < target) {
          if (statNumber.textContent.includes("M")) {
            statNumber.textContent = current.toFixed(1) + "M";
          } else if (statNumber.textContent.includes("%")) {
            statNumber.textContent = Math.floor(current) + "%";
          } else {
            statNumber.textContent = Math.floor(current) + "+";
          }
          requestAnimationFrame(updateStat);
        } else {
          if (statNumber.textContent.includes("M")) {
            statNumber.textContent = target.toFixed(1) + "M";
          } else if (statNumber.textContent.includes("%")) {
            statNumber.textContent = target + "%";
          } else {
            statNumber.textContent = target + "+";
          }
        }
      };

      const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
          updateStat();
          observer.unobserve(item);
        }
      });

      observer.observe(item);
    });
  }

  // Features tabs
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const tabId = button.getAttribute("data-tab");

      // Update active button
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      button.classList.add("active");

      // Update active content
      tabContents.forEach((content) => {
        content.classList.remove("active");
        if (content.classList.contains(tabId)) {
          content.classList.add("active");
        }
      });
    });
  });

  // Team slider initialization
  if (document.querySelector(".team-slider")) {
    new Splide(".team-slider", {
      type: "loop",
      perPage: 3,
      gap: "30px",
      pagination: false,
      breakpoints: {
        992: {
          perPage: 2,
        },
        768: {
          perPage: 1,
        },
      },
    }).mount();
  }

  // Impact chart
  const ctx = document.getElementById("impactChart");
  if (ctx) {
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: [
          "On-Time Performance",
          "Fuel Efficiency",
          "Passenger Satisfaction",
          "Operational Costs",
        ],
        datasets: [
          {
            label: "Improvement %",
            data: [25, 18, 32, 22],
            backgroundColor: [
              "rgba(0, 51, 102, 0.7)",
              "rgba(255, 153, 0, 0.7)",
              "rgba(0, 51, 102, 0.7)",
              "rgba(255, 153, 0, 0.7)",
            ],
            borderColor: [
              "rgba(0, 51, 102, 1)",
              "rgba(255, 153, 0, 1)",
              "rgba(0, 51, 102, 1)",
              "rgba(255, 153, 0, 1)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return value + "%";
              },
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return context.parsed.y + "% improvement";
              },
            },
          },
        },
      },
    });
  }

  // Form submission
  const demoForm = document.getElementById("demoRequest");
  if (demoForm) {
    demoForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Simulate form submission
      setTimeout(() => {
        demoForm.style.display = "none";
        document.querySelector(".success-message").style.display = "block";
      }, 1000);
    });
  }

  // Scroll animations
  const animateElements = document.querySelectorAll(".animate-on-scroll");

  const animateOnScroll = () => {
    animateElements.forEach((element) => {
      const elementTop = element.getBoundingClientRect().top;
      const windowHeight = window.innerHeight;

      if (elementTop < windowHeight - 100) {
        element.classList.add("visible");
      }
    });
  };

  window.addEventListener("scroll", animateOnScroll);
  animateOnScroll(); // Run once on load
});
