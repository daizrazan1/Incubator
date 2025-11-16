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
});

function addToBuild(partId) {
    const buildId = localStorage.getItem('currentBuildId') || 'new';
    
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
            alert('Part added to build!');
            if (data.build_id) {
                localStorage.setItem('currentBuildId', data.build_id);
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
