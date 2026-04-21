@php
    $submitUrl = route('tenant.frontend.ai_custom_page.submit');
    $recordsUrl = route('tenant.frontend.ai_custom_page.records');
@endphp
<script>
    (function () {
        "use strict";

        const pageId = {{ (int) $page_post->id }};
        const submitUrl = @json($submitUrl);
        const recordsUrl = @json($recordsUrl);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const forms = Array.from(document.querySelectorAll('[data-ai-custom-form="1"], .ai-custom-page form'));
        const listBodies = Array.from(document.querySelectorAll('[data-ai-custom-list="1"]'));

        if (!forms.length && !listBodies.length) {
            return;
        }

        const escapeHtml = (value) => {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        };

        const serializePayload = (payload) => {
            if (!payload || typeof payload !== 'object') {
                return '';
            }
            return Object.keys(payload).map((key) => `<strong>${escapeHtml(key)}</strong>: ${escapeHtml(payload[key])}`).join('<br>');
        };

        const renderRows = (rows) => {
            if (!listBodies.length) return;
            listBodies.forEach((tbody) => {
                tbody.innerHTML = '';
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="3">No records yet</td></tr>';
                    return;
                }
                rows.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${serializePayload(row.payload)}</td>
                        <td>${escapeHtml(row.created_at || '')}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
        };

        const loadRecords = async () => {
            try {
                const url = new URL(recordsUrl, window.location.origin);
                url.searchParams.set('page_id', String(pageId));
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' }});
                const data = await res.json();
                if (data && data.success && Array.isArray(data.rows)) {
                    renderRows(data.rows);
                }
            } catch (e) {
                // silent by design
            }
        };

        forms.forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(form);
                formData.append('page_id', String(pageId));

                const payload = {};
                formData.forEach((value, key) => {
                    payload[key] = value;
                });

                try {
                    const res = await fetch(submitUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data && data.success) {
                        form.reset();
                        loadRecords();
                    } else {
                        alert(data?.message || 'Unable to save.');
                    }
                } catch (e) {
                    alert('Unable to save.');
                }
            });
        });

        loadRecords();
    })();
</script>
