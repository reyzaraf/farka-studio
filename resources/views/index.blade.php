<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Farka Studio</title>
    
    <!-- Primary Meta Tags -->
    <meta name="title" content="Farka Studio">
    <meta name="description" content="Farka Studio is a premier architectural and interior design studio specializing in innovative, premium, and sustainable spatial experiences.">
    <meta name="keywords" content="Farka Studio, Architecture, Interior Design, Architectural Firm, Sustainable Design, Premium Architecture, Spatial Design, Building Design">
    <meta name="author" content="Farka Studio">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language" content="English">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Farka Studio">
    <meta property="og:description" content="Farka Studio is a premier architectural and interior design studio specializing in innovative, premium, and sustainable spatial experiences.">
    <meta property="og:image" content="{{ asset('farkalogo.svg') }}">
    <meta property="og:site_name" content="Farka Studio">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="Farka Studio">
    <meta property="twitter:description" content="Farka Studio is a premier architectural and interior design studio specializing in innovative, premium, and sustainable spatial experiences.">
    <meta property="twitter:image" content="{{ asset('farkalogo.svg') }}">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#ffffff">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: white; color: #000; overflow: hidden; }

        /* --- PRELOADER ELEMENTS --- */
        .sketch-line { position: absolute; background-color: #000; z-index: 120; opacity: 1 !important; }
        .line-h { height: 2px; width: calc(100% + 40px); left: -20px; transform: scaleX(0); }
        .line-v { width: 2px; height: calc(100% + 30px); top: -20px; transform: scaleY(0); }
        .top-line { top: -2px; } 
        .right-line { right: -2px; }

        .loader-frame { position: relative; width: 80vw; max-width: 450px; height: 80px; display: flex; z-index: 110; }
        .char-box { flex: 1; position: relative; overflow: hidden; }
        .char-bg { position: absolute; inset: 0; z-index: 52; transform: translateY(-101%); opacity: 1 !important; scale: 1.05; }

        /* --- LOGO MORPHING (CINEMATIC) --- */
        #logo-farka { position: fixed; transition: all 0.8s cubic-bezier(0.85, 0, 0.15, 1); pointer-events: none; z-index: 195; }
        .logo-center { top: 50%; left: 50%; transform: translate(-50%, -50%); width: 220px; }
        .logo-nav-bottom { top: 82%; left: 50%; transform: translate(-50%, -50%); width: 80px; }
        .logo-project-header { top: 2rem; left: 1.5rem; transform: translate(0, -50%); width: 90px; }

        @media (min-width: 768px) {
            .loader-frame { height: 120px; }
            .logo-center { width: 280px; }
            .logo-nav-bottom { width: 110px; }
            .logo-project-header { top: 5rem; left: 5rem; width: 130px; }
        }

        /* --- PAGE SECTIONS --- */
        .page-section { position: absolute; inset: 0; opacity: 0; pointer-events: none; transition: opacity 0.6s ease; }
        .page-section.active { opacity: 1; pointer-events: auto; }
        .scroll-container { overflow-y: auto; scrollbar-width: none; scroll-behavior: smooth; }
        .scroll-container::-webkit-scrollbar { display: none; }

        /* --- BOTTOM BAR & FOOTER --- */
        .bottom-nav-bar { position: fixed; bottom: 0; left: 0; width: 100%; height: 25vh; background-color: #fff; z-index: 100; border-top: 1px solid #eee; transition: transform 0.6s cubic-bezier(0.85, 0, 0.15, 1); }
        .hidden-bar { transform: translateY(100%); }

        .nav-btn .btn-text { transition: all 0.3s; }
        .nav-btn:hover .btn-text { background-color: #000; color: #fff; }
        .nav-btn:hover .btn-lines { opacity: 1; }
        
        .btn-lines { position: absolute; inset: 0; opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 20; }
        .btn-line-h { position: absolute; left: -8px; right: -8px; height: 1.5px; background: black; }
        .btn-line-v { position: absolute; top: -8px; bottom: -6px; width: 1.5px; background: black; }
        .btn-t { top: -1.5px; }  
        .btn-r { right: -1.5px; }

        /* --- PROJECT MENU STYLES --- */
        .submenu-container { 
            max-height: 0; 
            opacity: 0;
            overflow: hidden; 
            margin-top: 0; 
            margin-bottom: 0;
            transition: max-height 0.6s cubic-bezier(0.85, 0, 0.15, 1), opacity 0.4s ease, margin 0.6s cubic-bezier(0.85, 0, 0.15, 1); 
        }
        .submenu-container.open { 
            max-height: 1200px;
            opacity: 1;
            margin-bottom: 1rem; 
            margin-top: 0.75rem; 
        }
        
        .submenu-item:first-child {
            margin-top: 0.75rem;
        }

        .menu-text, .submenu-text { transition: all 0.3s; color: #ccc; }
        .submenu-text { color: #888; }
    </style>
</head>
<body class="overflow-hidden">

    <div id="preloader-bg" class="fixed inset-0 z-[190] bg-white"></div>
    <img id="logo-farka" src="{{ asset('farkalogo.svg') }}" class="logo-center opacity-0">

    <div id="preloader-stagger" class="fixed inset-0 z-[200] flex items-center justify-center pointer-events-none">
        <div class="loader-frame" id="loader-frame-main">
            <div class="sketch-line line-h top-line" id="l-top"></div>
            <div class="sketch-line line-v right-line" id="l-right"></div>
            
            <div class="char-box"><div class="char-bg bg-[#1F1F1F]" id="bg-0"></div></div>
            <div class="char-box"><div class="char-bg bg-[#A6A6A6]" id="bg-1"></div></div>
            <div class="char-box"><div class="char-bg bg-[#D4D4D4]" id="bg-2"></div></div>
            <div class="char-box"><div class="char-bg bg-[#F0F0F0]" id="bg-3"></div></div>
            <div class="char-box"><div class="char-bg bg-[#FFFFFF]" id="bg-4"></div></div>
        </div>
    </div>

    @include('pages.home')
    @include('pages.project')
    @include('pages.about')
    @include('pages.contact')
    @include('layouts.navigation')

    <script>
        const db = {!! $projects_json ?? '{}' !!};

        const cascadeColors = [
            '#1F1F1F', // 1. Hitam awal (tetap)
            '#404040', // 2. Abu-abu gelap
            '#666666', // 3. Abu-abu medium-gelap
            '#8C8C8C', // 4. Abu-abu medium
            '#AFAFAF', // 5. Abu-abu medium-terang
            '#C9C9C9', // 6. Abu-abu terang mulai memudar
            '#DFDFDF', // 7. Abu-abu pucat
            '#F0F0F0', // 8. Abu-abu sangat pucat (soft)
            '#F8F8F8', // 9. Hampir putih bersih
            '#FFFFFF'  // 10. Putih murni
            ];
        const sidebar = document.getElementById('sidebar-menu');
        const mainItems = Array.from(document.querySelectorAll('.project-menu-item'));
        
        function scrollToProjectMenu() {
            const scrollArea = document.getElementById('project-scroll-container');
            if (scrollArea) { scrollArea.scrollTo({ top: 0, behavior: 'smooth' }); }
        }
        
        function clearCascade(itemsList, isSubmenu = false) {
            itemsList.forEach(item => {
                const textEl = isSubmenu ? item.querySelector('.submenu-text') : item.querySelector('.menu-text');
                const linesEl = item.querySelector(isSubmenu ? '.submenu-lines' : '.menu-lines');
                const defaultColor = isSubmenu ? '#888' : '#ccc';
                
                textEl.style.backgroundColor = 'transparent';
                textEl.style.color = defaultColor;
                if (linesEl) linesEl.style.opacity = '0';
            });
        }

        function applyCascade(itemsList, targetIndex, isSubmenu = false) {
            itemsList.forEach((item, idx) => {
                const textEl = isSubmenu ? item.querySelector('.submenu-text') : item.querySelector('.menu-text');
                const linesEl = item.querySelector(isSubmenu ? '.submenu-lines' : '.menu-lines');
                const defaultColor = isSubmenu ? '#888' : '#ccc';

                if (idx < targetIndex) {
                    textEl.style.backgroundColor = 'transparent';
                    textEl.style.color = defaultColor;
                    if (linesEl) linesEl.style.opacity = '0';
                } else {
                    const offset = idx - targetIndex;
                    const color = cascadeColors[offset] || cascadeColors[cascadeColors.length - 1];
                    
                    textEl.style.backgroundColor = color;
                    textEl.style.color = offset >= 3 ? '#000' : '#fff';
                    if (linesEl) linesEl.style.opacity = '1';
                }
            });
        }

        function restoreActiveStates() {
            const activeMainIdx = mainItems.findIndex(el => el.classList.contains('active'));
            clearCascade(mainItems, false);
            clearCascade(Array.from(document.querySelectorAll('.submenu-item')), true);
            
            if (activeMainIdx !== -1) {
                const activeContainer = document.getElementById(mainItems[activeMainIdx].getAttribute('data-target'));
                if (activeContainer) {
                    const subItems = Array.from(activeContainer.querySelectorAll('.submenu-item'));
                    const activeSubIdx = subItems.findIndex(el => el.classList.contains('active'));

                    if (activeSubIdx !== -1) {
                        applyCascade(subItems, activeSubIdx, true);
                    } else {
                        applyCascade(mainItems, activeMainIdx, false);
                    }
                }
            }
        }

        mainItems.forEach((item, idx) => {
            item.addEventListener('mouseenter', () => {
                clearCascade(Array.from(document.querySelectorAll('.submenu-item')), true);
                applyCascade(mainItems, idx, false);
            });
        });

        document.querySelectorAll('.submenu-item').forEach(subItem => {
            subItem.addEventListener('mouseenter', () => {
                const container = subItem.closest('.submenu-container');
                const subItems = Array.from(container.querySelectorAll('.submenu-item'));
                clearCascade(mainItems, false); 
                clearCascade(Array.from(document.querySelectorAll('.submenu-item')), true);
                applyCascade(subItems, subItems.indexOf(subItem), true);
            });
        });

        if(sidebar) sidebar.addEventListener('mouseleave', restoreActiveStates);

        const wait = (ms) => new Promise(resolve => setTimeout(resolve, ms));
        
        async function runPreloader() {
            const mainLogo = document.getElementById('logo-farka');
            
            const linesH = ['l-top'];
            const linesV = ['l-right'];
            const bgs = [0, 1, 2, 3, 4];
            const ease = "cubic-bezier(0.85, 0, 0.15, 1)";
            const loopCount = 2; 

            for (let loop = 0; loop < loopCount; loop++) {
                const dirs = bgs.map(() => Math.random() > 0.5 ? 101 : -101);

                bgs.forEach(i => {
                    const bg = document.getElementById(`bg-${i}`);
                    bg.style.transition = 'none';
                    bg.style.transform = `translateY(${dirs[i]}%)`;
                });

                linesH.forEach(id => { 
                    const el = document.getElementById(id);
                    el.style.transition = 'none'; 
                    el.style.transform = 'scaleX(0)'; 
                    el.style.transformOrigin = Math.random() > 0.5 ? 'left' : 'right';
                });
                linesV.forEach(id => { 
                    const el = document.getElementById(id);
                    el.style.transition = 'none'; 
                    el.style.transform = 'scaleY(0)'; 
                    el.style.transformOrigin = Math.random() > 0.5 ? 'top' : 'bottom';
                });

                await wait(50);

                linesH.forEach(id => { document.getElementById(id).style.transition = `transform 0.8s ${ease}`; document.getElementById(id).style.transform = 'scaleX(1)'; });
                linesV.forEach(id => { document.getElementById(id).style.transition = `transform 0.8s ${ease}`; document.getElementById(id).style.transform = 'scaleY(1)'; });
                bgs.forEach(i => { const bg = document.getElementById(`bg-${i}`); bg.style.transition = `transform 0.8s ${ease} ${i * 80}ms`; bg.style.transform = 'translateY(0)'; });

                await wait(1200);
                
                if (loop === 0) { 
                    mainLogo.style.transition = 'opacity 0.6s ease';
                    mainLogo.classList.remove('opacity-0'); 
                }

                linesH.forEach(id => { const el = document.getElementById(id); el.style.transformOrigin = Math.random() > 0.5 ? 'left' : 'right'; el.style.transform = 'scaleX(0)'; });
                linesV.forEach(id => { const el = document.getElementById(id); el.style.transformOrigin = Math.random() > 0.5 ? 'top' : 'bottom'; el.style.transform = 'scaleY(0)'; });
                bgs.forEach(i => { const bg = document.getElementById(`bg-${i}`); bg.style.transition = `transform 0.8s ${ease} ${i * 80}ms`; bg.style.transform = `translateY(${-dirs[i]}%)`; });

                await wait(1200);
                await wait(1000);
            }

            await wait(100);
            mainLogo.style.transition = 'all 1.2s cubic-bezier(0.85, 0, 0.15, 1)';
            mainLogo.className = 'logo-nav-bottom';
            
            const preloaderBg = document.getElementById('preloader-bg');
            const preloaderStagger = document.getElementById('preloader-stagger');
            preloaderBg.style.transition = 'opacity 1.2s ease-in-out';
            preloaderBg.style.opacity = '0';
            preloaderStagger.style.display = 'none';

            setTimeout(() => {
                preloaderBg.style.display = 'none';
                document.body.classList.remove('overflow-hidden');
                mainLogo.style.transition = 'all 0.8s cubic-bezier(0.85, 0, 0.15, 1)';
                mainLogo.style.zIndex = '150'; 
                restoreActiveStates();
            }, 1200);
        }

        window.onload = runPreloader;

        function toggleCat(id, el) {
            const targetCat = document.getElementById(`cat-${id}`);
            const isActive = el.classList.contains('active');

            document.querySelectorAll('.submenu-container').forEach(e => e.classList.remove('open'));
            document.querySelectorAll('.project-menu-item').forEach(e => e.classList.remove('active'));

            if (!isActive && targetCat) {
                targetCat.classList.add('open');
                el.classList.add('active');
            }
            
            const idx = mainItems.indexOf(el);
            clearCascade(mainItems, false); 
            clearCascade(Array.from(document.querySelectorAll('.submenu-item')), true);
            
            if (!isActive) {
                applyCascade(mainItems, idx, false);
            }
        }

        function setDetail(id, el) {
            const data = db[id];
            if (!data) return;

            const detailContainer = document.getElementById('project-detail-container');
            const placeholder = document.getElementById('project-placeholder');
            const contentContainer = document.getElementById('det-content-container');
            
            if(placeholder) {
                placeholder.style.opacity = '0'; 
                setTimeout(() => { placeholder.style.display = 'none'; }, 500);
            }

            detailContainer.style.opacity = '0'; 
            
            setTimeout(() => {
                document.getElementById('det-title').innerText = data.title;
                document.getElementById('det-status').innerText = data.status || 'N/A';
                document.getElementById('det-architect').innerText = data.architect || 'N/A';
                document.getElementById('det-floor').innerText = data.floor || 'N/A';
                document.getElementById('det-site').innerText = data.site || 'N/A';
                document.getElementById('det-stories').innerText = data.stories || 'N/A';
                document.getElementById('det-location').innerText = data.location || 'N/A';
                
                contentContainer.innerHTML = '';
                if (data.content) {
                    data.content.forEach(item => {
                        const wrapper = document.createElement('div');
                        wrapper.className = "mb-12";
                        const imgEl = document.createElement('img');
                        imgEl.src = item.img;
                        imgEl.className = "w-full aspect-[4/5] object-cover shadow-sm mb-4";
                        const descEl = document.createElement('p');
                        descEl.className = "text-sm text-gray-500 leading-relaxed pl-2 border-l-2 border-gray-200";
                        descEl.innerText = item.text;
                        wrapper.appendChild(imgEl);
                        wrapper.appendChild(descEl);
                        contentContainer.appendChild(wrapper);
                    });
                }
                
                detailContainer.style.opacity = '1';
                
                if (window.innerWidth < 768) {
                    detailContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    document.getElementById('project-scroll-container').scrollTop = 0; 
                }
            }, 300);

            document.querySelectorAll('.submenu-item').forEach(e => e.classList.remove('active'));
            el.classList.add('active');
            const container = el.closest('.submenu-container');
            const subItems = Array.from(container.querySelectorAll('.submenu-item'));
            const idx = subItems.indexOf(el);
            clearCascade(mainItems, false);
            clearCascade(Array.from(document.querySelectorAll('.submenu-item')), true);
            applyCascade(subItems, idx, true);
        }

        function hideAllPages() {
            document.getElementById('home-page')?.classList.remove('active');
            document.getElementById('project-page')?.classList.remove('active');
            document.getElementById('about-page')?.classList.remove('active');
            document.getElementById('contact-page')?.classList.remove('active');
        }

        function goToProject() {
            document.getElementById('bottom-bar').classList.add('hidden-bar');
            hideAllPages();
            document.getElementById('logo-farka').className = 'logo-project-header';
            setTimeout(() => { 
                document.getElementById('project-page').classList.add('active'); 
                restoreActiveStates(); 
            }, 600);
        }

        function goToAbout() {
            document.getElementById('bottom-bar').classList.add('hidden-bar');
            hideAllPages();
            document.getElementById('logo-farka').className = 'logo-project-header';
            setTimeout(() => { 
                document.getElementById('about-page').classList.add('active'); 
            }, 600);
        }

        function goToContact() {
            document.getElementById('bottom-bar').classList.add('hidden-bar');
            hideAllPages();
            document.getElementById('logo-farka').className = 'logo-project-header';
            setTimeout(() => { 
                document.getElementById('contact-page').classList.add('active'); 
            }, 600);
        }

        function goToHome() {
            hideAllPages();
            setTimeout(() => {
                document.getElementById('logo-farka').className = 'logo-nav-bottom';
                document.getElementById('bottom-bar').classList.remove('hidden-bar');
                document.getElementById('home-page').classList.add('active');
            }, 300);
        }
    </script>
</body>
</html>