(() => {
    const searchInput = document.getElementById('orderSearch');
    const tableBody = document.getElementById('ordersTable');
    const rows = Array.from(tableBody.querySelectorAll('tr'));

    const visibleCount = document.getElementById('visibleCount');
    const currentPageEl = document.getElementById('currentPage');
    const totalPagesEl = document.getElementById('totalPages');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    const PER_PAGE = 10;
    let currentPage = 1;
    let filteredRows = rows;

    function apply() {
        const q = (searchInput.value || '').toLowerCase().trim();

        filteredRows = rows.filter(row =>
            row.dataset.search.includes(q)
        );

        const totalPages = Math.max(1, Math.ceil(filteredRows.length / PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;

        rows.forEach(r => r.style.display = 'none');

        const start = (currentPage - 1) * PER_PAGE;
        const end = start + PER_PAGE;

        filteredRows.slice(start, end).forEach(r => {
            r.style.display = '';
        });

        visibleCount.textContent = filteredRows.length;
        currentPageEl.textContent = currentPage;
        totalPagesEl.textContent = totalPages;

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
    }

    searchInput.addEventListener('input', () => {
        currentPage = 1;
        apply();
    });

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            apply();
        }
    });

    nextBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / PER_PAGE);
        if (currentPage < totalPages) {
            currentPage++;
            apply();
        }
    });

    apply();
})();
