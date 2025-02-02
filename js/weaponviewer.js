///////////
// Setup //
///////////

import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { RGBELoader } from 'three/addons/loaders/RGBELoader.js';
import WebGL from 'three/addons/capabilities/WebGL.js';

let scene, renderer, controls, camera;
const canvasContainer = document.querySelector('#threejsArea');

if(usethreejs && WebGL.isWebGL2Available() && canvasContainer
&& modelpath) {
    InitScene();

    if(modelpath) {
        ToggleLoading();

        (async () => {
            await LoadModel(modelpath, legacy);
            
            if(texturepath && texturepath[0] && texturepath[1]) {
                await LoadTexture(texturepath[0], texturepath[1]);
            }
            
            ToggleLoading(true);
        })();
    }
}else {
    canvasContainer.remove();

    const image = document.querySelector('#previewImg');
    image.style.display = 'grid';
}

let stickerslist = null, keychainslist = null;
fetch(Prefix+'src/data/stickers.json')
.then(response => response.json())
.then(stickers => stickerslist = stickers);
fetch(Prefix+'src/data/keychains.json')
.then(response => response.json())
.then(keychains => keychainslist = keychains);

let changedvalt = [];
let changedvalct = [];

/////////////
// Buttons //
/////////////

let viewlistcurrent = false, viewlistpos = false, viewlistselected = false;
const viewselect = document.querySelector('#viewselect');
document.addEventListener('click', async function(e) {
    if(e.target.tagName != 'BUTTON') {return;}

    // Apply buttons
    if(e.target.classList.contains('terror') ||
    e.target.classList.contains('counter-terror')) {
        ToggleLoading();

        const mainbox = document.querySelector('.mainbox');

        const index = mainbox.dataset.index;
        if(!index) {return;}

        let paint = false;
        if(mainbox.dataset.paint) {
            paint = mainbox.dataset.paint;
        }

        let name = false;
        if(mainbox.dataset.name) {
            name = mainbox.dataset.name;
        }

        const wear = document.querySelector('#wear');
        let wearval = 0;
        if(wear) {
            wearval = wear.querySelector('select').value || 0;
            if(wearval == 'custom') {
                wearval = wear.querySelector('#customwear').value || 0;
            }
        }

        const seed = document.querySelector('#seed');
        let seedval = 0;
        if(seed) {
            if(seed.querySelector('input[type="checkbox"]').checked) {
                seedval = seed.querySelector('input[type="number"]').value || 0;
            }
        }

        const nametag = document.querySelector('#nametag');
        let nametagval = false;
        if(nametag) {
            if(nametag.querySelector('input[type="checkbox"]').checked) {
                nametagval = nametag.querySelector('input[type="text"]').value || false;
            }
        }

        const stattrak = document.querySelector('#stattrak');
        let stattrakval = 0;
        if(stattrak) {
            stattrak.querySelector('input[type="checkbox"]').checked?stattrakval=1:stattrakval=0;
        }

        const stickerselem = document.querySelector('.addons .stickers');
        const stickersval = [];
        if(stickerselem) {
            for(const [index, elem] of Object.entries(stickerselem.children)) {
                let id = elem.dataset.id || 0;
                let schema = elem.dataset.schema || 0;
                let x = elem.dataset.x || 0, y = elem.dataset.y || 0;
                let wear = elem.dataset.wear || 0;
                let scale = elem.dataset.scale || 0;
                let rotation = elem.dataset.rotation || 0;
                
                stickersval.push(`${id};${schema};${x};${y};${wear};${scale};${rotation};`);
            }
        }else {
            for(let i=0;i<5;i++) {
                stickersval.push('0;0;0;0;0;0;0;');
            }
        }

        const keychainselem = document.querySelector('.addons .keychains');
        const keychainsval = [];
        if(keychainselem) {
            for(const [index, elem] of Object.entries(keychainselem.children)) {
                let id = elem.dataset.id || 0;
                let x = elem.dataset.x || 0, y = elem.dataset.y || 0, z = elem.dataset.z || 0;
                let seed = elem.dataset.seed || 0;
                
                keychainsval.push(`${id};${x};${y};${z};${seed};`);
            }
        }else {
            keychainsval.push('0;0;0;0;0;');
        }

        let team = 0;
        if(e.target.classList.contains('terror')) {
            team = 1;
        }else if(e.target.classList.contains('counter-terror')) {
            team = 2;
        }

        try {
            let formdata = new FormData();
            formdata.append('team', team);
            formdata.append('type', currenttype);
            formdata.append('index', index);
            formdata.append('name', name);
            formdata.append('paint', paint);
            formdata.append('wear', wearval);
            formdata.append('seed', seedval);
            formdata.append('nametag', nametagval);
            formdata.append('stattrak', stattrakval);
            formdata.append('stickers', JSON.stringify(stickersval));
            formdata.append('keychains', JSON.stringify(keychainsval));

            const response = await fetch(`${Prefix}update`, {
                method: 'post',
                body: formdata
            });
            const data = await response.json();

            ToggleLoading(true);

            if(data['error']) {
                AddMessage(`${data['error']}`, 'fail', 10000);
                return;
            }

            if(data.length == 0) {
                if(team == 1) {
                    saved_t = JSON.stringify({
                        weapon_wear: wearval,
                        weapon_seed: seedval,
                        weapon_nametag: nametagval || null,
                        weapon_stattrak: stattrakval,
                        weapon_stattrak_count: 0,
                        weapon_sticker_0: stickersval[0],
                        weapon_sticker_1: stickersval[1],
                        weapon_sticker_2: stickersval[2],
                        weapon_sticker_3: stickersval[3],
                        weapon_sticker_4: stickersval[4],
                        weapon_keychain: keychainsval[0],
                    });

                    if(saved_t != false && saved_ct != false && currenttype != 'mvp' && currenttype != 'agents') {
                        document.querySelector('#preset').style.display = null;
                    }
                }else if(team == 2) {
                    saved_ct = JSON.stringify({
                        weapon_wear: wearval,
                        weapon_seed: seedval,
                        weapon_nametag: nametagval || null,
                        weapon_stattrak: stattrakval,
                        weapon_stattrak_count: 0,
                        weapon_sticker_0: stickersval[0],
                        weapon_sticker_1: stickersval[1],
                        weapon_sticker_2: stickersval[2],
                        weapon_sticker_3: stickersval[3],
                        weapon_sticker_4: stickersval[4],
                        weapon_keychain: keychainsval[0],
                    });
                    
                    if(saved_t != false && saved_ct != false && currenttype != 'mvp' && currenttype != 'agents') {
                        document.querySelector('#preset').style.display = null;
                        document.querySelector('#preset .counterterrormark').checked = true;
                    }
                }

                AddMessage('Skin saved', 'success', 10000);
                e.target.classList.add('applied');
            }
        }catch(err) {
            AddMessage(`${err.name}<br>${err.message}`, 'fail', 10000);
        }
    }

    // Stickers
    if(e.target.parentElement &&
    e.target.parentElement.classList.contains('stickers')) {
        const stickers = e.target.parentElement;
        viewlistpos = Array.from(stickers.children).indexOf(e.target)+1;
        viewlistselected = e.target.dataset.id;
        
        let title = `${viewselect.querySelector('.title').dataset.prefix} ${stickers.previousElementSibling.innerHTML}`;
        let search = `${viewselect.querySelector('input').dataset.prefix} ${stickers.previousElementSibling.innerHTML}`;
        viewselect.querySelector('.title').innerHTML = title;
        viewselect.querySelector('input').placeholder = search;

        viewselect.querySelector('ul').innerHTML = '';
        viewselect.querySelector('ul').innerHTML = GetHTMLTemplate(stickerslist, viewlistselected);
        viewselect.style.top = '0';

        viewlistcurrent = 'stickers';
    }

    // Keychains
    if(e.target.parentElement &&
    e.target.parentElement.classList.contains('keychains')) {
        const keychains = e.target.parentElement;
        viewlistpos = Array.from(keychains.children).indexOf(e.target)+1;
        viewlistselected = e.target.dataset.id;

        let title = `${viewselect.querySelector('.title').dataset.prefix} ${keychains.previousElementSibling.innerHTML}`;
        let search = `${viewselect.querySelector('input').dataset.prefix} ${keychains.previousElementSibling.innerHTML}`;
        viewselect.querySelector('.title').innerHTML = title;
        viewselect.querySelector('input').placeholder = search;

        viewselect.querySelector('ul').innerHTML = '';
        viewselect.querySelector('ul').innerHTML = GetHTMLTemplate(keychainslist, viewlistselected);
        viewselect.style.top = '0';

        viewlistcurrent = 'keychains';
    }

    // Return from viewselect
    if(e.target.classList.contains('viewselect_return')) {
        viewselect.style.top = '100%';
        viewlistcurrent = false;
        viewlistpos = false;
        viewlistselected = false;

        setTimeout(() => {
            viewselect.querySelector('ul').innerHTML = '';
        }, 300);
    }

    // Viewlist picked
    if(e.target.dataset.action == 'viewlist_choose') {
        const id = e.target.dataset.id;

        if(!e.target.classList.contains('selected')) {
            if(viewlistcurrent === 'stickers') {
                let stickers = document.querySelector('.addons .stickers');
                if(id != 0) {
                    let choosen = stickerslist.find(sticker => sticker.id == id);
        
                    stickers.children[viewlistpos-1].dataset.id = id;
                    stickers.children[viewlistpos-1].title = choosen.name;
                    stickers.children[viewlistpos-1].innerHTML = `<img src="${choosen.image}">`;
                }else {
                    stickers.children[viewlistpos-1].removeAttribute('data-id');
                    stickers.children[viewlistpos-1].removeAttribute('title');
                    stickers.children[viewlistpos-1].innerHTML = '+';
                }
            }else if(viewlistcurrent === 'keychains') {
                let keychains = document.querySelector('.addons .keychains');
                if(id != 0) {
                    let choosen = keychainslist.find(keychain => keychain.id == id);
        
                    keychains.children[viewlistpos-1].dataset.id = id;
                    keychains.children[viewlistpos-1].title = choosen.name;
                    keychains.children[viewlistpos-1].innerHTML = `<img src="${choosen.image}">`;
                }else {
                    keychains.children[viewlistpos-1].removeAttribute('data-id');
                    keychains.children[viewlistpos-1].removeAttribute('title');
                    keychains.children[viewlistpos-1].innerHTML = '+';
                }
            }
        }
        
        viewselect.style.top = '100%';
        viewlistcurrent = false;
        viewlistpos = false;
        viewlistselected = false;

        setTimeout(() => {
            viewselect.querySelector('ul').innerHTML = '';
        }, 300);
    }
});

///////////////
// Functions //
///////////////

function InitScene() {
    let boundingbox = canvasContainer.getBoundingClientRect();

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(75, boundingbox.width / boundingbox.height, 0.1, 1000);

    renderer = new THREE.WebGLRenderer({alpha: true, antialias: true});
    renderer.setSize(boundingbox.width, boundingbox.height);
    renderer.setAnimationLoop(animate);

    canvasContainer.append(renderer.domElement);

    window.addEventListener('resize', function(e) {
        boundingbox = canvasContainer.getBoundingClientRect();

        camera.aspect = boundingbox.width / boundingbox.height;
        camera.updateProjectionMatrix();
    
        renderer.setSize(boundingbox.width, boundingbox.height);
    });

    ////////////////////
    // Orbit Controls //
    ////////////////////

    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.maxDistance = 60;
    controls.rotateSpeed = 0.8;
    controls.enablePan = false;
    

    /////////////////
    // Environment //
    /////////////////

    const pmremGenerator = new THREE.PMREMGenerator(renderer);

    const hdriLoader = new RGBELoader();
    hdriLoader.load('https://raw.githubusercontent.com/LielXD/CS2-WeaponPaints-Website/refs/heads/main/src/environment.hdr', function(texture) {
        const envMap = pmremGenerator.fromEquirectangular(texture).texture;
        texture.dispose();
        scene.environment = envMap;
    });
}

function animate() {
    renderer.render(scene, camera);
    controls.update();
}

function CenterMesh(mesh, center) {
    mesh.scene.position.x += (mesh.scene.position.x - center.x);
    mesh.scene.position.y += (mesh.scene.position.y - center.y);
    mesh.scene.position.z += (mesh.scene.position.z - center.z);
}

let model = null;
async function LoadModel(modelpath, legacy) {
    try {
        const pathexists = await fetch(modelpath);

        if(!pathexists.ok) {
            return false;
        }
    }catch(error) {console.error(error);}

    return new Promise((resolve, reject) => {
        new GLTFLoader().load(modelpath, function(weapon) {
            resolve(weapon);
            scene.add(weapon.scene);

            if(currenttype != 'knifes' && currenttype != 'gloves' && !modelpath.includes('weapon_taser')) {
                if(legacy == true) {
                    weapon.scene.remove(weapon.scene.children[1]);
                }else if(legacy == false) {
                    weapon.scene.remove(weapon.scene.children[0]);
                }
            }
        
            const box = new THREE.Box3().setFromObject(weapon.scene);
            const center = box.getCenter(new THREE.Vector3());
            
            CenterMesh(weapon, center);
        
            const size = box.getSize(new THREE.Vector3());
            camera.position.set(0, 5, size.length() / -1.4);
            camera.lookAt(new THREE.Vector3(0,0,0));
            
            controls.minDistance = size.length() / 1.7;
    
            model = weapon;

            canvasContainer.animate([
                {transform: "scale(0)"},
                {transform: "scale(1)"}
            ], {duration: 800});
        }, false, reject);
    });
}

function DeleteModel() {
    if(!model) {return false;}
    
    model.scene.traverse(function(child) {
        if(child.material) {
            child.parent.remove(child);
            child.material.dispose();
            
            if(child.material.map) {
                child.material.map.dispose();
            }
            if(child.material.aoMap) {
                child.material.aoMap.dispose();
            }
            if(child.material.metalnessMap) {
                child.material.metalnessMap.dispose();
            }
            if(child.material.roughnessMap) {
                child.material.roughnessMap.dispose();
            }
            if(child.material.normalMap) {
                child.material.normalMap.dispose();
            }
        }
    });

    scene.remove(model.scene);
    model = null;

    return true;
}

async function LoadTexture(texturepath, texturepath_metal) {
    if(!model) {return false;}

    try {
        const pathexists = await fetch(texturepath);

        if(!pathexists.ok) {
            return false;
        }
    }catch(error) {console.error(error);}
    
    const texture = new THREE.TextureLoader().load(texturepath);
    texture.encoding = THREE.sRGBEncoding;
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.flipY = false;
    
    let texture_metal = null;
    if(texturepath_metal) {        
        try {
            const pathexists = await fetch(texturepath_metal);
            
            if(pathexists.ok) {
                texture_metal = new THREE.TextureLoader().load(texturepath_metal);
                texture_metal.encoding = THREE.sRGBEncoding;
                texture_metal.colorSpace = THREE.SRGBColorSpace;
                texture_metal.wrapS = THREE.RepeatWrapping;
                texture_metal.wrapT = THREE.RepeatWrapping;
                texture_metal.flipY = false;
            }
        }catch(error) {console.error(error);}
    }

    model.scene.traverse(function(child) {
        if(child.isMesh && child.material && !child.material.name.includes('bare_arm') && !child.material.name.includes('scope')) {
            child.material.map = texture;
            child.material.metalnessMap = texture_metal;
        }
    });

    return true;
}

function AddMessage(msgContent = '', type = 'default', timeout = 3000) {
    if(!msgContent || msgContent == '' || typeof(msgContent) !== 'string') {return;}

    const msg = document.createElement('span');
    msg.classList.add('msg');
    msg.style.setProperty('--timeout', `${timeout}ms`);

    const content = document.createElement('p');
    content.innerHTML = msgContent;

    let svg = '';
    switch(type) {
        case 'fail':
            svg = '<svg viewBox="0 0 512 512"><path d="M255.997,460.351c112.685,0,204.355-91.668,204.355-204.348S368.682,51.648,255.997,51.648  c-112.68,0-204.348,91.676-204.348,204.355S143.317,460.351,255.997,460.351z M255.997,83.888  c94.906,0,172.123,77.209,172.123,172.115c0,94.898-77.217,172.117-172.123,172.117c-94.9,0-172.108-77.219-172.108-172.117  C83.888,161.097,161.096,83.888,255.997,83.888z"/><path d="M172.077,341.508c3.586,3.523,8.25,5.27,12.903,5.27c4.776,0,9.54-1.84,13.151-5.512l57.865-58.973l57.878,58.973  c3.609,3.672,8.375,5.512,13.146,5.512c4.658,0,9.316-1.746,12.902-5.27c7.264-7.125,7.369-18.793,0.242-26.051l-58.357-59.453  l58.357-59.461c7.127-7.258,7.021-18.92-0.242-26.047c-7.252-7.123-18.914-7.018-26.049,0.24l-57.878,58.971l-57.865-58.971  c-7.135-7.264-18.797-7.363-26.055-0.24c-7.258,7.127-7.369,18.789-0.236,26.047l58.351,59.461l-58.351,59.453  C164.708,322.715,164.819,334.383,172.077,341.508z"/></svg>';
            break;
        case 'success':
            svg = '<svg viewBox="0 0 32 32"><path d="M16,1A15,15,0,1,0,31,16,15,15,0,0,0,16,1Zm0,28.33A13.33,13.33,0,1,1,29.33,16,13.35,13.35,0,0,1,16,29.33Z"/><polygon points="13.91 18.59 10.71 15.39 9.29 16.8 13.91 21.41 23.61 11.71 22.2 10.29 13.91 18.59"/></svg>';
            break;
        case 'warning':
            svg = '<svg viewBox="0 0 24 24"><path d="M21.171,15.398l-5.912-9.854C14.483,4.251,13.296,3.511,12,3.511s-2.483,0.74-3.259,2.031l-5.912,9.856  c-0.786,1.309-0.872,2.705-0.235,3.83C3.23,20.354,4.472,21,6,21h12c1.528,0,2.77-0.646,3.406-1.771  C22.043,18.104,21.957,16.708,21.171,15.398z M12,17.549c-0.854,0-1.55-0.695-1.55-1.549c0-0.855,0.695-1.551,1.55-1.551  s1.55,0.696,1.55,1.551C13.55,16.854,12.854,17.549,12,17.549z M13.633,10.125c-0.011,0.031-1.401,3.468-1.401,3.468  c-0.038,0.094-0.13,0.156-0.231,0.156s-0.193-0.062-0.231-0.156l-1.391-3.438C10.289,9.922,10.25,9.712,10.25,9.5  c0-0.965,0.785-1.75,1.75-1.75s1.75,0.785,1.75,1.75C13.75,9.712,13.711,9.922,13.633,10.125z"/></svg>';
            break;
        default:
            svg = '<svg viewBox="0 0 16 16"><path d="M8,2C4.69,2,2,4.69,2,8s2.69,6,6,6s6-2.69,6-6S11.31,2,8,2z M8,13c-2.76,0-5-2.24-5-5s2.24-5,5-5s5,2.24,5,5    S10.76,13,8,13z"/><path d="M8,6.85c-0.28,0-0.5,0.22-0.5,0.5v3.4c0,0.28,0.22,0.5,0.5,0.5s0.5-0.22,0.5-0.5v-3.4C8.5,7.08,8.28,6.85,8,6.85z"/><path d="M8.01,4.8C7.75,4.78,7.51,5.05,7.5,5.32c0,0.01,0,0.07,0,0.08c0,0.27,0.21,0.47,0.49,0.48c0,0,0.01,0,0.01,0    c0.27,0,0.49-0.24,0.5-0.5c0-0.01,0-0.11,0-0.11C8.5,4.98,8.29,4.8,8.01,4.8z"/></svg>';
            break;
    }

    msg.innerHTML = svg;
    msg.append(content);

    document.querySelector('#messages').append(msg);
    setTimeout(() => {
        msg.remove();
    }, timeout);
}

function GetHTMLTemplate(list = [], id) {
    let html = '';
    
    html = `
    <li>
        <button class="card ${!id?'selected':''}" data-action="viewlist_choose" data-id="0">
            <div class="imgbox">
                <img src="https://raw.githubusercontent.com/LielXD/CS2-WeaponPaints-Website/refs/heads/main/src/none.png">
            </div>
            <span>None</span>
        </button>
    </li>
    `;

    for(const [index, elem] of Object.entries(list)) {
        if(index > 100) {break;}

        html += `
        <li>
            <button class="card ${id==elem.id?'selected':''}" data-action="viewlist_choose" data-id="${elem.id}">
                <div class="imgbox">
                    <img src="${elem.image}">
                </div>
                <span>${elem.name}</span>
            </button>
        </li>
        `;
    }

    return html;
}

const viewselect_search = viewselect.querySelector('input');
viewselect_search.addEventListener('input', function() {
    if(viewselect_search.value == '') {
        if(viewlistcurrent == 'stickers') {
            viewselect.querySelector('ul').innerHTML = GetHTMLTemplate(stickerslist, viewlistselected);
        }else if(viewlistcurrent == 'keychains') {
            viewselect.querySelector('ul').innerHTML = GetHTMLTemplate(keychainslist, viewlistselected);
        }
        return;
    }

    if(viewlistcurrent == 'stickers') {
        let searched = stickerslist.filter(sticker => sticker.name.toLowerCase().includes(viewselect_search.value.toLocaleLowerCase()));
        viewselect.querySelector('ul').innerHTML = GetHTMLTemplate(searched, viewlistselected);
    }else if(viewlistcurrent == 'keychains') {
        let searched = keychainslist.filter(keychain => keychain.name.toLowerCase().includes(viewselect_search.value.toLocaleLowerCase()));
        viewselect.querySelector('ul').innerHTML = GetHTMLTemplate(searched, viewlistselected);
    }
});

const settings = document.querySelector('#settings');
const marks = settings.querySelectorAll('.marks input[type="radio"]');
marks.forEach(elem => {
    elem.addEventListener('input', function() {
        let current = false;
        if(elem.classList.contains('terrormark') && saved_t) {
            current = JSON.parse(saved_t);
        }else if(elem.classList.contains('counterterrormark') && saved_ct) {
            current = JSON.parse(saved_ct);
        }

        const wear = document.querySelector('#wear');
        if(wear && current['weapon_wear'] != null) {
            let wearoption = wear.querySelector(`option[value="${current['weapon_wear']}"]`);
            if(wearoption) {
                wearoption.selected = 'selected';
            }else {
                wear.querySelector('option[value="custom"]').selected = 'selected';
                wear.querySelector('#customwear').value = current['weapon_wear'];
                wear.querySelector('#customwear').dataset.val = current['weapon_wear'];
            }
        }

        const seed = document.querySelector('#seed');
        if(seed && current['weapon_seed'] != null) {
            if(current['weapon_seed'] == 0) {
                seed.querySelector('input[type="checkbox"]').checked = false;
                seed.querySelector('input[type="number"]').value = '';
                seed.querySelector('input[type="number"]').disabled = true;
            }else {
                seed.querySelector('input[type="checkbox"]').checked = true;
                seed.querySelector('input[type="number"]').value = current['weapon_seed'];
                seed.querySelector('input[type="number"]').disabled = null;
            }
        }

        const nametag = document.querySelector('#nametag');
        if(nametag) {
            if(current['weapon_nametag'] == null) {
                nametag.querySelector('input[type="checkbox"]').checked = false;
                nametag.querySelector('input[type="text"]').value = '';
                nametag.querySelector('input[type="text"]').disabled = true;
            }else {
                nametag.querySelector('input[type="checkbox"]').checked = true;
                nametag.querySelector('input[type="text"]').value = current['weapon_nametag'];
                nametag.querySelector('input[type="text"]').disabled = false;
            }
        }

        const stattrak = document.querySelector('#stattrak');
        if(stattrak && current['weapon_stattrak'] != null) {
            if(current['weapon_stattrak'] == 0) {
                stattrak.querySelector('input[type="checkbox"]').checked = false;
                stattrak.querySelector('p').innerHTML = '/';
                stattrak.querySelector('p').style.opacity = null;
            }else {
                stattrak.querySelector('input[type="checkbox"]').checked = true;
                stattrak.querySelector('p').innerHTML = current['weapon_stattrak_count'];
                stattrak.querySelector('p').style.opacity = 1;
            }
        }

        const stickerselem = document.querySelector('.addons .stickers');
        if(stickerselem) {
            if(current['weapon_sticker_0']) {
                let stickersaved = current['weapon_sticker_0'].split(';');
                
                if(stickersaved[0] == 0) {
                    stickerselem.children[0].removeAttribute('data-id');
                    stickerselem.children[0].removeAttribute('title');
                    stickerselem.children[0].innerHTML = `+`;
                }else {
                    let stickerinfo = stickerslist.find(sticker => sticker.id == stickersaved[0]);

                    stickerselem.children[0].dataset.id = stickersaved[0];
                    stickerselem.children[0].title = stickerinfo.name;
                    stickerselem.children[0].innerHTML = `<img src="${stickerinfo.image}">`;
                }
            }
            if(current['weapon_sticker_1']) {
                let stickersaved = current['weapon_sticker_1'].split(';');
                if(stickersaved[0] == 0) {
                    stickerselem.children[1].removeAttribute('data-id');
                    stickerselem.children[1].removeAttribute('title');
                    stickerselem.children[1].innerHTML = `+`;
                }else {
                    let stickerinfo = stickerslist.find(sticker => sticker.id == stickersaved[0]);
                    
                    stickerselem.children[1].dataset.id = stickersaved[0];
                    stickerselem.children[1].title = stickerinfo.name;
                    stickerselem.children[1].innerHTML = `<img src="${stickerinfo.image}">`;
                }
            }
            if(current['weapon_sticker_2']) {
                let stickersaved = current['weapon_sticker_2'].split(';');
                if(stickersaved[0] == 0) {
                    stickerselem.children[2].removeAttribute('data-id');
                    stickerselem.children[2].removeAttribute('title');
                    stickerselem.children[2].innerHTML = `+`;
                }else {
                    let stickerinfo = stickerslist.find(sticker => sticker.id == stickersaved[0]);

                    stickerselem.children[2].dataset.id = stickersaved[0];
                    stickerselem.children[2].title = stickerinfo.name;
                    stickerselem.children[2].innerHTML = `<img src="${stickerinfo.image}">`;
                }
            }
            if(current['weapon_sticker_3']) {
                let stickersaved = current['weapon_sticker_3'].split(';');
                if(stickersaved[0] == 0) {
                    stickerselem.children[3].removeAttribute('data-id');
                    stickerselem.children[3].removeAttribute('title');
                    stickerselem.children[3].innerHTML = `+`;
                }else {
                    let stickerinfo = stickerslist.find(sticker => sticker.id == stickersaved[0]);

                    stickerselem.children[3].dataset.id = stickersaved[0];
                    stickerselem.children[3].title = stickerinfo.name;
                    stickerselem.children[3].innerHTML = `<img src="${stickerinfo.image}">`;
                }
            }
            if(current['weapon_sticker_4']) {
                let stickersaved = current['weapon_sticker_4'].split(';');
                if(stickersaved[0] == 0) {
                    stickerselem.children[4].removeAttribute('data-id');
                    stickerselem.children[4].removeAttribute('title');
                    stickerselem.children[4].innerHTML = `+`;
                }else {
                    let stickerinfo = stickerslist.find(sticker => sticker.id == stickersaved[0]);

                    stickerselem.children[4].dataset.id = stickersaved[0];
                    stickerselem.children[4].title = stickerinfo.name;
                    stickerselem.children[4].innerHTML = `<img src="${stickerinfo.image}">`;
                }
            }
        }

        const keychains = document.querySelector('.addons .keychains');
        if(keychains) {
            if(current['weapon_keychain']) {
                let keychainsaved = current['weapon_keychain'].split(';');
                if(keychainsaved[0] == 0) {
                    keychains.children[0].removeAttribute('data-id');
                    keychains.children[0].removeAttribute('title');
                    keychains.children[0].innerHTML = '+';
                }else {
                    let keychaininfo = keychainslist.find(keychain => keychain.id == keychainsaved[0]);

                    keychains.children[0].dataset.id = keychainsaved[0];
                    keychains.children[0].title = keychaininfo.name;
                    keychains.children[0].innerHTML = `<img src="${keychaininfo.image}">`;
                }
            }
        }
    });
});