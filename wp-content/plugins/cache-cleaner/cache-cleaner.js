/*jQuery(function ($) {
    $('#wp-admin-bar-bm-cache-cleaner a').on('click', function (e) {
        e.preventDefault();
        $('#bm-cache-cleaner-panel').toggle();
    });

    $('#bm-cache-cleaner-btn').on('click', function () {
        const key = $(this).data('key') || $('#bm-cache-cleaner-key').val();
        const msg = $('#bm-cache-cleaner-msg').text('Cleaning...');
        $.post(BMCacheCleaner.ajax_url, {
            action: 'bm_cache_cleaner_clear',
            nonce: BMCacheCleaner.nonce,
            key: key
        }, function (res) {
            if (res.success) {
                msg.text('✅ ' + res.data);
            } else {
                msg.text('❌ ' + res.data);
            }
        });
    });
});*/

document.addEventListener('DOMContentLoaded', function () {
    const buttonWrapper = document.getElementById('wp-admin-bar-bm-cache-cleaner');
    const buttonPopupToggle = buttonWrapper?.querySelector('a');
    const popup = document.getElementById('bm-cache-cleaner-panel');

    buttonPopupToggle.addEventListener('click', (e) => {
        e.preventDefault();

        if ( popup.classList.contains('active') ) {
            popup.classList.remove('active');
            popup.style.display = 'none';
        } else {
            popup.classList.add('active');
            popup.style.display = 'block';
        }
    })

    const button = document.getElementById('bm-cache-cleaner-btn');
    if (!button) return;

    const messageBox = document.getElementById('bm-cache-cleaner-msg');
    const select = document.getElementById('bm-cache-cleaner-key');

    button.addEventListener('click', () => {
        const key = button.dataset.key || document.getElementById('bm-cache-cleaner-key')?.value;
        const selectedIndex = select.selectedIndex;
        if (!key) return;

        messageBox.textContent = '🧹 Cleaning...';
        select.disabled = true;
        button.disabled = true;

        const formData = new URLSearchParams();
        formData.append('action', 'bm_cache_cleaner_clear');
        formData.append('nonce', BMCacheCleaner.nonce);
        formData.append('key', key);

        fetch(BMCacheCleaner.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString(),
        })
            .then((res) => res.json())
            .then((data) => {
                messageBox.textContent = data.success
                    ? `✅ ${data.data}`
                    : `❌ ${data.data}`;

                if (selectedIndex !== -1) {
                    select.options[selectedIndex].remove();
                }
            })
            .catch(() => {
                messageBox.textContent = '❌ Request error';
            })
            .finally(() => {
                button.disabled = false;
                select.disabled = false;
            })
    });
});

