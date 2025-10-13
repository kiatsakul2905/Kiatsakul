// Password toggle functionality
function togglePassword(inputId) {
  const input = document.getElementById(inputId)
  const button = input.nextElementSibling
  const icon = button.querySelector("i")

  if (input.type === "password") {
    input.type = "text"
    icon.classList.remove("fa-eye")
    icon.classList.add("fa-eye-slash")
  } else {
    input.type = "password"
    icon.classList.remove("fa-eye-slash")
    icon.classList.add("fa-eye")
  }
}

// Smooth scrolling for anchor links
document.addEventListener("DOMContentLoaded", () => {
  const links = document.querySelectorAll('a[href^="#"]')

  links.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()

      const targetId = this.getAttribute("href")
      const targetSection = document.querySelector(targetId)

      if (targetSection) {
        targetSection.scrollIntoView({
          behavior: "smooth",
        })
      }
    })
  })
})

// Mood analysis functionality
function analyzeMood() {
  const moodInput = document.getElementById("mood_input")
  const moodResult = document.getElementById("mood_result")
  const foodRecommendations = document.getElementById("food_recommendations")

  if (!moodInput || !moodInput.value.trim()) {
    alert("กรุณาใส่ข้อความเกี่ยวกับอารมณ์ของคุณ")
    return
  }

  // Show loading
  if (moodResult) {
    moodResult.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> กำลังวิเคราะห์อารมณ์...</p>'
    moodResult.style.display = "block"
  }

  // Send AJAX request to Python mood analyzer
  fetch("python/analyze_mood.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      text: moodInput.value,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayMoodResult(data.mood, data.confidence)
        loadFoodRecommendations(data.mood)
      } else {
        if (moodResult) {
          moodResult.innerHTML = '<p style="color: #e74c3c;">เกิดข้อผิดพลาดในการวิเคราะห์อารมณ์</p>'
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      if (moodResult) {
        moodResult.innerHTML = '<p style="color: #e74c3c;">เกิดข้อผิดพลาดในการเชื่อมต่อ</p>'
      }
    })
}

function displayMoodResult(mood, confidence) {
  const moodResult = document.getElementById("mood_result")
  if (moodResult) {
    const moodEmojis = {
      มีความสุข: "😊",
      เศร้า: "😢",
      โกรธ: "😠",
      เครียด: "😰",
      เฉยๆ: "😐",
      ตื่นเต้น: "🤩",
    }

    const emoji = moodEmojis[mood] || "🤔"
    const confidencePercent = Math.round(confidence * 100)

    moodResult.innerHTML = `
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size: 2rem;">${emoji}</span>
                <div>
                    <strong>อารมณ์ที่ตรวจพบ: ${mood}</strong>
                    <br>
                    <small>ความแม่นยำ: ${confidencePercent}%</small>
                </div>
            </div>
        `
  }
}

function selectMood(mood) {
  // Remove active class from all mood buttons
  document.querySelectorAll(".mood-btn").forEach((btn) => {
    btn.classList.remove("active")
  })

  // Add active class to selected button
  event.target.classList.add("active")

  // Load food recommendations
  loadFoodRecommendations(mood)
}

function loadFoodRecommendations(mood) {
  const foodRecommendations = document.getElementById("food_recommendations")

  if (foodRecommendations) {
    foodRecommendations.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> กำลังโหลดอาหารแนะนำ...</p>'

    fetch("api/get_food_recommendations.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        mood: mood,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayFoodRecommendations(data.foods)
        } else {
          foodRecommendations.innerHTML = "<p>ไม่พบอาหารแนะนำสำหรับอารมณ์นี้</p>"
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        foodRecommendations.innerHTML = "<p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>"
      })
  }
}

function displayFoodRecommendations(foods) {
  const foodRecommendations = document.getElementById("food_recommendations")

  if (foods.length === 0) {
    foodRecommendations.innerHTML = "<p>ไม่พบอาหารแนะนำสำหรับอารมณ์นี้</p>"
    return
  }

  let html = '<div class="food-grid">'

  foods.forEach((food) => {
    html += `
            <div class="food-card">
                <div class="food-image">
                    ${
                      food.food_image
                        ? `<img src="${food.food_image}" alt="${food.name}" style="width: 100%; height: 100%; object-fit: cover;">`
                        : `<i class="fas fa-utensils" style="font-size: 3rem; color: #ccc;"></i>`
                    }
                </div>
                <div class="food-content">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <h3 class="food-title">${food.name}</h3>
                        <span class="calories-badge">${food.calories} cal</span>
                    </div>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1rem;">${food.description || ""}</p>
                    <div class="food-herbs">
                        ${food.herbs.map((herb) => `<span class="herb-tag"><i class="fas fa-leaf"></i> ${herb}</span>`).join("")}
                    </div>
                    <a href="recipe.php?id=${food.id}" class="btn btn-primary" style="width: 100%; text-decoration: none;">
                        <i class="fas fa-utensils"></i> ดูสูตรอาหาร
                    </a>
                </div>
            </div>
        `
  })

  html += "</div>"
  foodRecommendations.innerHTML = html
}

// Form validation
function validateForm(formId) {
  const form = document.getElementById(formId)
  const inputs = form.querySelectorAll("input[required], textarea[required], select[required]")
  let isValid = true

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.style.borderColor = "#e74c3c"
      isValid = false
    } else {
      input.style.borderColor = "#e9ecef"
    }
  })

  return isValid
}

// Auto-hide alerts
document.addEventListener("DOMContentLoaded", () => {
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.style.display = "none"
      }, 300)
    }, 5000)
  })
})

// Image preview functionality
function previewImage(input, previewId) {
  const file = input.files[0]
  const preview = document.getElementById(previewId)

  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`
    }
    reader.readAsDataURL(file)
  }
}

// Nutrition chart functionality
function createNutritionChart(data) {
  const canvas = document.getElementById("nutritionChart")
  if (!canvas) return

  const ctx = canvas.getContext("2d")
  const centerX = canvas.width / 2
  const centerY = canvas.height / 2
  const radius = Math.min(centerX, centerY) - 20

  const total = data.protein + data.carbs + data.fat
  let currentAngle = 0

  const colors = ["#e74c3c", "#f39c12", "#3498db"]
  const labels = ["โปรตีน", "คาร์โบไฮเดรต", "ไขมัน"]
  const values = [data.protein, data.carbs, data.fat]

  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height)

  // Draw pie chart
  values.forEach((value, index) => {
    const sliceAngle = (value / total) * 2 * Math.PI

    ctx.beginPath()
    ctx.moveTo(centerX, centerY)
    ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle)
    ctx.closePath()
    ctx.fillStyle = colors[index]
    ctx.fill()

    currentAngle += sliceAngle
  })

  // Draw legend
  const legendY = canvas.height - 60
  values.forEach((value, index) => {
    const legendX = 20 + index * 120

    ctx.fillStyle = colors[index]
    ctx.fillRect(legendX, legendY, 15, 15)

    ctx.fillStyle = "#333"
    ctx.font = "12px Arial"
    ctx.fillText(`${labels[index]}: ${value}g`, legendX + 20, legendY + 12)
  })
}

// Calorie tracking functionality
function updateCalorieProgress(consumed, goal) {
  const progressBar = document.getElementById("calorieProgress")
  const progressText = document.getElementById("calorieProgressText")

  if (progressBar && progressText) {
    const percentage = Math.min((consumed / goal) * 100, 100)
    progressBar.style.width = percentage + "%"
    progressText.textContent = `${consumed} / ${goal} cal (${Math.round(percentage)}%)`

    // Change color based on progress
    if (percentage < 50) {
      progressBar.style.backgroundColor = "#27ae60"
    } else if (percentage < 80) {
      progressBar.style.backgroundColor = "#f39c12"
    } else {
      progressBar.style.backgroundColor = "#e74c3c"
    }
  }
}

// Recipe step tracking
function toggleRecipeStep(stepIndex) {
  const step = document.getElementById(`step-${stepIndex}`)
  const checkbox = document.getElementById(`checkbox-${stepIndex}`)

  if (step && checkbox) {
    if (checkbox.checked) {
      step.classList.add("completed")
    } else {
      step.classList.remove("completed")
    }

    updateRecipeProgress()
  }
}

function updateRecipeProgress() {
  const totalSteps = document.querySelectorAll(".recipe-step").length
  const completedSteps = document.querySelectorAll(".recipe-step.completed").length
  const progressBar = document.getElementById("recipeProgress")
  const progressText = document.getElementById("recipeProgressText")

  if (progressBar && progressText) {
    const percentage = (completedSteps / totalSteps) * 100
    progressBar.style.width = percentage + "%"
    progressText.textContent = `${completedSteps}/${totalSteps} ขั้นตอน`

    if (percentage === 100) {
      showCompletionMessage()
    }
  }
}

function showCompletionMessage() {
  const message = document.getElementById("completionMessage")
  if (message) {
    message.style.display = "block"
    message.scrollIntoView({ behavior: "smooth" })
  }
}
