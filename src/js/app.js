// js/app.js

const API_BASE = '/api'; // ใช้ Relative Path ตามที่คุยกัน
const token = localStorage.getItem('jwt_token');

// 1. ตรวจสอบสิทธิ์ทันทีที่โหลดไฟล์
if (!token) {
    window.location.href = 'index.html';
}

// 2. ฟังก์ชันสร้าง Sidebar และ Navbar
function initLayout(activeMenuId) {
    const sidebarHTML = `
        <div class="sidebar-heading border-bottom">NexID System</div>
        <div class="list-group list-group-flush mt-3">
            <a href="dashboard.html" class="list-group-item list-group-item-action ${activeMenuId === 'menu-dashboard' ? 'active' : ''}">
                <i class="bi bi-speedometer2 me-2"></i> ภาพรวมระบบ
            </a>
            <a href="profile.html" class="list-group-item list-group-item-action ${activeMenuId === 'menu-profile' ? 'active' : ''}">
                <i class="bi bi-person-gear me-2"></i> แก้ไขข้อมูลส่วนตัว
            </a>
            <a href="#" class="list-group-item list-group-item-action ${activeMenuId === 'menu-history' ? 'active' : ''}">
                <i class="bi bi-credit-card me-2"></i> ประวัติธุรกรรม
            </a>
            <a href="#" class="list-group-item list-group-item-action text-danger mt-5" onclick="logout()">
                <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
            </a>
        </div>
    `;

    const navbarHTML = `
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-light" id="menu-toggle"><i class="bi bi-list"></i></button>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted" id="globalUsername">Loading...</span>
                    <img src="https://ui-avatars.com/api/?name=User&background=random" id="globalAvatar" class="rounded-circle" width="40" height="40">
                </div>
            </div>
        </nav>
    `;

    // Inject HTML เข้าไปใน ID ที่เตรียมไว้
    document.getElementById('sidebar-wrapper').innerHTML = sidebarHTML;
    document.getElementById('navbar-placeholder').innerHTML = navbarHTML;

    // ผูก Event Toggle Sidebar
    document.getElementById("menu-toggle").onclick = function() {
        document.getElementById("wrapper").classList.toggle("toggled");
    };
    
    // โหลดข้อมูล User มาแสดงที่ Navbar
    loadGlobalUserData();
}

// 3. ฟังก์ชัน Logout
function logout() {
    Swal.fire({
        title: 'ออกจากระบบ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ยืนยัน'
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.removeItem('jwt_token');
            window.location.href = 'index.html';
        }
    });
}

// 4. ดึงข้อมูล User (ใช้ร่วมกันทุกหน้า)
async function loadGlobalUserData() {
    try {
        const res = await axios.get(`${API_BASE}/get_profile.php`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        const d = res.data.data;
        const displayName = d.first_name || d.username;
        
        // อัปเดต Navbar ทุกหน้า
        if(document.getElementById('globalUsername')) {
            document.getElementById('globalUsername').innerText = d.username;
            document.getElementById('globalAvatar').src = `https://ui-avatars.com/api/?name=${displayName}&background=764ba2&color=fff`;
        }
    } catch (err) {
        if (err.response && err.response.status === 401) {
            localStorage.removeItem('jwt_token');
            window.location.href = 'index.html';
        }
    }
}