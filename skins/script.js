let currentMenu = false;
document.addEventListener('click', function (e) {
    if (e.target.tagName != 'BUTTON' && e.target.tagName != 'svg' || !e.target.getAttribute('data-action')) {
        let settings = document.querySelector('footer .settings');
        if(settings.hasAttribute('data-open')) {
            settings.removeAttribute('data-open');
        }
        return;
    }

    const cookies = document.cookie.split(';');
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
        case 'agent_picked':
            let team = e.target.getAttribute('data-team');

            SendFormPost([
                ['weapon', team]
            ]);
            break;
        case 'agent_change':
            let agentTeam = e.target.getAttribute('data-team');
            let agentModel = e.target.getAttribute('data-agent');

            SendFormPost([
                ['agentTeam', agentTeam],
                ['agentModel', agentModel]
            ]);
            break;
        case 'mvp_change':
            let mvpId = e.target.getAttribute('data-id');

            SendFormPost([
                ['mvpId', mvpId]
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
        case 'menu_back':
            if(currentMenu) {
                e.target.parentElement.innerHTML = currentMenu;
                currentMenu = false;
            }
            break;
        case 'language_select':
            currentMenu = e.target.parentElement.innerHTML;
            let langs = e.target.getAttribute('data-langs');
            if(!langs) {return;}

            let templateLang = `
            <button data-action="menu_back">
                <svg viewBox="0 0 512 512"><path d="M353,450a15,15,0,0,1-10.61-4.39L157.5,260.71a15,15,0,0,1,0-21.21L342.39,54.6a15,15,0,1,1,21.22,21.21L189.32,250.1,363.61,424.39A15,15,0,0,1,353,450Z"/></svg>
            </button>`;
            JSON.parse(langs).forEach(lang => {
                lang = lang.replaceAll('.json', '');
                const languageNames = new Intl.DisplayNames([lang], {
                    type: 'language'
                });

                templateLang += `
                <button data-action="language_change" data-language="${lang}">
                    <svg viewBox="0 0 20 20"><path d="M10,0 C4.5,0 0,4.5 0,10 C0,15.5 4.5,20 10,20 C15.5,20 20,15.5 20,10 C20,4.5 15.5,0 10,0 L10,0 Z M16.9,6 L14,6 C13.7,4.7 13.2,3.6 12.6,2.4 C14.4,3.1 16,4.3 16.9,6 L16.9,6 Z M10,2 C10.8,3.2 11.5,4.5 11.9,6 L8.1,6 C8.5,4.6 9.2,3.2 10,2 L10,2 Z M2.3,12 C2.1,11.4 2,10.7 2,10 C2,9.3 2.1,8.6 2.3,8 L5.7,8 C5.6,8.7 5.6,9.3 5.6,10 C5.6,10.7 5.7,11.3 5.7,12 L2.3,12 L2.3,12 Z M3.1,14 L6,14 C6.3,15.3 6.8,16.4 7.4,17.6 C5.6,16.9 4,15.7 3.1,14 L3.1,14 Z M6,6 L3.1,6 C4.1,4.3 5.6,3.1 7.4,2.4 C6.8,3.6 6.3,4.7 6,6 L6,6 Z M10,18 C9.2,16.8 8.5,15.5 8.1,14 L11.9,14 C11.5,15.4 10.8,16.8 10,18 L10,18 Z M12.3,12 L7.7,12 C7.6,11.3 7.5,10.7 7.5,10 C7.5,9.3 7.6,8.7 7.7,8 L12.4,8 C12.5,8.7 12.6,9.3 12.6,10 C12.6,10.7 12.4,11.3 12.3,12 L12.3,12 Z M12.6,17.6 C13.2,16.5 13.7,15.3 14,14 L16.9,14 C16,15.7 14.4,16.9 12.6,17.6 L12.6,17.6 Z M14.4,12 C14.5,11.3 14.5,10.7 14.5,10 C14.5,9.3 14.4,8.7 14.4,8 L17.8,8 C18,8.6 18.1,9.3 18.1,10 C18.1,10.7 18,11.4 17.8,12 L14.4,12 L14.4,12 Z"/></svg>
                    <div>
                        <span>${languageNames.of(lang)}</span>
                    </div>
                </button>`;
            });
            e.target.parentElement.innerHTML = templateLang;
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
        case 'color_select':
            currentMenu = e.target.parentElement.innerHTML;

            let translations_color = e.target.getAttribute('data-translations');

            let ThemeCookie_sel = cookies.find(cookie => cookie.includes('cs2weaponpaints_lielxd_theme'));
            let checked = '', input = '';
            if(ThemeCookie_sel) {
                ThemeCookie_sel = ThemeCookie_sel.split('=')[1];
                
                checked = 'checked';
                input = `
                <button data-action="theme_change">
                    <svg viewBox="0 0 20 20"><path d="M9 20v-1.7l.01-.24L15.07 12h2.94c1.1 0 1.99.89 1.99 2v4a2 2 0 0 1-2 2H9zm0-3.34V5.34l2.08-2.07a1.99 1.99 0 0 1 2.82 0l2.83 2.83a2 2 0 0 1 0 2.82L9 16.66zM0 1.99C0 .9.89 0 2 0h4a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zM4 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
                    <div>
                        <span>${JSON.parse(translations_color)['choose']}</span>
                        <input type="color" value="${ThemeCookie_sel}">
                    </div>
                </button>`;
            }

            let templateTheme = `
            <button data-action="menu_back">
                <svg viewBox="0 0 512 512"><path d="M353,450a15,15,0,0,1-10.61-4.39L157.5,260.71a15,15,0,0,1,0-21.21L342.39,54.6a15,15,0,1,1,21.22,21.21L189.32,250.1,363.61,424.39A15,15,0,0,1,353,450Z"/></svg>
            </button>
            <button data-action="theme_check" data-translations='${translations_color}'>
                <svg viewBox="0 0 20 20"><path d="M9 20v-1.7l.01-.24L15.07 12h2.94c1.1 0 1.99.89 1.99 2v4a2 2 0 0 1-2 2H9zm0-3.34V5.34l2.08-2.07a1.99 1.99 0 0 1 2.82 0l2.83 2.83a2 2 0 0 1 0 2.82L9 16.66zM0 1.99C0 .9.89 0 2 0h4a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zM4 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
                <div>
                    <span>${JSON.parse(translations_color)['custom']}</span>
                    <input type="radio" disabled ${checked}>
                </div>
            </button>
            ${input}`;

            e.target.parentElement.innerHTML = templateTheme;
            break;
        case 'theme_check':
            let ThemeCookie_choose = cookies.find(cookie => cookie.includes('cs2weaponpaints_lielxd_theme'));

            if(!ThemeCookie_choose) {
                let currentTheme_color = document.body.style.getPropertyValue('--main-color');
                if(!currentTheme_color) {
                    currentTheme_color = getComputedStyle(document.documentElement).getPropertyValue('--main-color');
                }

                let translations_color = e.target.getAttribute('data-translations');

                let date = new Date();
                date.setTime(date.getTime()+1000 * 60 * 60 * 24 * 90);

                document.cookie = `cs2weaponpaints_lielxd_theme=${currentTheme_color}; path=/;expires=${date}`;

                e.target.querySelector('input').setAttribute('checked', true);
                e.target.parentElement.innerHTML +=
                `<button data-action="theme_change">
                    <svg viewBox="0 0 24 24"><path d="M20.3847 2.87868C19.2132 1.70711 17.3137 1.70711 16.1421 2.87868L14.0202 5.00052L13.313 4.29332C12.9225 3.9028 12.2894 3.9028 11.8988 4.29332C11.5083 4.68385 11.5083 5.31701 11.8988 5.70754L17.5557 11.3644C17.9462 11.7549 18.5794 11.7549 18.9699 11.3644C19.3604 10.9739 19.3604 10.3407 18.9699 9.95018L18.2629 9.24316L20.3847 7.12132C21.5563 5.94975 21.5563 4.05025 20.3847 2.87868Z" fill="currentColor"/><path clip-rule="evenodd" d="M11.9297 7.09116L4.1515 14.8693C3.22786 15.793 3.03239 17.169 3.5651 18.2842L1.99994 19.8493L3.41415 21.2635L4.97931 19.6984C6.09444 20.2311 7.4705 20.0356 8.39414 19.112L16.1723 11.3338L11.9297 7.09116ZM13.3439 11.3338L11.9297 9.91959L5.56571 16.2835C5.17518 16.6741 5.17518 17.3072 5.56571 17.6978C5.95623 18.0883 6.5894 18.0883 6.97992 17.6978L13.3439 11.3338Z"/></svg>
                    <div>
                        <span>${JSON.parse(translations_color)['choose']}</span>
                        <input type="color" value="${currentTheme_color}">
                    </div>
                </button>`;
            }else {
                ToggleLoading();
                document.cookie = `cs2weaponpaints_lielxd_theme=; path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT`;
                
                e.target.querySelector('input').checked = false;
                e.target.parentElement.querySelector('button[data-action="theme_change"]').remove();
                window.location.reload();
            }
            break;
        case 'theme_change':
            e.target.querySelector('input').click();
            e.target.querySelector('input').oninput = function(e) {
                let date = new Date();
                date.setTime(date.getTime()+1000 * 60 * 60 * 24 * 90);

                document.cookie = `cs2weaponpaints_lielxd_theme=${e.target.value}; path=/;expires=${date}`;
                document.body.style.setProperty('--main-color', e.target.value);
            }
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