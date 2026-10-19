// oryzo-3d.js - the 3D plate in the hero of home.php (Three.js r128).
// A ceramic plate with the dish photo on it. It leans towards the mouse, turns slowly,
// and tips over as you scroll away.
// Sparks rise from the plate and scatter away from your cursor (like the reference's "hover hand").
// If WebGL or Three.js is missing, the flat round photo stays instead.

(function () {
    var hero = document.querySelector('.oz-hero');
    var box = document.querySelector('.oz-hero__plate');
    var canvas = document.querySelector('.oz-3d');
    if (!hero || !box || !canvas) { return; }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // how to cut a round "food" circle out of the pasta photo:
    // zoom = share of the short side, dx/dy = move the centre (the pan sits a little low in the photo)
    var crop = { zoom: 0.86, dx: 0.01, dy: -0.07 };


    // ---------- 3D ----------
    if (!window.THREE) { return; }
    var renderer;
    try {
        renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
    } catch (err) {
        return;                                   // no WebGL: keep the photo
    }
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));   // sharp enough, much lighter on the GPU
    renderer.outputEncoding = THREE.sRGBEncoding;

    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(26, 1, 0.1, 50);
    camera.position.set(0, 9.6, 5.1);      // far enough that the plate fills the plate box
    camera.lookAt(0, 0, 0);

    // lights: soft fill, a warm key from the top left, and an ember-coloured rim from behind
    scene.add(new THREE.HemisphereLight(0xfff1e6, 0x1a0f0b, 0.45));
    var key = new THREE.DirectionalLight(0xffe2c4, 1.15);
    key.position.set(-3, 6, 2);
    scene.add(key);
    var rim = new THREE.PointLight(0xe0784f, 1.6, 12);
    rim.position.set(2.5, 1.2, -2.5);
    scene.add(rim);

    // everything that tilts together
    var tilt = new THREE.Group();     // follows the mouse and the scroll
    var spin = new THREE.Group();     // turns slowly
    scene.add(tilt);
    tilt.add(spin);

    // the plate: a profile line turned around the centre (like a potter's wheel)
    var profile = [
        [0.00, -0.06], [1.05, -0.06], [1.12, -0.03], [1.38, 0.12], [1.42, 0.15],
        [1.40, 0.17], [1.34, 0.15], [1.08, 0.03], [0.00, 0.03]
    ].map(function (p) { return new THREE.Vector2(p[0], p[1]); });
    var plate = new THREE.Mesh(
        new THREE.LatheGeometry(profile, 96),
        new THREE.MeshStandardMaterial({ color: 0x17110f, roughness: 0.42, metalness: 0.2, side: THREE.DoubleSide })
    );
    spin.add(plate);

    // thin copper ring on the rim (Ember accent)
    var ring = new THREE.Mesh(
        new THREE.TorusGeometry(1.41, 0.012, 8, 128),
        new THREE.MeshStandardMaterial({ color: 0xb1462f, roughness: 0.3, metalness: 0.8 })
    );
    ring.rotation.x = Math.PI / 2;
    ring.position.y = 0.165;
    spin.add(ring);

    // the food: a round slice of the dish photo
    var foodMaterial = new THREE.MeshStandardMaterial({ roughness: 0.75, metalness: 0 });
    var food = new THREE.Mesh(new THREE.CircleGeometry(1.06, 96), foodMaterial);
    food.rotation.x = -Math.PI / 2;
    food.position.y = 0.035;
    spin.add(food);

    // soft shadow under the plate
    var shadowCanvas = document.createElement('canvas');
    shadowCanvas.width = shadowCanvas.height = 128;
    var sctx = shadowCanvas.getContext('2d');
    var grad = sctx.createRadialGradient(64, 64, 10, 64, 64, 64);
    grad.addColorStop(0, 'rgba(0,0,0,0.55)');
    grad.addColorStop(1, 'rgba(0,0,0,0)');
    sctx.fillStyle = grad;
    sctx.fillRect(0, 0, 128, 128);
    var shadow = new THREE.Mesh(
        new THREE.PlaneGeometry(3.6, 3.6),
        new THREE.MeshBasicMaterial({ map: new THREE.CanvasTexture(shadowCanvas), transparent: true, depthWrite: false })
    );
    shadow.rotation.x = -Math.PI / 2;
    shadow.position.y = -0.25;
    tilt.add(shadow);


    // sparks rising from the plate
    var SPARKS = reduceMotion ? 0 : 140;
    var sparkPos = new Float32Array(SPARKS * 3);
    var sparkVel = [];
    function resetSpark(i, anywhere) {
        var a = Math.random() * Math.PI * 2, r = 0.4 + Math.random() * 1.0;
        sparkPos[i * 3] = Math.cos(a) * r;
        sparkPos[i * 3 + 1] = anywhere ? Math.random() * 2.2 : 0.1;
        sparkPos[i * 3 + 2] = Math.sin(a) * r;
        sparkVel[i] = { x: 0, y: 0.004 + Math.random() * 0.008, z: 0 };
    }
    for (var s = 0; s < SPARKS; s++) { resetSpark(s, true); }
    var sparkGeo = new THREE.BufferGeometry();
    sparkGeo.setAttribute('position', new THREE.BufferAttribute(sparkPos, 3));
    var sparks = new THREE.Points(sparkGeo, new THREE.PointsMaterial({
        color: 0xff9a5c, size: 0.045, transparent: true, opacity: 0.85,
        blending: THREE.AdditiveBlending, depthWrite: false
    }));
    tilt.add(sparks);


    // ---------- mouse ("hand") and scroll ----------
    var mouse = { x: 0, y: 0, inside: false };
    var mouseWorld = new THREE.Vector3(99, 99, 99);
    var raycaster = new THREE.Raycaster();
    var hoverPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0), -0.6);

    hero.addEventListener('mousemove', function (e) {
        var r = canvas.getBoundingClientRect();
        mouse.x = ((e.clientX - r.left) / r.width) * 2 - 1;
        mouse.y = -((e.clientY - r.top) / r.height) * 2 + 1;
        mouse.inside = true;
        raycaster.setFromCamera(mouse, camera);
        raycaster.ray.intersectPlane(hoverPlane, mouseWorld);
    });
    hero.addEventListener('mouseleave', function () { mouse.inside = false; mouse.x = mouse.y = 0; mouseWorld.set(99, 99, 99); });


    // ---------- size ----------
    var heroH = hero.offsetHeight;
    function resize() {
        heroH = hero.offsetHeight;
        var w = canvas.clientWidth, h = canvas.clientHeight;
        if (!w || !h) { return; }
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    window.addEventListener('resize', resize);
    resize();


    // ---------- animation ----------
    var visible = true;
    new IntersectionObserver(function (entries) { visible = entries[0].isIntersecting; }).observe(hero);

    function frame(time) {
        requestAnimationFrame(frame);
        if (!visible) { return; }

        // lean towards the mouse, tip over as the hero scrolls away
        var scrolled = Math.min(1, window.scrollY / heroH);
        tilt.rotation.x += ((-mouse.y * 0.28 + scrolled * 0.9) - tilt.rotation.x) * 0.06;
        tilt.rotation.z += ((-mouse.x * 0.28) - tilt.rotation.z) * 0.06;
        tilt.position.y = scrolled * 0.8;

        if (!reduceMotion) { spin.rotation.y += 0.0025; }

        // sparks drift up; near the cursor they get pushed away
        for (var i = 0; i < SPARKS; i++) {
            var v = sparkVel[i], ix = i * 3;
            if (mouse.inside) {
                var dx = sparkPos[ix] - mouseWorld.x, dz = sparkPos[ix + 2] - mouseWorld.z, dy = sparkPos[ix + 1] - mouseWorld.y;
                var d2 = dx * dx + dy * dy + dz * dz;
                if (d2 < 0.5) {
                    var push = (0.5 - d2) * 0.02;
                    v.x += dx * push; v.z += dz * push;
                }
            }
            v.x *= 0.96; v.z *= 0.96;
            sparkPos[ix] += v.x + Math.sin(time * 0.001 + i) * 0.0015;
            sparkPos[ix + 1] += v.y;
            sparkPos[ix + 2] += v.z;
            if (sparkPos[ix + 1] > 2.4) { resetSpark(i, false); }
        }
        if (SPARKS) { sparkGeo.attributes.position.needsUpdate = true; }

        renderer.render(scene, camera);
    }

    // load the pasta photo, crop a circle out of it, then fade the 3D plate in
    new THREE.TextureLoader().load(canvas.getAttribute('data-src'), function (tex) {
        var w = tex.image.width, h = tex.image.height;
        var side = Math.min(w, h) * crop.zoom;
        tex.repeat.set(side / w, side / h);
        tex.offset.set((1 - side / w) / 2 + crop.dx, (1 - side / h) / 2 + crop.dy);
        tex.encoding = THREE.sRGBEncoding;
        tex.anisotropy = renderer.capabilities.getMaxAnisotropy();
        foodMaterial.map = tex;
        foodMaterial.needsUpdate = true;
        resize();
        document.documentElement.classList.add('has-3d');
        requestAnimationFrame(frame);
    });
})();
