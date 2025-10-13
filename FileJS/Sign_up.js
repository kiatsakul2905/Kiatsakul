document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('signupForm');
  const errorMsg = document.getElementById('errorMsg');
  const profileImageInput = document.getElementById('profileImage');
  const imagePreview = document.getElementById('imagePreview');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const password = form.password.value;
    const confirmPassword = form.confirmPassword.value;
    const file = profileImageInput.files[0];

    if (password !== confirmPassword) {
      errorMsg.textContent = 'รหัสผ่านไม่ตรงกัน';
      return;
    }

    if (file) {
      const allowedTypes = ['image/jpeg', 'image/png'];
      if (!allowedTypes.includes(file.type)) {
        errorMsg.textContent = 'รองรับเฉพาะไฟล์ JPG หรือ PNG เท่านั้น';
        return;
      }
    }

    errorMsg.textContent = '';
    alert('สมัครสมาชิกสำเร็จ!');
    form.reset();
    imagePreview.style.display = 'none';
  });

  // ฟังชั่น preview รูป
  profileImageInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
      const allowedTypes = ['image/jpeg', 'image/png'];
      if (allowedTypes.includes(file.type)) {
        const reader = new FileReader();
        reader.onload = function (e) {
          imagePreview.src = e.target.result;
          imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        imagePreview.style.display = 'none';
        errorMsg.textContent = 'ไฟล์ต้องเป็น JPG หรือ PNG เท่านั้น';
      }
    } else {
      imagePreview.style.display = 'none';
    }
  });
});
