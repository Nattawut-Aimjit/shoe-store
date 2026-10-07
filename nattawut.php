<?php include_once __DIR__.'/header.php'; ?>

<div class="portfolio-wrap">

<style>
/* Portfolio styles (inline to avoid editing global CSS) */
.portfolio-wrap { max-width:1100px; margin:22px auto; padding:18px; }
.portfolio-hero { background:var(--card); padding:22px; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.04); }
.hero-inner { display:flex; gap:20px; align-items:center; flex-wrap:wrap; }
.profile-pic img { width:160px; height:160px; object-fit:cover; border-radius:12px; border:4px solid #f0f4ff; }
.hero-info { flex:1; min-width:260px; }
.hero-info h1 { margin:0 0 8px; font-size:28px; }
.lead { font-size:15px; margin:6px 0 12px; }
.hero-actions { margin-top:12px; display:flex; gap:10px; }
.portfolio-section { margin-top:18px; background:var(--card); padding:16px; border-radius:10px; box-shadow:0 6px 18px rgba(39,54,66,0.03); }
.skills { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
.skill { background:#f0f6ff; color:var(--primary); padding:6px 10px; border-radius:999px; font-weight:600; }
.projects-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:12px; margin-top:12px; }
.project-card { background: #fff; border-radius:10px; padding:10px; box-shadow:0 6px 18px rgba(33,47,60,0.04); }
.project-card img { width:100%; height:120px; object-fit:cover; border-radius:8px; }
.project-card h3 { margin:8px 0 6px; }
.project-card p { margin:0; color:var(--muted); }
@media (max-width:700px){ .hero-inner{flex-direction:column; align-items:flex-start} .profile-pic img{width:120px;height:120px} }
</style>

  <section class="portfolio-hero">
    <div class="hero-inner">
      <div class="profile-pic">
        <img src="image/me.png" alt="Nattawut">
      </div>
      <div class="hero-info">
        <h1>ณัฐวุฒิ <span class="muted">(Nattawut)</span></h1>
        <p class="lead muted">นักพัฒนาเว็บ / นักออกแบบ UI — สร้างเว็บที่ใช้งานง่าย มีประสิทธิภาพ และเป็นมิตรกับผู้ใช้</p>

        <p>สวัสดีครับ ผมเป็นนักพัฒนาเว็บที่ใช้ PHP, MySQL และ JavaScript ในการสร้างโปรเจกต์จริง เช่น เว็บร้านค้า และระบบจัดการหลังบ้าน สนใจเรียนรู้เทคโนโลยีใหม่ๆ และแก้ปัญหาให้ผู้ใช้งานได้จริง</p>

        <div class="hero-actions">
          <a class="btn" href="mailto:664244114@mail.pbru.ac.th">ติดต่อทางอีเมล</a>
          <a class="btn secondary" href="people.php">กลับไปยังหน้าเหล่าผู้สร้าง</a>
        </div>
      </div>
    </div>
  </section>

  <section class="portfolio-section about">
    <h2>ทักษะ</h2>
    <div class="skills">
      <span class="skill">PHP</span>
      <span class="skill">MySQL</span>
      <span class="skill">HTML</span>
      <span class="skill">CSS</span>
      <span class="skill">JavaScript</span>
      <span class="skill">Bootstrap</span>
    </div>
  </section>

  <section class="portfolio-section projects">
    <h2>ผลงาน / โปรเจกต์</h2>
    <div class="projects-grid">
      <article class="project-card">
        <img src="image/shoestore01.png" alt="โปรเจกต์ร้านค้า">
        <h3>เว็บไซต์ร้านค้า</h3>
        <p class="muted">ฟีเจอร์ค้นหา ตะกร้า และแผงจัดการหลังบ้าน</p>
      </article>

      <article class="project-card">
        <img src="image/dormhub02.png" alt="โปรเจกต์รหอพัก">
        <h3>เว็บไซต์หอพัก</h3>
        <p class="muted">อัพโหลด จัดการ และเพิ่มหอพัก</p>
      </article>

      <article class="project-card">
        <img src="image/admin03.png" alt="ระบบหลังบ้าน แอดมิน">
        <h3>ระบบหลังบ้าน แอดมิน</h3>
        <p class="muted">จัดการข้อมูลหอพัก อนุมัติและลบหอพัก</p>
      </article>
    </div>
  </section>

  <section class="portfolio-section contact">
    <h2>ติดต่อ</h2>
    <p class="muted">ต้องการร่วมงานหรือสอบถามเพิ่มเติม ติดต่อผ่านอีเมลด้านล่างได้เลย</p>
    <p><a class="btn" href="mailto:664244114@mail.pbru.ac.th">ส่งอีเมลหา ณัฐวุฒิ</a></p>
  </section>

</div>

<?php include_once __DIR__.'/footer.php'; ?>
