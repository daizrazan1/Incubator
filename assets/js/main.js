document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = document.getElementById('searchInput').value;
            window.location.href = `/parts.php?query=${encodeURIComponent(query)}`;
        });
    }

    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const inputs = filterForm.querySelectorAll('select, input');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }

    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.body.classList.add('light-mode');
            themeToggle.textContent = '🌞';
        }

        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('light-mode');
            if (document.body.classList.contains('light-mode')) {
                themeToggle.textContent = '🌞';
                localStorage.setItem('theme', 'light');
            } else {
                themeToggle.textContent = '🌙';
                localStorage.setItem('theme', 'dark');
            }
        });
    }
});

function addToBuild(partId) {
    const urlParams = new URLSearchParams(window.location.search);
    const buildId = urlParams.get('build_id') || localStorage.getItem('currentBuildId') || 'new';
    
    fetch('/api/add_to_build.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            build_id: buildId,
            part_id: partId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.build_id) {
                localStorage.setItem('currentBuildId', data.build_id);
                window.location.href = '/build.php?build_id=' + data.build_id;
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add part to build');
    });
}

function removePart(buildPartId) {
    if (!confirm('Are you sure you want to remove this part?')) {
        return;
    }
    
    fetch('/api/remove_from_build.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            build_part_id: buildPartId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove part');
    });
}

function trackClick(partId, merchantId) {
    fetch('/api/track_click.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            part_id: partId,
            merchant_id: merchantId
        })
    });
}
