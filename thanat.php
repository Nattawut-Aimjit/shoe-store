<?php include_once __DIR__.'/header.php'; ?>

  <style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }
  
  header {
  position: fixed;       
  top: 0;
  left: 0;
  width: 100% !important;
  z-index: 999;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background-color: #081b38 !important;
    color: #333;
    display: block !important;        
    padding: 40px 20px;
  }

  main {
    max-width: unset !important;      
    margin: 0 !important;
    padding: 0 !important;
    display: flex;
    justify-content: center;
  }
  
  .container {
    background: #ffffff;
    max-width: 1100px;
    width: 100%;
    padding: 50px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    margin-top: 60px;
  }

  .header {
    display: flex;
    align-items: center;
    gap: 30px;
  }

  .avatar img {
    width: 250px;
    height: 250px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #081b38;
  }

  .greeting {
    font-size: 19px;
    color: #888;
    margin-bottom: 5px;
  }

  .name {
    font-family: 'Syne', sans-serif;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
  }

  .tagline {
    font-size: 15px;
    color: #555;
    line-height: 1.6;
  }

  .divider {
    height: 3px;
    background: #081b38;
    margin: 35px 0;
  }
  .section {
    margin-bottom: 35px;
  }
  .section-label {
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 18px;
  }
  .about-text {
    line-height: 1.7;
    color: #555;
  }
  .tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .tag {
    background: #e0e0e0;
    color: #0f356d;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
  }
  .tag:hover {
    border: 2px solid #081b38;
    background: #061224;
    color: #ffffff;
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
  }
  .yt-card {
    display: flex;
    align-items: center;
    gap: 15px;
    text-decoration: none;
    background: #e0e0e0;
    padding: 15px 20px;
    border-radius: 25px;
    border: 1px solid #eee;
    color: #333;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
  }
  .yt-card:hover {
    border: 2px solid #081b38;
    background: #061224;
    color: #ffffff;
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
  }
  .yt-icon svg {
    width: 28px;
    fill: #ff0000;
  }
  .yt-name {
    font-weight: 600;
  }
  .yt-desc {
    font-size: 13px;
    color: #777;
  }
  .footer {
    margin-top: 40px;
    text-align: center;
    font-size: 15px;
    color: #081b38;
  }
  .btn-wrapper {
    display: flex;
    justify-content: flex-end; 
  }
  .btn {
    background: #081b38;
    color: #fff;
    padding: 10px 12px;
    border-radius: 14px;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    display: inline-block;
    margin-left: auto;   
    text-decoration: none; 
  }
  .btn:hover {
    background: #061224;
  }

  /* ===== Responsive ===== */
  @media (max-width: 600px) {
    body {
      padding: 20px 10px;
    }
    .header {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    .avatar img {
      width: 150px;
      height: 150px;
    }
    .container {
      padding: 20px;
    }
  }
</style>
 
  <div class="container">
        <div class="btn-wrapper">
            <a class="btn" href="people.php">กลับไปยังหน้าเหล่าผู้สร้าง</a>
        </div>
    <!-- Header -->
    <div class="header">
      <div class="avatar-wrap">
        <div class="avatar-glow"></div>
        <div class="avatar" >
            <img src="image/th03.jpg" alt="th03">
        </div>
        <div class="status-dot"></div>
      </div>
      <div class="header-info">
        <div class="greeting">Hello Everyone.</div>
        <div class="name">นายธนัตถ์ รุ่งคำ</div>
        <p class="tagline">
          <span>นักศึกษาคณะเทคโนโลยีสารสนเทศ สาขาวิทยาการคอมพิวเตอร์</span> ที่ชอบเขียนโค้ดและสร้างสิ่งใหม่ๆ<br>
          หลงใหลใน Web Dev และ Content Creation
        </p>
      </div>
    </div>

    <div class="divider"></div>

    <!-- Section -->
    <div class="section">
      <div class="section-label">ข้อมูลการติดต่อ.</div>
      <div class="tags">
        <span class="tag">✉️ Email: 664244116@mai.pbru.ac.th</span>
        <span class="tag">📱 Phone: 0620806987</span>
        <span class="tag">📌 Adress: 19/6 M2. Pranburi Prachuap Khiri Khan</span>
      </div>
    </div>
    
    <div class="section">
      <div class="section-label">เกี่ยวกับฉัน.</div>
      <p class="about-text">
        สวัสดีครับ! ผมเป็น<strong>นักศึกษาสาขาเทคโนโลยีสารสนเทศ</strong> ที่ชื่นชอบในการออกเเบบหน้าเว็บเเละตกเเต่งหน้าเว็บโยใช้ HTML เเละ CSS เป็นหลัก 
        <br>เน้นใช้ความคิดสร้างสรรค์ในการออกเบบ เเละฝึกฝนการใช้ <strong>Figma - เพื่อออกเเบบหน้าเว็บไซต์</strong> 
        ในเวลาว่างชอบการทำ <strong>คอนเทนต์ YouTube - Gaming</strong> <br> เป็นงานอดิเรก เผื่อสามารถนำไปต่อยอดในด้านอื่นๆได้
      </p>
    </div>

    
    <div class="section">
      <div class="section-label">SKILL.</div>
      <div class="tags">
        <span class="tag">HTML / CSS</span>
        <span class="tag">PHP</span>
        <span class="tag">MySQL</span>
        <span class="tag">Figma UX/UI</span>
        <span class="tag">Video Editing</span>
        <span class="tag">Content Creator</span>
      </div>
    </div>

    <!-- YouTube -->
    <div class="section">
      <div class="section-label">ผลงาน.</div>
      <a href="http://www.youtube.com/@WalDAzZen" target="_blank" class="yt-card">
        <div class="yt-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
          </svg>
        </div>
        <div class="yt-info">
          <div class="yt-name">YouTube - WalDAzZen</div>
          <div class="yt-desc">คลิกเพื่อเข้าชมช่อง → www.youtube.com/@WalDAzZen</div>
        </div>
      </a>
    </div>

    <div class="divider"></div>
    <div class="footer">
      Made with Thanat Rungkham 664244116
    </div>

  </div>

<?php include_once __DIR__.'/footer.php'; ?>