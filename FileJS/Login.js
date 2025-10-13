document.addEventListener('DOMContentLoaded', function () {
  const signupForm = document.getElementById('signupForm');
  const loginForm = document.getElementById('loginForm');

  // สมัครสมาชิก
  if (signupForm) {
    const errorMsg = document.getElementById('errorMsg');
    const profileImageInput = document.getElementById('profileImage');
    const imagePreview = document.getElementById('imagePreview');

    signupForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const user = {
        firstName: signupForm.firstName.value,
        lastName: signupForm.lastName.value,
        email: signupForm.email.value,
        phone: signupForm.phone.value,
        password: signupForm.password.value,
      };

      if (signupForm.password.value !== signupForm.confirmPassword.value) {
        errorMsg.textContent = 'รหัสผ่านไม่ตรงกัน';
        return;
      }

      // ตรวจสอบไฟล์
      const file = profileImageInput.files[0];
      if (file) {
        const allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
          errorMsg.textContent = 'รองรับเฉพาะไฟล์ JPG หรือ PNG เท่านั้น';
          return;
        }
      }

      errorMsg.textContent = '';
      // บันทึกผู้ใช้ (เฉพาะฝั่ง client)
      localStorage.setItem('user', JSON.stringify(user));
      alert('สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ');
      window.location.href = 'login.html';
    });

    // แสดง preview รูป
    if (profileImageInput) {
      profileImageInput.addEventListener('change', function () {
        const file = this.files[0];
        const allowedTypes = ['image/jpeg', 'image/png'];
        if (file && allowedTypes.includes(file.type)) {
          const reader = new FileReader();
          reader.onload = function (e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else {
          imagePreview.style.display = 'none';
        }
      });
    }
  }

  // เข้าสู่ระบบ
  if (loginForm) {
    const loginError = document.getElementById('loginError');

    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const inputEmail = loginForm.email.value;
      const inputPassword = loginForm.password.value;

      const storedUser = JSON.parse(localStorage.getItem('user'));

      if (!storedUser || storedUser.email !== inputEmail || storedUser.password !== inputPassword) {
        loginError.textContent = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        return;
      }

      loginError.textContent = '';
      // บันทึกสถานะล็อกอิน
      localStorage.setItem('loggedInUser', JSON.stringify(storedUser));
      window.location.href = 'index.html';
    });
  }
});
