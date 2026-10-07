<?php include_once __DIR__.'/header.php'; ?>

<style>
    .resume-wrapper {
        max-width: 1000px;
        margin: 60px auto 40px;
        position: relative;
    }
    .resume-container {
        background: #ffffff;
        box-shadow: 0 15px 35px rgba(0,0,0,0.08); /* เงาฟุ้งๆ ดูมีมิติ */
        border-radius: 16px; /* ขอบโค้งมน */
        display: flex;
        flex-wrap: wrap;
        font-family: 'Segoe UI', Arial, sans-serif;
        overflow: hidden; /* ให้ขอบโค้งตัดกับพื้นหลังซ้ายขวาพอดี */
    }
    .resume-left {
        flex: 1;
        min-width: 300px;
        background: #2c3e50; /* ปรับสีเทาให้ติดน้ำเงินนิดๆ ดูแพงขึ้น (Dark Navy) */
        color: #ffffff;
        padding: 50px 30px;
    }
    .resume-right {
        flex: 2;
        min-width: 400px;
        padding: 50px;
        color: #333333;
    }
    
    /* 1. ปรับรูปโปรไฟล์เป็นวงกลม */
    .profile-img-placeholder {
        width: 180px;
        height: 180px;
        background: #e0e6ed;
        border-radius: 50%; /* ทำให้เป็นวงกลม */
        margin: 0 auto 30px;
        overflow: hidden;
        border: 4px solid #ffffff; /* กรอบขาวล้วน */
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .profile-img-placeholder img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .resume-left h3 {
        color: #64b5f6; /* สีฟ้าอ่อนให้ตัดกับพื้นหลังเข้ม */
        border-bottom: 1px solid rgba(255,255,255,0.2);
        padding-bottom: 8px;
        margin-top: 30px;
        margin-bottom: 20px;
        text-transform: uppercase;
        font-size: 1.1em;
        letter-spacing: 2px;
    }
    .resume-left p {
        font-size: 0.95em;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    /* 2. เปลี่ยน Skills เป็นแบบป้าย Badge */
    .skill-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    .skill-badges span {
        background: rgba(255,255,255,0.1);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        border: 1px solid rgba(255,255,255,0.2);
        white-space: nowrap; /* 🌟 เพิ่มบรรทัดนี้: บังคับไม่ให้ข้อความในป้ายแตกเป็น 2 บรรทัด */
        display: inline-block; /* 🌟 เพิ่มบรรทัดนี้: ให้ป้ายคงรูปทรงกรอบแคปซูลไว้เสมอ */
    }
    
    .resume-right h1 {
        font-size: 2.8em;
        margin: 0 0 5px;
        color: #111;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .resume-title {
        color: var(--primary); /* ใช้สีน้ำเงินของเว็บ */
        font-size: 1.4em;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 40px;
        letter-spacing: 2px;
    }
    
    /* 3. ปรับสไตล์หัวข้อฝั่งขวาให้คลีนขึ้น */
    .resume-right h3 {
        color: #2c3e50;
        text-transform: uppercase;
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 1.2em;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .resume-right h3::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e0e0e0;
    }
    
    /* 4. ปรับกล่องเนื้อหา (เอาขอบดำทึบออก ใช้เป็นกล่องสีเทาอ่อนและแถบสีด้านซ้ายแทน) */
    .content-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid var(--primary);
        margin-bottom: 30px;
        line-height: 1.7;
    }
    .resume-right ul { padding-left: 20px; margin-top: 10px; }
    .resume-right li { margin-bottom: 8px; }
    
    /* ปุ่มสลับภาษา */
    .lang-switch-container {
        position: absolute;
        top: -45px;
        right: 0;
    }
    .btn-lang {
        background: #1f6feb;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: background 0.2s, transform 0.1s;
    }
    .btn-lang:hover { background: #1557c0; }
    .btn-lang:active { transform: scale(0.95); }

    .lang-th { display: none; }
    .show-th .lang-en { display: none; }
    .show-th span.lang-th, .show-th strong.lang-th { display: inline; }
    .show-th div.lang-th, .show-th p.lang-th, .show-th ul.lang-th, .show-th h1.lang-th, .show-th h3.lang-th, .show-th li.lang-th { display: block; }

    @media (max-width: 768px) {
        .resume-right { padding: 30px 20px; }
        .resume-left { padding: 40px 20px; }
        .resume-right h1 { font-size: 2em; }
        .lang-switch-container { position: static; text-align: right; margin-bottom: 15px; }
    }
</style>

<div class="resume-wrapper">
    
    <div class="lang-switch-container">
        <button class="btn-lang" onclick="toggleLanguage()" id="langBtn">🌐 เปลี่ยนเป็นภาษาไทย (TH)</button>
    </div>

    <div class="resume-container">
        
        <div class="resume-left">
            <div class="profile-img-placeholder">
                <img src="image/test.jpg" alt="Bunyawi Profile">
            </div>

            <h3>
                <span class="lang-en">Contact</span>
                <span class="lang-th">ติดต่อ</span>
            </h3>
            <p>
                📞 <strong class="lang-en">Phone:</strong><strong class="lang-th">เบอร์โทรศัพท์:</strong><br>
                094-398-2643
            </p>
            <p>
                ✉️ <strong class="lang-en">Email:</strong><strong class="lang-th">อีเมล:</strong><br>
                664244118@mail.pbru.ac.th
            </p>
            <p>
                📍 <strong class="lang-en">Address:</strong><strong class="lang-th">ที่อยู่:</strong><br>
                <span class="lang-en">Huahin, Prachuap Khiri Khan</span>
                <span class="lang-th">หัวหิน, ประจวบคีรีขันธ์</span>
            </p>

            <h3>
                <span class="lang-en">Education</span>
                <span class="lang-th">การศึกษา</span>
            </h3>
            <p class="lang-en">
                🎓 <strong>B.Sc. in Computer Science</strong><br>
                Faculty of Information Technology,<br>
                Phetchaburi Rajabhat University<br>
                <span style="color: #64b5f6;">(Graduation: 2026)</span>
            </p>
            <p class="lang-th">
                🎓 <strong>วิทยาการคอมพิวเตอร์</strong><br>
                คณะเทคโนโลยีสารสนเทศ,<br>
                มหาวิทยาลัยราชภัฏเพชรบุรี<br>
                <span style="color: #64b5f6;">(ปีที่คาดว่าจะจบการศึกษา: 2569)</span>
            </p>

            <h3>
                <span class="lang-en">Skills</span>
                <span class="lang-th">ทักษะความถนัด</span>
            </h3>
            
            <p style="margin-bottom: 8px;"><strong class="lang-en">Tech Stack</strong><strong class="lang-th">ทักษะทางเทคนิค</strong></p>
            <div class="skill-badges">
                <span>HTML / CSS</span>
                <span>PHP</span>
                <span>Android Studio</span>
                <span>MySQL</span>
            </div>

            <p style="margin-top: 15px; margin-bottom: 8px;"><strong class="lang-en">Soft Skills</strong><strong class="lang-th">ทักษะด้านบุคคล</strong></p>
            <div class="skill-badges lang-en">
                <span>Positive mindset</span>
                <span>Discipline</span>
                <span>Stress management</span>
            </div>
            <div class="skill-badges lang-th">
                <span>ทัศนคติเชิงบวก</span>
                <span>มีระเบียบวินัย</span>
                <span>รับมือความกดดันได้ดี</span>
            </div>
        </div>

        <div class="resume-right">
            <h1>
                <span class="lang-en">BunyaWi Thananan</span>
                <span class="lang-th">บุญยวีร์ ธนานันท์</span>
            </h1>
            <div class="resume-title">
                <span class="lang-en">Web Developer</span>
                <span class="lang-th">นักพัฒนาเว็บไซต์</span>
            </div>

            <div>
                <h3>
                    <span class="lang-en">About Me</span>
                    <span class="lang-th">เกี่ยวกับฉัน</span>
                </h3>
                <div class="content-box">
                    <span class="lang-en">I am an aspiring Web Developer with strong fundamentals in HTML, CSS, and PHP. I enjoy building clean, user-friendly interfaces and solving problems with practical, efficient code. With a positive mindset, strong discipline, and the ability to stay focused under pressure, I am committed to improving my skills and contributing to real-world web projects. I am ready to learn, adapt quickly, and add value to any development team.</span>
                    <span class="lang-th">ผมคือนักพัฒนาเว็บไซต์ที่มุ่งมั่นและมีพื้นฐานที่แข็งแกร่งในด้าน HTML, CSS และ PHP ผมชื่นชอบการสร้างหน้าเว็บที่สะอาดตา ใช้งานง่าย และแก้ปัญหาด้วยโค้ดที่มีประสิทธิภาพและนำไปใช้ได้จริง ด้วยทัศนคติเชิงบวก ความมีระเบียบวินัย และความสามารถในการจดจ่อแม้อยู่ภายใต้ความกดดัน ผมพร้อมที่จะพัฒนาทักษะของตนเองและทุ่มเทให้กับโปรเจกต์จริง ผมพร้อมที่จะเรียนรู้ ปรับตัวให้ไว และสร้างคุณค่าให้กับทีมพัฒนาครับ</span>
                </div>
            </div>

            <div>
                <h3>
                    <span class="lang-en">Experience</span>
                    <span class="lang-th">ประสบการณ์ / ผลงาน</span>
                </h3>
                <div class="content-box">
                    <strong class="lang-en">Android Studio Group Project</strong>
                    <strong class="lang-th">โปรเจกต์กลุ่ม (Android Studio)</strong>
                    <br><span style="color: var(--muted); font-size: 0.9em;">Student Schedule Management App</span>
                    
                    <ul class="lang-en">
                        <li>Designed core features for a student schedule management app.</li>
                        <li>Collaborated with a 3-member team; delivered all tasks 100% on time.</li>
                        <li>Built scheduling and reminder functionalities.</li>
                        <li>Tested with 10+ users and iteratively improved based on feedback.</li>
                    </ul>
                    <ul class="lang-th">
                        <li>ออกแบบฟีเจอร์หลักสำหรับแอปพลิเคชันจัดการตารางเรียนของนักศึกษา</li>
                        <li>ทำงานร่วมกับทีม 3 คน และส่งมอบงานทั้งหมดได้ตรงเวลา 100%</li>
                        <li>สร้างระบบจัดตารางเวลาและระบบการแจ้งเตือน</li>
                        <li>ทดสอบการใช้งานกับผู้ใช้จริงกว่า 10 คน และนำข้อเสนอแนะมาปรับปรุงแอปพลิเคชันอย่างต่อเนื่อง</li>
                    </ul>
                </div>
            </div>

            <div>
                <h3>
                    <span class="lang-en">Availability</span>
                    <span class="lang-th">ความพร้อมในการทำงาน</span>
                </h3>
                <div class="content-box" style="padding: 15px 20px;">
                    <span class="lang-en">🚀 Available to start immediately.</span>
                    <span class="lang-th">🚀 พร้อมเริ่มงานได้ทันที</span>
                </div>
            </div>
            
            <div style="margin-top: 40px; text-align: right;">
                <a class="btn secondary" href="people.php">⬅ กลับไปยังหน้าเหล่าผู้สร้าง</a>
            </div>
        </div>
    </div>
</div>

<script>
    let isThai = false; 
    function toggleLanguage() {
        isThai = !isThai; 
        const btn = document.getElementById('langBtn');
        const body = document.body;

        if(isThai) {
            body.classList.add('show-th'); 
            btn.innerText = '🌐 Switch to English (EN)';
            btn.style.background = '#e74c3c'; 
        } else {
            body.classList.remove('show-th'); 
            btn.innerText = '🌐 เปลี่ยนเป็นภาษาไทย (TH)';
            btn.style.background = '#1f6feb'; 
        }
    }
</script>

<?php include_once __DIR__.'/footer.php'; ?>