/* ===============================

   EAZOLA Doctor Website JS

   Demo Content + Booking Logic

================================ */

document.addEventListener("DOMContentLoaded", function () {

  /* Inject Demo HTML */

  const container = document.createElement("div");

  container.className = "doctor-container";

  container.innerHTML = `

    <div class="doctor-hero">

      <h1>د. أحمد محمد</h1>

      <p>استشاري أمراض الباطنة والجهاز الهضمي</p>

    </div>

    <div class="section">

      <div class="doctor-card">

        <img src="https://images.unsplash.com/photo-1550831107-1553da8c8464" />

        <div class="doctor-info">

          <h3>عن الطبيب</h3>

          <p>

            خبرة أكثر من 15 سنة في تشخيص وعلاج أمراض الباطنة،

            مع اهتمام خاص بالمتابعة الدقيقة وحالة المريض.

          </p>

        </div>

      </div>

    </div>

    <div class="section">

      <h2>الخدمات الطبية</h2>

      <div class="services">

        <div class="service-box">

          <h3>كشف باطنة</h3>

          <p>تشخيص شامل للحالة الصحية</p>

        </div>

        <div class="service-box">

          <h3>متابعة مزمنة</h3>

          <p>ضغط - سكر - كوليسترول</p>

        </div>

        <div class="service-box">

          <h3>استشارة أونلاين</h3>

          <p>مكالمات فيديو وحالات طارئة</p>

        </div>

      </div>

    </div>

    <div class="section">

      <h2>حجز موعد</h2>

      <form class="booking-form" id="bookingForm">

        <input type="text" placeholder="الاسم بالكامل" required />

        <input type="tel" placeholder="رقم الهاتف" required />

        <input type="date" required />

        <input type="time" required />

        <button type="submit">تأكيد الحجز</button>

      </form>

      <p id="bookingMsg" style="margin-top:10px;color:green;display:none;">

        تم إرسال طلب الحجز بنجاح

      </p>

    </div>

    <div class="doctor-footer">

      © 2026 جميع الحقوق محفوظة

    </div>

  `;

  document.body.appendChild(container);

  /* Booking Form Logic */

  document.getElementById("bookingForm").addEventListener("submit", function (e) {

    e.preventDefault();

    document.getElementById("bookingMsg").style.display = "block";

    this.reset();

  });

});