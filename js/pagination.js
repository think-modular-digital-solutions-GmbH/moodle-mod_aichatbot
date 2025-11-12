$(document).ready(function() {
    console.log('Pagination script loaded');
    const rowsPerPage = 10;
    const $table = $('#conversations-table');
    const $rows = $table.find('tbody tr');
    const $pagination = $('#pagination');
    const pageCount = Math.ceil($rows.length / rowsPerPage);

    function displayPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        $rows.hide().slice(start, end).show();

        $pagination.find('li').removeClass('active');
        $pagination.find(`li[data-page=${page}]`).addClass('active');
    }

    function setupPagination() {
        $pagination.empty();

        if (pageCount <= 1) {
            // Don’t show pagination if only one page
            return;
        }

        for (let i = 1; i <= pageCount; i++) {
            const $li = $('<li class="page-item" data-page="' + i + '"></li>');
            const $btn = $('<button class="page-link">' + i + '</button>');

            $btn.on('click', function() {
                displayPage(i);
            });

            $li.append($btn);
            $pagination.append($li);
        }
    }

    setupPagination();
    displayPage(1);
});
