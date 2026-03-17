let filters = document.querySelectorAll('#projects-grid-filters > .filter');
filters.forEach(filter => filter.addEventListener('click', (event) => {
    filterData = filter.getAttribute('data-filter');

    filters.forEach(filter => filter.classList.remove('active'));
    filter.classList.add('active');

    document.querySelectorAll('.project-item').forEach(item => {
        let itemFilterElement = item.querySelector('[data-filter-target="' + filterData + '"]');

        if (filterData === 'filter-0' || itemFilterElement)
        {
            showFilterItem(item);
            return;
        }

        if (itemFilterElement === null && !item.classList.contains('hidden'))
        {
            hideFilterItem(item);
        }
    });
}));

function showFilterItem(item)
{
    item.style.display = 'block';

    setTimeout(() => {
        item.classList.remove('hidden')
    }, 50)
}

function hideFilterItem(item)
{
    item.classList.add('hidden')
    setTimeout(() => {
        item.style.display = 'none';
    }, 400)
}