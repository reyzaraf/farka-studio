    <section id="project-page" class="page-section bg-white pt-24 md:pt-40 px-6 md:px-20">
        <div class="fixed top-8 md:top-20 right-6 md:right-20 -translate-y-1/2 font-black cursor-pointer border-b-2 border-black text-[0.65rem] md:text-xs tracking-tight transition hover:text-gray-500 z-50" onclick="goToHome()">Back to Home</div>
        
        <div id="back-to-menu-btn" class="md:hidden fixed bottom-6 right-6 bg-black text-white px-4 py-2.5 text-[0.6rem] font-black tracking-[0.2em] cursor-pointer z-[150] shadow-2xl active:scale-95 transition-all" onclick="scrollToProjectMenu()">
            Back to Menu
        </div>

        <div id="project-scroll-container" class="flex flex-col md:grid md:grid-cols-12 gap-6 md:gap-4 h-full pb-[20vh] md:pb-0 overflow-y-auto md:overflow-y-hidden scroll-container">
            <div class="md:col-span-2 md:border-r border-gray-50 pt-4 md:pt-10" id="sidebar-menu">
                
                @foreach($categories as $categoryModel)
                <div class="mb-4">
                    <div class="project-menu-item relative inline-block cursor-pointer {{ $loop->first ? 'active' : '' }} ml-3" onclick="toggleCat('{{ $categoryModel->slug }}', this)" data-target="cat-{{ $categoryModel->slug }}">
                        <div class="menu-lines absolute inset-0 z-20 opacity-0 transition-opacity duration-300 pointer-events-none">
                            <div class="absolute top-[-2px] left-[-10px] right-[-10px] h-[2px] bg-black"></div>
                            <div class="absolute right-[-2px] top-[-10px] bottom-[-6px] w-[2px] bg-black"></div>
                        </div>
                        <div class="menu-text px-4 py-1 relative z-10 font-black font-header text-xl md:text-2xl tracking-tight">{{ $categoryModel->name }}</div>
                    </div>
                    <div id="cat-{{ $categoryModel->slug }}" class="submenu-container {{ $loop->first ? 'open' : '' }} pl-4 flex flex-col items-start">
                        @foreach($projects->where('category_id', $categoryModel->id) as $projectItem)
                        <div class="submenu-item relative inline-block cursor-pointer mb-4 ml-2" data-slug="{{ $projectItem->slug }}" onclick="setDetail('{{ $projectItem->slug }}', this)">
                            <div class="submenu-lines absolute inset-0 z-20 opacity-0 transition-opacity duration-300 pointer-events-none">
                                <div class="absolute top-[-2px] left-[-10px] right-[-10px] h-[2px] bg-black"></div>
                                <div class="absolute right-[-2px] top-[-10px] bottom-[-6px] w-[2px] bg-black"></div>
                            </div>
                            <div class="submenu-text px-3 py-1 relative z-10 text-xs md:text-sm font-bold font-header tracking-tight">{{ $projectItem->title }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="md:col-span-10 relative h-auto md:h-[calc(100vh-25vh)] mt-6 md:mt-0">
                <div id="project-placeholder" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-opacity duration-500 z-10">
                    <div class="text-gray-200 font-black font-title text-3xl md:text-5xl tracking-tight opacity-60 px-4 text-center">Select a Project</div>
                </div>

                <div class="opacity-0 transition-opacity duration-500 flex flex-col md:flex-row gap-6 md:gap-10 h-full" id="project-detail-container">
                    <div class="w-full md:w-2/3 h-auto md:h-full relative order-last md:order-first">
                        <div class="h-auto md:h-full overflow-y-visible md:overflow-y-auto scroll-container pr-0 md:pr-4 pb-10 md:pb-20" id="image-scroll-container">
                            <div id="det-content-container" class="flex flex-col gap-12"></div>
                        </div>
                    </div>

                    <div class="w-full md:w-1/3 pt-0 md:pt-4 h-fit border-b-2 border-gray-100 pb-6 md:border-0 md:pb-0 order-first md:order-last">
                        <h3 id="det-title" class="font-black font-title text-2xl border-b-2 border-black pb-4 mb-4"></h3>
                        <div class="text-[0.65rem] font-black text-gray-400 mt-4 md:mt-6 tracking-tight">Status</div>
                        <div id="det-status" class="text-sm font-bold mt-1"></div>
                        <div class="text-[0.65rem] font-black text-gray-400 mt-4 md:mt-6 tracking-tight">Architect in Charge</div>
                        <div id="det-architect" class="text-sm font-bold mt-1"></div>
                        <div class="text-[0.65rem] font-black text-gray-400 mt-4 md:mt-6 tracking-tight">Floor Area</div>
                        <div id="det-floor" class="text-sm font-bold mt-1"></div>
                        <div class="text-[0.65rem] font-black text-gray-400 mt-4 md:mt-6 tracking-tight">Site Area</div>
                        <div id="det-site" class="text-sm font-bold mt-1"></div>
                        <div class="text-[0.65rem] font-black text-gray-400 mt-4 md:mt-6 tracking-tight">Description</div>
                        <div id="det-stories" class="text-sm font-bold mt-1"></div>
                        <div class="text-[0.65rem] font-black text-gray-400 mt-4 md:mt-6 tracking-tight">Location</div>
                        <div id="det-location" class="text-sm font-bold mt-1"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
