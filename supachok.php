<?php include_once __DIR__.'/header.php'; ?>

<style>
    /* Custom Theme Variables */
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --glass: rgba(255, 255, 255, 0.9);
    }

    .supachok-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Kanit', sans-serif;
    }

    /* Hero Section */
    .hero-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        display: flex;
        align-items: center;
        gap: 40px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    .hero-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 150px; height: 150px;
        background: var(--primary-gradient);
        opacity: 0.1;
        border-radius: 0 0 0 100%;
    }

    .profile-frame {
        flex-shrink: 0;
        width: 200px;
        height: 200px;
        border-radius: 20%;
        overflow: hidden;
        border: 5px solid white;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
    }

    .profile-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-text h1 {
        font-size: 2.5rem;
        margin: 0;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .student-id {
        display: inline-block;
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #64748b;
        margin: 10px 0;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }

    .content-box {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
    }

    .content-box h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        color: #1e293b;
    }

    /* Skill Tags */
    .skill-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .pill:hover {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
        transform: translateY(-3px);
    }

    /* Action Buttons */
    .action-bar {
        margin-top: 30px;
        display: flex;
        gap: 15px;
    }

    .btn-main {
        background: var(--primary-gradient);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: opacity 0.2s;
    }

    @media (max-width: 768px) {
        .hero-card { flex-direction: column; text-align: center; }
        .info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="supachok-container">
    <div class="hero-card">
        <div class="profile-frame">
            <img src="image/Supachok.jpg" alt="Supachok">
        </div>
        <div class="hero-text">
            <h1>ศุภโชค อุปมา</h1>
            <span class="student-id">รหัสประจำตัว: 664244133</span>
            <p style="color: #475569; font-size: 1.1rem; line-height: 1.6;">
                "มุ่งมั่นพัฒนาการเขียนโปรแกรมเพื่อสร้างสรรค์นวัตกรรมที่เปลี่ยนโลก <br>
                หลงใหลในด้าน Full-stack Development และ Data Management"
            </p>
            <div class="action-bar">
                <a href="mailto:664244133@mail.pbru.ac.th" class="btn-main">ติดต่อฉัน</a>
                <a href="people.php" class="btn" style="background:#f1f5f9; color:#475569;">กลับหน้าหลัก</a>
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="content-box">
            <h3>🚀 เกี่ยวกับฉัน</h3>
            <p style="color: #64748b; line-height: 1.8;">
                สวัสดีครับ ผมศุภโชค นักศึกษาสาขาวิทยาการคอมพิวเตอร์ ผมมีความสนใจในการออกแบบระบบฐานข้อมูล 
                และการพัฒนาเว็บแอปพลิเคชันด้วย PHP และ MySQL ผมเชื่อว่าโค้ดที่ดีต้องมาพร้อมกับประสบการณ์การใช้งานที่ยอดเยี่ยม
            </p>
            <h3 style="margin-top: 30px;">🛠 ทักษะและความสามารถ</h3>
            <div class="skill-pills">
                <div class="pill">PHP / MySQL</div>
                <div class="pill">HTML5 / CSS3</div>
                <div class="pill">JavaScript</div>
                <div class="pill">UI/UX Design</div>
                <div class="pill">Responsive Design</div>
            </div>
        </div>

        <div class="content-box">
            <h3>📍 ช่องทางการติดต่อ</h3>
            <ul style="list-style: none; padding: 0; color: #64748b;">
                <li style="margin-bottom: 15px;">📧 <b>Email:</b><br> 664244133@mail.pbru.ac.th</li>
                <li style="margin-bottom: 15px;">🏢 <b>Faculty:</b><br> คณะเทคโนโลยีสารสนเทศ</li>
                <li style="margin-bottom: 15px;">🎓 <b>University:</b><br> มหาวิทยาลัยราชภัฏเพชรบุรี</li>
            </ul>
        </div>
    </div>
</div>

<?php include_once __DIR__.'/footer.php'; ?>