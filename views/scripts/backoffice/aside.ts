Array.from(document.getElementsByClassName('aside-btn')).forEach(asideButton => {
    asideButton.addEventListener('click', () => {
        const category: string = asideButton.children[0].children[1].innerHTML.toLowerCase();
        if (!asideButton.className.includes('here')) {
            window.location.href = `./${category}.php`;
        }
    })
})