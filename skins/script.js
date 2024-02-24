let currentMenu = false;
document.addEventListener('click', function (e) {
    if (e.target.tagName != 'BUTTON' && e.target.tagName != 'svg' || !e.target.getAttribute('data-action')) {
        let settings = document.querySelector('footer .settings');
        if(settings.hasAttribute('data-open')) {
            settings.removeAttribute('data-open');
        }
        return;
    }

    switch (e.target.getAttribute('data-action')) {
        case 'weapon_picked':
            let weapon = e.target.getAttribute('data-weapon');
            if (!weapon) {
                alert('Something happened please contact support!\nError: couldn\'t find weapon name.');
                return;
            }

            ToggleLoading();
            SendFormPost([
                ['weapon', weapon]
            ]);
            break;
        case 'weapon_choose':
            ToggleLoading();
            window.location.href = './';
            break;
        case 'weapon_change':
            if (e.target.classList.contains('selected')) {
                alert(e.target.parentElement.getAttribute('data-text') || 'This skin is already equiped!');
                return;
            }

            ToggleLoading();

            if (!e.target.getAttribute('data-defindex') || !e.target.getAttribute('data-paint')) {
                alert('something happened!\nError: couldn\'t find weapon data.');
                ToggleLoading(true);
                return;
            }

            let defindex = e.target.getAttribute('data-defindex');
            let paint = e.target.getAttribute('data-paint');
            let weaponName = e.target.getAttribute('data-weapon');
            let wear = document.getElementById('wear').value;
            let seed = document.getElementById('seed').value;
            if (!seed || seed < 0 || seed > 1000) {
                seed = 0;
            } else {
                seed = Math.ceil(Number(seed));
            }

            SendFormPost([
                ['defindex', defindex],
                ['weapon_name', weaponName],
                ['paint', paint],
                ['wear', wear],
                ['seed', seed]
            ]);
            break;
        case 'fullscreen':
            let srcImg = e.target.parentElement.querySelector('img').src;
            ToggleModal(srcImg);
            break;
        case 'exit_fullscreen':
            ToggleModal();
            break;
        case 'category':
            if (e.target.classList.contains('selected')) {
                SendFormPost([
                    ['category', 'none']
                ]);
                return;
            }

            const category = e.target.getAttribute('data-category');
            SendFormPost([
                ['category', category]
            ]);
            break;
        case 'toggle_menu':
            let menu = e.target.parentElement;
            menu.getAttribute('data-open') ? menu.removeAttribute('data-open') : menu.setAttribute('data-open', true);
            break;
        case 'language_select':
            if(currentMenu) {
                e.target.parentElement.innerHTML = currentMenu;
                currentMenu = false;
            }

            currentMenu = e.target.parentElement.innerHTML;
            let langs = e.target.getAttribute('data-langs');
            if(!langs) {return;}

            let template = `
            <button data-action="language_select">
                <svg viewBox="0 0 512 512"><path d="M353,450a15,15,0,0,1-10.61-4.39L157.5,260.71a15,15,0,0,1,0-21.21L342.39,54.6a15,15,0,1,1,21.22,21.21L189.32,250.1,363.61,424.39A15,15,0,0,1,353,450Z"/></svg>
            </button>`;
            JSON.parse(langs).forEach(lang => {
                lang = lang.replaceAll('.json', '');
                const languageNames = new Intl.DisplayNames([lang], {
                    type: 'language'
                });

                template += `
                <button data-action="language_change" data-language="${lang}">
                    <svg viewBox="0 0 20 20"><path d="M10,0 C4.5,0 0,4.5 0,10 C0,15.5 4.5,20 10,20 C15.5,20 20,15.5 20,10 C20,4.5 15.5,0 10,0 L10,0 Z M16.9,6 L14,6 C13.7,4.7 13.2,3.6 12.6,2.4 C14.4,3.1 16,4.3 16.9,6 L16.9,6 Z M10,2 C10.8,3.2 11.5,4.5 11.9,6 L8.1,6 C8.5,4.6 9.2,3.2 10,2 L10,2 Z M2.3,12 C2.1,11.4 2,10.7 2,10 C2,9.3 2.1,8.6 2.3,8 L5.7,8 C5.6,8.7 5.6,9.3 5.6,10 C5.6,10.7 5.7,11.3 5.7,12 L2.3,12 L2.3,12 Z M3.1,14 L6,14 C6.3,15.3 6.8,16.4 7.4,17.6 C5.6,16.9 4,15.7 3.1,14 L3.1,14 Z M6,6 L3.1,6 C4.1,4.3 5.6,3.1 7.4,2.4 C6.8,3.6 6.3,4.7 6,6 L6,6 Z M10,18 C9.2,16.8 8.5,15.5 8.1,14 L11.9,14 C11.5,15.4 10.8,16.8 10,18 L10,18 Z M12.3,12 L7.7,12 C7.6,11.3 7.5,10.7 7.5,10 C7.5,9.3 7.6,8.7 7.7,8 L12.4,8 C12.5,8.7 12.6,9.3 12.6,10 C12.6,10.7 12.4,11.3 12.3,12 L12.3,12 Z M12.6,17.6 C13.2,16.5 13.7,15.3 14,14 L16.9,14 C16,15.7 14.4,16.9 12.6,17.6 L12.6,17.6 Z M14.4,12 C14.5,11.3 14.5,10.7 14.5,10 C14.5,9.3 14.4,8.7 14.4,8 L17.8,8 C18,8.6 18.1,9.3 18.1,10 C18.1,10.7 18,11.4 17.8,12 L14.4,12 L14.4,12 Z"/></svg>
                    <div>
                        <span>${languageNames.of(lang)}</span>
                    </div>
                </button>`;
            });
            e.target.parentElement.innerHTML = template;
            break;
        case 'language_change':
            let lang = e.target.getAttribute('data-language');
            if(!lang) {return;}

            ToggleLoading();

            let date = new Date();
            date.setTime(date.getTime()+1000 * 60 * 60 * 24 * 90);

            document.cookie = `cs2weaponpaints_lielxd_language=${lang}; path=/;expires=${date}`;
            window.location.reload();
            break;
    }
});

function ToggleLoading(stop) {
    const loading = document.getElementById('loading');

    if (stop) {
        loading.removeAttribute('data-loading');
        return;
    }

    loading.setAttribute('data-loading', true);
}

const modal = document.getElementById('modalFullscreen');
function ToggleModal(srcImg) {
    if (!srcImg) {
        modal.style.opacity = 0;
        modal.style.pointerEvents = 'none';
        return;
    }

    modal.querySelector('img').src = srcImg;
    modal.style.opacity = 1;
    modal.style.pointerEvents = 'unset';
}

function SendFormPost(data) {
    if (!data || data.length < 1) { return; }

    let form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    for (let i = 0; i < data.length; i++) {
        let field = data[i];
        let input = document.createElement('input');
        input.name = field[0];
        input.value = field[1];

        form.append(input);
    }

    document.body.append(form);
    form.submit();
}

window.onload = function () {
    let card = document.querySelector('.card');
    if (card && getComputedStyle(card).backgroundColor == 'rgba(0, 0, 0, 0)') {
        document.body.style.setProperty('--card-color', 'var(--main-color)');
    }

    let selected = document.querySelector('.selected');
    if (selected && getComputedStyle(selected).backgroundColor == 'rgba(0, 0, 0, 0)') {
        document.body.style.setProperty('--card-selected', '#111');
    }
}