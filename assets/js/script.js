
// Initialize all tooltips
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize all popovers
function initPopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Toggle sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('mainContent').classList.toggle('active');
}

// Initialize charts
function initCharts() {
    // Example for Chart.js - you'll need to include Chart.js library
    if (typeof Chart !== 'undefined') {
        var ctx = document.getElementById('performanceChart');
        if (ctx) {
            var performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
                    datasets: [{
                        label: 'Your Performance',
                        data: [65, 59, 80, 81, 56, 72],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    }
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    initPopovers();
    initCharts();
    fixButtonClicks();
    fixStudentDashboardButtons();
    
    // Sidebar toggle button
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Confirm before delete actions
    document.querySelectorAll('.confirm-delete').forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });
});

// Add this function to handle button clicks properly
function fixButtonClicks() {
    // Handle "View All Courses" button in student dashboard
    const viewAllCoursesBtn = document.getElementById('viewAllCoursesBtn');
    if (viewAllCoursesBtn) {
        viewAllCoursesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'courses.php';
        });
    }

    // Handle "View All" buttons in My Courses section
    document.querySelectorAll('.view-all-courses').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'courses.php';
        });
    });
}
// Fix for student dashboard buttons
function fixStudentDashboardButtons() {
    // View All Courses button
    const viewAllCoursesBtn = document.getElementById('viewAllCoursesBtn');
    if (viewAllCoursesBtn) {
        viewAllCoursesBtn.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') return; // Already a link
            window.location.href = 'courses.php';
        });
    }

    // Any other "View All" buttons
    document.querySelectorAll('[data-view-all="courses"]').forEach(button => {
        button.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') return;
            window.location.href = 'courses.php';
        });
    });
}
