const primaryHeaeder = document.querySelector('.title-container');
const primarytab = document.querySelector('.tab-container');
const scrollWatcher = document.createElement('div');


scrollWatcher.setAttribute('data-scroll-watcher', '');
primaryHeaeder.before(scrollWatcher);

const navObserver = new IntersectionObserver((entries) => {
    console.log(entries)
    primaryHeaeder.classList.toggle('sticking', !entries[0].isIntersecting)
    primarytab.classList.toggle('sticking', !entries[0].isIntersecting)
}, {rootMargin: "100px 0px 0px 0px"})

navObserver.observe(scrollWatcher)



