document.addEventListener('click', function(e) {
    if(e.target.tagName != 'BUTTON' && e.target.tagName != 'svg' || !e.target.getAttribute('data-action')) {return;}

    switch(e.target.getAttribute('data-action')) {
        case 'weapon_picked':
            let weapon = e.target.getAttribute('data-weapon');
            if(!weapon) {
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
            if(e.target.classList.contains('selected')) {
                alert(e.target.parentElement.getAttribute('data-text') || 'This skin is already equiped!');
                return;
            }

            ToggleLoading();
            
            if(!e.target.getAttribute('data-defindex') || !e.target.getAttribute('data-paint')) {
                alert('something happened!\nError: couldn\'t find weapon data.');
                ToggleLoading(true);
                return;
            }
            
            let defindex = e.target.getAttribute('data-defindex');
            let paint = e.target.getAttribute('data-paint');
            let weaponName = e.target.getAttribute('data-weapon');
            let wear = document.getElementById('wear').value;
            let seed = document.getElementById('seed').value;
            if(!seed || seed < 0 || seed > 1000) {
                seed = 0;
            }else {
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
            if(e.target.classList.contains('selected')) {
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
    }
});

function ToggleLoading(stop) {
    const loading = document.getElementById('loading');

    if(stop) {
        loading.removeAttribute('data-loading');
        return;
    }

    loading.setAttribute('data-loading', true);
}

const modal = document.getElementById('modalFullscreen');
function ToggleModal(srcImg) {
    if(!srcImg) {
        modal.style.opacity = 0;
        modal.style.pointerEvents = 'none';
        return;
    }

    modal.querySelector('img').src = srcImg;
    modal.style.opacity = 1;
    modal.style.pointerEvents = 'unset';
}

function SendFormPost(data) {
    if(!data || data.length < 1) {return;}

    let form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    for(let i=0; i<data.length; i++) {
        let field = data[i];
        let input = document.createElement('input');
        input.name = field[0];
        input.value = field[1];

        form.append(input);
    }

    document.body.append(form);
    form.submit();
}

window.onload = function() {
    let card = document.querySelector('.card');
    if(card && getComputedStyle(card).backgroundColor == 'rgba(0, 0, 0, 0)') {
        document.body.style.setProperty('--card-color', 'var(--main-color)');
    }

    let selected = document.querySelector('.selected');
    if(selected && getComputedStyle(selected).backgroundColor == 'rgba(0, 0, 0, 0)') {
        document.body.style.setProperty('--card-selected', '#111');
    }
}