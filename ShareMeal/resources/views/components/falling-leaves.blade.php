{{-- Falling Leaves Effect Component --}}
{{-- Usage: <x-falling-leaves /> --}}

<style>
    /* Falling Leaves CSS */
    .leaf-container-auth {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        pointer-events: none;
        z-index: 1;
        overflow: hidden;
    }

    .leaf-item-auth {
        position: absolute;
        top: -60px;
        pointer-events: none;
        will-change: transform;
        animation-name: leaf-fall-auth;
        animation-iteration-count: infinite;
        animation-timing-function: linear;
    }

    .leaf-wind-auth {
        pointer-events: none;
        transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
        transform: translate3d(0, 0, 0);
        will-change: transform;
    }

    .leaf-svg-auth {
        display: block;
        width: 100%;
        height: 100%;
        will-change: transform;
        animation-name: leaf-sway-auth;
        animation-iteration-count: infinite;
        animation-direction: alternate;
        animation-timing-function: ease-in-out;
        transform-origin: center;
    }

    /* Leaf Colors */
    svg.leaf-svg-auth path.leaf-color-1 { fill: #1b5e20 !important; }
    svg.leaf-svg-auth path.leaf-color-2 { fill: #2e7d32 !important; }
    svg.leaf-svg-auth path.leaf-color-3 { fill: #4caf50 !important; }
    svg.leaf-svg-auth path.leaf-color-4 { fill: #81c784 !important; }
    svg.leaf-svg-auth path.leaf-color-5 { fill: #a5d6a7 !important; }

    @keyframes leaf-fall-auth {
        0%   { transform: translateY(-60px); }
        100% { transform: translateY(110vh); }
    }

    @keyframes leaf-sway-auth {
        0%   { transform: rotate3d(1, 0.5, 0.2, -45deg) rotateZ(-30deg) translateX(-15px); }
        100% { transform: rotate3d(0.2, 1, 0.5, 45deg)  rotateZ(30deg)  translateX(15px);  }
    }
</style>

<div id="falling-leaves-auth" class="leaf-container-auth"></div>

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function () {
        const leafContainer = document.getElementById('falling-leaves-auth');
        if (!leafContainer) return;

        const leafTemplates = [
            // Leaf 1: Classic Oval Leaf
            `<svg class="leaf-svg-auth" viewBox="0 0 100 100">
                <path d="M50,10 C30,30 20,55 35,75 C45,85 55,85 65,75 C80,55 70,30 50,10 Z" />
             </svg>`,
            // Leaf 2: Curved Bamboo/Eucalyptus Leaf
            `<svg class="leaf-svg-auth" viewBox="0 0 100 100">
                <path d="M50,5 C42,20 40,50 48,95 C52,95 56,60 52,20 C52,10 51,5 50,5 Z" />
             </svg>`,
            // Leaf 3: Oak-like Lobed Leaf
            `<svg class="leaf-svg-auth" viewBox="0 0 100 100">
                <path d="M50,10 C45,20 35,22 40,32 C30,35 25,45 35,52 C25,60 30,75 50,85 C70,75 75,60 65,52 C75,45 70,35 60,32 C65,22 55,20 50,10 Z" />
             </svg>`
        ];

        const colorClasses = [
            'leaf-color-1',
            'leaf-color-2',
            'leaf-color-3',
            'leaf-color-4',
            'leaf-color-5'
        ];

        const numLeaves = 20;

        for (let i = 0; i < numLeaves; i++) {
            const leafItem = document.createElement('div');
            leafItem.className = 'leaf-item-auth';

            const size        = Math.floor(Math.random() * 16) + 15; // 15–30px
            const left        = Math.random() * 100;                  // 0–100%
            const opacity     = (Math.random() * 0.3) + 0.12;        // 0.12–0.42
            const fallDuration = (Math.random() * 10) + 10;           // 10–20s
            const fallDelay   = Math.random() * -20;                  // stagger phases
            const swayDuration = (Math.random() * 3) + 3;             // 3–6s
            const swayDelay   = Math.random() * -6;

            leafItem.style.width            = `${size}px`;
            leafItem.style.height           = `${size}px`;
            leafItem.style.left             = `${left}%`;
            leafItem.style.opacity          = opacity;
            leafItem.style.animationDuration = `${fallDuration}s`;
            leafItem.style.animationDelay   = `${fallDelay}s`;

            const windWrapper = document.createElement('div');
            windWrapper.className = 'leaf-wind-auth';

            const templateIdx = Math.floor(Math.random() * leafTemplates.length);
            const colorClass  = colorClasses[Math.floor(Math.random() * colorClasses.length)];

            windWrapper.innerHTML = leafTemplates[templateIdx];

            const svgPath = windWrapper.querySelector('path');
            if (svgPath) svgPath.className.baseVal = colorClass;

            const leafSvg = windWrapper.querySelector('.leaf-svg-auth');
            if (leafSvg) {
                leafSvg.style.animationDuration = `${swayDuration}s`;
                leafSvg.style.animationDelay    = `${swayDelay}s`;
            }

            leafItem.appendChild(windWrapper);
            leafContainer.appendChild(leafItem);
        }

        // Cursor wind deflection (desktop only)
        if (!('ontouchstart' in window || navigator.maxTouchPoints > 0)) {
            const leaves = leafContainer.querySelectorAll('.leaf-item-auth');
            let prevX = 0, prevY = 0, speedX = 0, speedY = 0;

            window.addEventListener('mousemove', (e) => {
                speedX = e.clientX - prevX;
                speedY = e.clientY - prevY;
                prevX  = e.clientX;
                prevY  = e.clientY;

                leaves.forEach(leaf => {
                    const rect  = leaf.getBoundingClientRect();
                    const leafX = rect.left + rect.width  / 2;
                    const leafY = rect.top  + rect.height / 2;
                    const dx    = leafX - e.clientX;
                    const dy    = leafY - e.clientY;
                    const dist  = Math.hypot(dx, dy);

                    if (dist < 150) {
                        const wind    = leaf.querySelector('.leaf-wind-auth');
                        if (!wind) return;
                        const s  = (150 - dist) / 150;
                        const px = (dx / (dist || 1)) * s * 25 + speedX * s * 0.6;
                        const py = (dy / (dist || 1)) * s * 12 + speedY * s * 0.3;
                        wind.style.transform = `translate3d(${px}px, ${py}px, 0)`;
                        clearTimeout(wind._tid);
                        wind._tid = setTimeout(() => {
                            wind.style.transform = 'translate3d(0,0,0)';
                        }, 800);
                    }
                });
            });
        }
    });
})();
</script>
